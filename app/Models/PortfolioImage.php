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

    /**
     * Image de portfolio la plus récente parmi les boutiques ouvertes
     * pratiquant ce style (voir Shop::STYLES), pour illustrer une tuile
     * de style sur la page d'accueil. Null si aucune boutique n'a encore
     * d'image dans ce style.
     */
    public function findCoverByStyle(string $style): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT pi.filename
            FROM portfolio_image pi
            INNER JOIN shop ON shop.id = pi.shop_id
            WHERE shop.is_open = 1 AND JSON_CONTAINS(shop.styles, :style)
            ORDER BY pi.created_at DESC
            LIMIT 1'
        );
        $stmt->execute(['style' => json_encode($style)]);

        $result = $stmt->fetchColumn();

        return $result !== false ? $result : null;
    }
}
