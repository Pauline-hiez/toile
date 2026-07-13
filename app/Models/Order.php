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

    public function findByIdWithDetails(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*,
                    s.name AS shop_name, s.slug AS shop_slug, s.user_id AS shop_owner_id,
                    u.username AS client_name,
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
            ],
            'quote_requested' => [
                'artist' => ['accepted', 'rejected'],
            ],
            'accepted' => [
                'artist' => ['in_progress', 'cancelled'],
                'client' => ['cancelled'],
            ],
            'in_progress' => [
                'artist' => ['delivered', 'cancelled'],
            ],
            'delivered' => [
                'client' => ['completed'],
                'artist' => ['in_progress']
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
            'quote_requested' => ['label' => 'Devis demandé', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-border text-muted'],
            'pending' => ['label' => 'En attente', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-title-bg text-title'],
            'accepted' => ['label' => 'Acceptée', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-info-bg text-info'],
            'rejected' => ['label' => 'Refusée', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-danger-bg text-danger'],
            'in_progress' => ['label' => 'En cours', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-info-bg text-info'],
            'delivered' => ['label' => 'Livrée', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-info-bg text-info'],
            'completed' => ['label' => 'Terminée', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-success-bg text-success'],
            'cancelled' => ['label' => 'Annulée', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-danger-bg text-danger'],
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
            $where[] = '(o.title LIKE :q1 OR u.username LIKE :q2 OR s.name LIKE :q3)';
            $params['q1'] = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
            $params['q3'] = '%' . $filters['q'] . '%';
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
