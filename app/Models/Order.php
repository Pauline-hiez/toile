<?php

namespace App\Models;

class Order extends BaseModel
{
    protected string $table = 'orders';

    public function findByClientId(int $clientId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, s.name AS shop_name, s.slug AS shop_slug
            FROM orders o
            INNER JOIN shop s ON s.id = o.shop_id
            WHERE o.client_id = :client_id
            ORDER BY o.created_at DESC'
        );
        $stmt->execute(['client_id' => $clientId]);

        return $stmt->fetchAll();
    }

    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, u.username AS client_name
            FROM orders o
            INNER JOIN users u ON u.id = o.client_id
            WHERE o.shop_id = :shop_id
            ORDER BY o.created_at DESC'
        );

        $stmt->execute(['shop_id' => $shopId]);
        return $stmt->fetchAll();
    }

    /**
     * Nombre de commandes en attente d'action de l'artiste (nouvelle
     * demande ou devis) — pour la pastille de la sidebar, même définition
     * de "en attente" que OrderController::receivedOrders().
     */
    public function countPendingByShopId(int $shopId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM orders
             WHERE shop_id = :shop_id AND status IN ('quote_requested', 'pending')"
        );
        $stmt->execute(['shop_id' => $shopId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Chiffres clés "à vie" d'une boutique (onglet Statistiques de
     * /my-shop) : nombre de commandes réellement honorées et revenu net
     * (après commission plateforme) sur ces commandes — même filtre de
     * statuts que les graphiques de revenus de l'admin.
     */
    public function getShopLifetimeStats(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(total_price - commission_amount), 0) AS net_revenue
             FROM orders
             WHERE shop_id = :shop_id
             AND status IN ('accepted', 'in_progress', 'delivered', 'completed')"
        );
        $stmt->execute(['shop_id' => $shopId]);
        $result = $stmt->fetch();

        return [
            'total_orders' => (int) $result['total_orders'],
            'net_revenue' => (int) $result['net_revenue'],
        ];
    }

    public function findByIdWithDetails(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*,
                    s.name AS shop_name, s.slug AS shop_slug, s.user_id AS shop_owner_id,
                    u.username AS client_name, u.email AS client_email,
                    sv.title AS service_title
             FROM orders o
             INNER JOIN shop s ON s.id = o.shop_id
             INNER JOIN users u ON u.id = o.client_id
             LEFT JOIN service sv ON sv.id = o.service_id
             WHERE o.id = :id'
        );
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getAllowedTransitions(): array
    {
        return [
            'pending' => [
                'artist' => ['accepted', 'rejected'],
                // L'admin peut forcer une annulation en cas de litige,
                // depuis n'importe quel statut actif (voir
                // OrderController::transition() pour la détection de
                // l'acteur 'admin' et la notification des deux parties).
                'admin' => ['cancelled'],
            ],
            'quote_requested' => [
                'artist' => ['price_proposed', 'rejected'],
                'admin' => ['cancelled'],
            ],
            // L'acceptation du prix proposé ne passe pas par cette map :
            // elle nécessite un paiement Stripe (voir
            // OrderController::payQuote()), pas une simple transition.
            'price_proposed' => [
                'client' => ['rejected'],
                'admin' => ['cancelled'],
            ],
            'accepted' => [
                'artist' => ['in_progress', 'cancelled'],
                'client' => ['cancelled'],
                'admin' => ['cancelled'],
            ],
            'in_progress' => [
                'artist' => ['delivered', 'cancelled'],
                'admin' => ['cancelled'],
            ],
            'delivered' => [
                'client' => ['completed'],
                'artist' => ['in_progress'],
                'admin' => ['cancelled'],
            ],
        ];
    }

    public function canTransition(string $currentStatus, string $actor, string $newStatus): bool
    {
        $transitions = $this->getAllowedTransitions();

        return isset($transitions[$currentStatus][$actor])
            && in_array($newStatus, $transitions[$currentStatus][$actor], true);
    }

    /**
     * Libellé FR + classe de badge pour chaque statut de commande.
     * Partagé entre le dashboard et la page Commandes de l'admin.
     */
    public static function statusLabels(): array
    {
        return [
            'quote_requested' => ['label' => 'Devis demandé', 'class' => \App\Core\Badge::classes('neutral')],
            'price_proposed' => ['label' => 'Prix proposé', 'class' => \App\Core\Badge::classes('warning')],
            'pending' => ['label' => 'En attente', 'class' => \App\Core\Badge::classes('warning')],
            'accepted' => ['label' => 'Acceptée', 'class' => \App\Core\Badge::classes('info')],
            'rejected' => ['label' => 'Refusée', 'class' => \App\Core\Badge::classes('danger')],
            'in_progress' => ['label' => 'En cours', 'class' => \App\Core\Badge::classes('info')],
            'delivered' => ['label' => 'Livrée', 'class' => \App\Core\Badge::classes('info')],
            'completed' => ['label' => 'Terminée', 'class' => \App\Core\Badge::classes('success')],
            'cancelled' => ['label' => 'Annulée', 'class' => \App\Core\Badge::classes('danger')],
        ];
    }

    /**
     * Recherche paginée de commandes avec filtres combinables (page
     * Commandes de l'admin).
     *
     * @param array $filters q, status, registered ('week'|'month'),
     *                       archived ('only'|'all', défaut : masquées), page, per_page
     * @return array{orders: array, total: int}
     */
    public function adminSearch(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $keyword = \App\Core\SearchHelper::buildKeywordWhere($filters['q'], ['o.title', 'u.username', 's.name'], 'q');
            if ($keyword['sql'] !== '') {
                $where[] = $keyword['sql'];
                $params = array_merge($params, $keyword['params']);
            }
        }

        if (!empty($filters['status'])) {
            $where[] = 'o.status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['registered'] ?? '') === 'week') {
            $where[] = 'o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        } elseif (($filters['registered'] ?? '') === 'month') {
            $where[] = 'o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }

        if (($filters['archived'] ?? '') === 'only') {
            $where[] = 'o.is_archived = 1';
        } elseif (($filters['archived'] ?? '') !== 'all') {
            $where[] = 'o.is_archived = 0';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM orders o
             INNER JOIN users u ON u.id = o.client_id
             INNER JOIN shop s ON s.id = o.shop_id
             {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT o.id, o.title, o.status, o.total_price, o.commission_amount, o.is_archived, o.created_at,
                    u.username AS client_name,
                    s.name AS shop_name,
                    sv.title AS service_title
             FROM orders o
             INNER JOIN users u ON u.id = o.client_id
             INNER JOIN shop s ON s.id = o.shop_id
             LEFT JOIN service sv ON sv.id = o.service_id
             {$whereSql}
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return ['orders' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Suggestions d'autocomplétion pour la recherche admin (page Commandes).
     */
    public function findAdminSuggestions(string $q, int $limit = 8): array
    {
        return \App\Core\SearchHelper::suggest(
            $this->pdo,
            // su : propriétaire de la boutique (avatar de la boutique) —
            // distinct de u, le client de la commande.
            'orders o
             INNER JOIN users u ON u.id = o.client_id
             INNER JOIN shop s ON s.id = o.shop_id
             INNER JOIN users su ON su.id = s.user_id',
            [
                'o.title',
                ['column' => 'u.username', 'avatarColumn' => 'u.avatar'],
                ['column' => 's.name', 'avatarColumn' => 'su.avatar'],
            ],
            $q,
            $limit
        );
    }

    public function toggleArchived(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE orders SET is_archived = NOT is_archived WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Archive/désarchive plusieurs commandes en une requête.
     *
     * @param int[] $orderIds
     */
    public function setArchivedMany(array $orderIds, bool $archived): int
    {
        $orderIds = array_values(array_unique(array_filter(array_map('intval', $orderIds))));

        if ($orderIds === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmt = $this->pdo->prepare(
            "UPDATE orders SET is_archived = ? WHERE id IN ({$placeholders})"
        );
        $stmt->execute(array_merge([$archived ? 1 : 0], $orderIds));

        return $stmt->rowCount();
    }
}
