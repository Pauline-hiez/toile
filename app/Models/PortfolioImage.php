<?php

namespace App\Models;

class PortfolioImage extends BaseModel
{
    protected string $table = 'portfolio_image';

    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM portfolio_image WHERE shop_id = :shop_id ORDER BY created_at DESC'
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }
}
