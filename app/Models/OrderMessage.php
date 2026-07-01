<?php

namespace App\Models;

class OrderMessage extends BaseModel
{
    protected string $table = 'order_message';

    public function findByOrderId(int $orderId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT om.*, u.username AS sender_name
             FROM order_message om
             INNER JOIN users u ON u.id = om.sender_id
             WHERE om.order_id = :order_id
             ORDER BY om.created_at ASC'
        );
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }
}
