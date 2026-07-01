<?php

namespace App\Models;

class Shop extends BaseModel
{
    protected string $table = 'shop';

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shop WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function slugExists(string $slug, ?int $excludeShopId = null): bool
    {
        $sql = 'SELECT id FROM shop WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeShopId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeShopId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() !== false;
    }

    public function generateUniqueSlug(string $name, ?int $excludeShopId = null): string
    {
        $baseSlug = $this->slugify($name);
        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $excludeShopId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shop WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Recherche de boutiques avec filtres combinables.
     *
     * @param array 
     * 
     */

    public function search(array $filters): array
    {
        // Calcule le prix min de la boutique
        $sql = "
            SELECT
                shop.*,
                (
                    SELECT MIN(base_price)
                    FROM service
                    WHERE service.shop_id = shop.id AND service.is_active = 1
                ) AS min_price,
                (
                    SELECT AVG(review.rating)
                    FROM review
                    INNER JOIN orders ON orders.id = review.order_id
                    WHERE orders.shop_id = shop.id
                ) AS avg_rating
            FROM shop
            WHERE shop.is_open = 1
        ";

        $params = [];

        // Recherche textuelle sur le nom de la boutique.
        if (!empty($filters['q'])) {
            $sql .= ' AND shop.name LIKE :q';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        // Filtre par style 
        if (!empty($filters['style'])) {
            $sql .= ' AND JSON_CONTAINS(shop.styles, :style)';

            $params['style'] = json_encode($filters['style']);
        }

        $having = [];

        if (!empty($filters['min_price'])) {
            $having[] = 'min_price >= :min_price';
            $params['min_price'] = (int) round($filters['min_price'] * 100);
        }

        if (!empty($filters['max_price'])) {
            $having[] = 'min_price <= :max_price';
            $params['max_price'] = (int) round($filters['max_price'] * 100);
        }

        if (!empty($having)) {
            $sql .= ' HAVING ' . implode(' AND ', $having);
        }

        // Tri.
        $sort = $filters['sort'] ?? 'rating';
        $sql .= match ($sort) {
            'price' => ' ORDER BY min_price ASC',
            default => ' ORDER BY avg_rating DESC',
        };

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private function slugify(string $text): string
    {
        // Translittère les accents (é -> e, à -> a...).
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        // Tout en minuscules.
        $text = strtolower($text);

        // Remplace tout ce qui n'est pas une lettre/chiffre par un tiret.
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Retire les tirets en début/fin de chaîne.
        return trim($text, '-');
    }
}
