<?php

namespace App\Models;

class Review extends BaseModel
{
    protected string $table = 'review';

    public function getShopRatingStats(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT AVG(r.rating) AS average, COUNT(r.id) AS count
             FROM review r
             INNER JOIN orders o ON o.id = r.order_id
             WHERE o.shop_id = :shop_id'
        );
        $stmt->execute(['shop_id' => $shopId]);

        $result = $stmt->fetch();

        return [
            'average' => $result['average'] !== null ? (float) $result['average'] : null,
            'count' => (int) $result['count'],
        ];
    }

    public function findByOrderId(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM review WHERE order_id = :order_id'
        );
        $stmt->execute(['order_id' => $orderId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
