<?php

namespace App\Models;

class ShopSubscription extends BaseModel
{
    protected string $table = 'shop_subscription';

    // Trouve l'abonnement actif d'une boutique, retourne null si pas d'abonnement
    public function findActiveByShopId(int $shopId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ss.*, sp.name AS plan_name, sp.commission_rate, sp.max_options_per_service, sp.max_portfolio_images
            FROM shop_subscription ss
            INNER JOIN subscription_plan sp ON sp.id = ss.plan_id
            WHERE ss.shop_id = :shop_id
            AND ss.status = 'active'
            AND ss.current_period_end > NOW()"
        );
        $stmt->execute(['shop_id' => $shopId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Abonnements actifs sur un plan donné (voir
     * AdminController::updateSubscriptionPlans() — bascule tous les
     * abonnés déjà actifs vers le nouveau Price Stripe quand le prix
     * du plan change).
     */
    public function findActiveByPlanId(int $planId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM shop_subscription WHERE plan_id = :plan_id AND status = 'active'"
        );
        $stmt->execute(['plan_id' => $planId]);

        return $stmt->fetchAll();
    }

    // Trouve l'abonnement d'une boutique quel que soit son statut (shop_id est unique)
    public function findByShopId(int $shopId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM shop_subscription WHERE shop_id = :shop_id'
        );
        $stmt->execute(['shop_id' => $shopId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Retrouve l'abonnement à partir de l'id Stripe (webhook — voir
    // StripeWebhookController), avec la boutique associée pour notifier
    // le bon utilisateur.
    public function findByStripeSubscriptionId(string $stripeSubscriptionId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ss.*, s.user_id
            FROM shop_subscription ss
            INNER JOIN shop s ON s.id = ss.shop_id
            WHERE ss.stripe_subscription_id = :stripe_subscription_id'
        );
        $stmt->execute(['stripe_subscription_id' => $stripeSubscriptionId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Place (ou repasse) une boutique sur le palier gratuit "Commission".
     * Utilisé à la création d'une boutique et lors de l'annulation d'un
     * abonnement payant, pour que chaque boutique ait toujours une ligne
     * shop_subscription (comme les paliers payants), sans passer par Stripe.
     * La période n'expire jamais (palier gratuit, pas de facturation).
     */
    public function assignFreePlan(int $shopId, int $freePlanId): void
    {
        $existing = $this->findByShopId($shopId);

        $data = [
            'plan_id' => $freePlanId,
            'stripe_subscription_id' => null,
            'status' => 'active',
            'current_period_start' => date('Y-m-d H:i:s'),
            'current_period_end' => '2099-12-31 23:59:59',
        ];

        if ($existing !== null) {
            $this->update($existing['id'], $data);
        } else {
            $data['shop_id'] = $shopId;
            $this->create($data);
        }
    }

    // Retourne le taux de commission applicable pour une boutique, selon son plan actuel
    public function getCommissionRate(int $shopId): float
    {
        $subscription = $this->findActiveByShopId($shopId);
        if ($subscription !== null) {
            return (float) $subscription['commission_rate'];
        }
        return (float) ($_ENV['DEFAULT_COMMISSION_RATE'] ?? 10.0);
    }

    /**
     * Nombre max d'options (et d'éléments de base) par prestation selon
     * le plan actuel de la boutique — 2 par défaut (palier gratuit) si
     * aucun abonnement actif.
     */
    public function getMaxOptionsPerService(int $shopId): int
    {
        $subscription = $this->findActiveByShopId($shopId);
        return $subscription !== null ? (int) $subscription['max_options_per_service'] : 2;
    }

    /**
     * Nombre max d'images de portfolio selon le plan actuel de la
     * boutique — 5 par défaut (palier gratuit) si aucun abonnement actif.
     */
    public function getMaxPortfolioImages(int $shopId): int
    {
        $subscription = $this->findActiveByShopId($shopId);
        return $subscription !== null ? (int) $subscription['max_portfolio_images'] : 5;
    }

    /**
     * Recherche paginée d'abonnements avec filtres combinables (page
     * Abonnements de l'admin).
     *
     * @param array $filters q, status ('active'|'past_due'|'cancelled'),
     *                       registered ('week'|'month'),
     *                       plan (nom exact du plan, ex: 'Commission',
     *                       'Essentiel', 'Pro' — vide = tous), page, per_page
     * @return array{subscriptions: array, total: int}
     */
    public function adminSearch(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $keyword = \App\Core\SearchHelper::buildKeywordWhere($filters['q'], ['s.name', 'u.username', 'u.email'], 'q');
            if ($keyword['sql'] !== '') {
                $where[] = $keyword['sql'];
                $params = array_merge($params, $keyword['params']);
            }
        }

        if (!empty($filters['status'])) {
            $where[] = 'ss.status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['registered'] ?? '') === 'week') {
            $where[] = 'ss.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        } elseif (($filters['registered'] ?? '') === 'month') {
            $where[] = 'ss.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }

        if (!empty($filters['plan'])) {
            $where[] = 'sp.name = :plan';
            $params['plan'] = $filters['plan'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM shop_subscription ss
             INNER JOIN shop s ON s.id = ss.shop_id
             INNER JOIN users u ON u.id = s.user_id
             INNER JOIN subscription_plan sp ON sp.id = ss.plan_id
             {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT ss.id, ss.status, ss.current_period_start, ss.current_period_end, ss.created_at,
                    s.name AS shop_name, s.slug AS shop_slug,
                    u.username, u.email,
                    sp.name AS plan_name, sp.price AS plan_price
             FROM shop_subscription ss
             INNER JOIN shop s ON s.id = ss.shop_id
             INNER JOIN users u ON u.id = s.user_id
             INNER JOIN subscription_plan sp ON sp.id = ss.plan_id
             {$whereSql}
             ORDER BY ss.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return ['subscriptions' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Suggestions d'autocomplétion pour la recherche admin (page Abonnements).
     */
    public function findAdminSuggestions(string $q, int $limit = 8): array
    {
        return \App\Core\SearchHelper::suggest(
            $this->pdo,
            'shop_subscription ss INNER JOIN shop s ON s.id = ss.shop_id INNER JOIN users u ON u.id = s.user_id',
            [
                ['column' => 's.name', 'avatarColumn' => 'u.avatar'],
                ['column' => 'u.username', 'avatarColumn' => 'u.avatar'],
                ['column' => 'u.email', 'avatarColumn' => 'u.avatar'],
            ],
            $q,
            $limit
        );
    }
}
