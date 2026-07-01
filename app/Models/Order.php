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
}
