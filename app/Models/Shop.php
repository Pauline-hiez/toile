<?php

namespace App\Models;

class Shop extends BaseModel
{
    protected string $table = 'shop';

    /**
     * Styles artistiques disponibles à la sélection (formulaire boutique)
     * et au filtrage (recherche, page d'accueil). Source canonique unique.
     */
    public const STYLES = ['anime', 'réaliste', 'chibi', 'pixel art', 'concept art'];

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
                ) AS avg_rating,
                CASE WHEN re.status = 'selected' THEN 1 ELSE 0 END AS is_raffle_featured
            FROM shop
            LEFT JOIN raffle_entry re
                ON re.shop_id = shop.id
                AND re.type = 'boutiques'
                AND re.period = :current_month
                AND re.status = 'selected'
            WHERE 1 = 1
        ";

        $params['current_month'] = date('Y-m');

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
            'price' => ' ORDER BY shop.is_open DESC, is_raffle_featured DESC, min_price ASC',
            default => ' ORDER BY shop.is_open DESC, is_raffle_featured DESC, avg_rating DESC',
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

    public function findAllWithOwner(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT shop.*, u.username AS owner_name, u.email AS owner_email
            FROM shop
            INNER JOIN users u ON u.id = shop.user_id
            ORDER BY shop.created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Slugs de boutique indexés par user_id, pour un lot d'utilisateurs.
     *
     * @param int[] $userIds
     * @return array<int, string>
     */
    public function findSlugsByUserIds(array $userIds): array
    {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT user_id, slug FROM shop WHERE user_id IN ({$placeholders})"
        );
        $stmt->execute(array_values($userIds));

        return array_column($stmt->fetchAll(), 'slug', 'user_id');
    }

    /**
     * Recherche paginée de boutiques avec filtres combinables (page
     * Artistes de l'admin).
     *
     * @param array $filters q, status ('active'|'pending'|'closed'|'suspended'),
     *                       registered ('week'|'month'), page, per_page
     * @return array{shops: array, total: int}
     */
    public function adminSearch(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(shop.name LIKE :q1 OR u.username LIKE :q2)';
            $params['q1'] = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
        }

        if (($filters['status'] ?? '') === 'active') {
            $where[] = 'shop.is_open = 1 AND u.is_banned = 0';
        } elseif (($filters['status'] ?? '') === 'pending') {
            // N'a jamais choisi de formule d'abonnement (jamais ouverte).
            $where[] = 'shop.plan_selected = 0 AND u.is_banned = 0';
        } elseif (($filters['status'] ?? '') === 'closed') {
            // A choisi une formule, mais fermée volontairement pour l'instant.
            $where[] = 'shop.plan_selected = 1 AND shop.is_open = 0 AND u.is_banned = 0';
        } elseif (($filters['status'] ?? '') === 'suspended') {
            $where[] = 'u.is_banned = 1';
        }

        if (($filters['registered'] ?? '') === 'week') {
            $where[] = 'shop.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        } elseif (($filters['registered'] ?? '') === 'month') {
            $where[] = 'shop.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM shop INNER JOIN users u ON u.id = shop.user_id {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT shop.id, shop.name, shop.slug, shop.banner, shop.styles,
                    shop.is_open, shop.plan_selected, shop.monetization_type, shop.created_at,
                    u.username, u.avatar, u.is_banned,
                    (SELECT COUNT(*) FROM orders WHERE orders.shop_id = shop.id) AS order_count,
                    (SELECT AVG(review.rating) FROM review
                        INNER JOIN orders ON orders.id = review.order_id
                        WHERE orders.shop_id = shop.id) AS avg_rating,
                    (SELECT COUNT(*) FROM review
                        INNER JOIN orders ON orders.id = review.order_id
                        WHERE orders.shop_id = shop.id) AS review_count,
                    (SELECT COUNT(*) FROM favorite WHERE favorite.shop_id = shop.id) AS favorite_count,
                    sp.name AS plan_name
             FROM shop
             INNER JOIN users u ON u.id = shop.user_id
             LEFT JOIN shop_subscription ss ON ss.shop_id = shop.id AND ss.status = 'active'
             LEFT JOIN subscription_plan sp ON sp.id = ss.plan_id
             {$whereSql}
             ORDER BY shop.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return ['shops' => $stmt->fetchAll(), 'total' => $total];
    }

    public function toggleOpen(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE shop SET is_open = NOT is_open WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function toggleAcceptsQuotes(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE shop SET accepts_quotes = NOT accepts_quotes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Boutiques mises en avant sur la page d'accueil ("Nos artistes du
     * jour") : priorité aux gagnantes du tirage au sort "page d'accueil"
     * de la semaine en cours, complétées par les boutiques ouvertes les
     * mieux notées pour toujours remplir la grille.
     */
    public function findFeaturedForHomepage(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                shop.id, shop.name, shop.slug, shop.bio, shop.styles, shop.banner,
                (SELECT filename FROM portfolio_image
                    WHERE portfolio_image.shop_id = shop.id
                    ORDER BY created_at ASC LIMIT 1) AS cover_image,
                (SELECT AVG(review.rating) FROM review
                    INNER JOIN orders ON orders.id = review.order_id
                    WHERE orders.shop_id = shop.id) AS avg_rating,
                (SELECT COUNT(*) FROM review
                    INNER JOIN orders ON orders.id = review.order_id
                    WHERE orders.shop_id = shop.id) AS review_count,
                CASE WHEN re.status = 'selected' THEN 1 ELSE 0 END AS is_featured
             FROM shop
             LEFT JOIN raffle_entry re
                ON re.shop_id = shop.id
                AND re.type = 'homepage'
                AND re.status = 'selected'
                AND re.featured_until >= CURDATE()
             WHERE shop.is_open = 1
             ORDER BY is_featured DESC, avg_rating DESC, shop.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
