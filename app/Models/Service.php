<?php

namespace App\Models;

class Service extends BaseModel
{
    protected string $table = 'service';

    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM service WHERE shop_id = :shop_id ORDER BY created_at DESC');
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }

    public function findActiveByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM service WHERE shop_id = :shop_id AND is_active = 1 ORDER BY created_at DESC'
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }
}
