<?php

namespace App\Models;

class Favorite extends BaseModel
{
    protected string $table = 'favorite';

    // Vérifie si une boutique est en favoris pour un utilisateur
    public function isFavorite(int $userId, int $shopId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM favorite
            WHERE user_id = :user_id AND shop_id = :shop_id'
        );
        $stmt->execute(['user_id' => $userId, 'shop_id' => $shopId]);
        return $stmt->fetch() !== false;
    }

    public function add(int $userId, int $shopId): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO favorite (user_id, shop_id)
            VALUES (:user_id, :shop_id)'
        );
        $stmt->execute(['user_id' => $userId, 'shop_id' => $shopId]);
    }

    public function remove(int $userId, int $shopId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM favorite
            WHERE user_id = :user_id AND shop_id = :shop_id'
        );
        $stmt->execute(['user_id' => $userId, 'shop_id' => $shopId]);
    }

    // Affiche tous les favoris de l'utilisateur
    public function findShopsByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT shop.*
            FROM favorite
            INNER JOIN shop ON shop.id = favorite.shop_id
            WHERE favorite.user_id = :user_id
            ORDER BY favorite.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
