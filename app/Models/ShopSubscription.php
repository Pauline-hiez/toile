<?php

namespace App\Models;

class ShopSubscription extends BaseModel
{
    protected string $table = 'shop_subscription';

    // Trouve l'abonnement actif d'une boutique, retourne null si pas d'abonnement
    public function findActiveByShopId(int $shopId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ss.*, sp.name AS plan_name, sp.commission_rate
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

    // Retourne le taux de commission applicable pour une boutique (0% si abonnement actif)
    public function getCommissionRate(int $shopId): float
    {
        $subscription = $this->findActiveByShopId($shopId);
        if ($subscription !== null) {
            return 0.0;
        }
        return (float) ($_ENV['DEFAULT_COMMISSION_RATE'] ?? 10.0);
    }
}
