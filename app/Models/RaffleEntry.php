<?php

namespace App\Models;

class RaffleEntry extends BaseModel
{
    protected string $table = 'raffle_entry';

    // Trouve l'inscription d'une boutique pour un type et une période donnés
    public function findByShopTypeAndPeriod(int $shopId, string $type, string $period): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM raffle_entry
             WHERE shop_id = :shop_id AND type = :type AND period = :period'
        );
        $stmt->execute(['shop_id' => $shopId, 'type' => $type, 'period' => $period]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Nombre d'inscriptions pour un type et une période donnés
    public function countByTypeAndPeriod(string $type, string $period): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM raffle_entry WHERE type = :type AND period = :period'
        );
        $stmt->execute(['type' => $type, 'period' => $period]);

        return (int) $stmt->fetchColumn();
    }

    // Derniers tickets achetés par une boutique, tous types confondus
    public function findRecentByShopId(int $shopId, int $limit = 6): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM raffle_entry
             WHERE shop_id = :shop_id
             ORDER BY created_at DESC
             LIMIT ' . max(1, $limit)
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }

    // Derniers gagnants toutes boutiques et tous types confondus
    public function findRecentWinners(int $limit = 6): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT re.*, s.name AS shop_name, s.slug AS shop_slug, u.avatar
             FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
             INNER JOIN users u ON u.id = s.user_id
             WHERE re.status = 'selected'
             ORDER BY re.created_at DESC
             LIMIT " . max(1, $limit)
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Toutes les inscriptions pour un type et une période donnés
    public function findByTypeAndPeriod(string $type, string $period): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT re.*, s.name AS shop_name, s.slug AS shop_slug
             FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
             WHERE re.type = :type AND re.period = :period'
        );
        $stmt->execute(['type' => $type, 'period' => $period]);

        return $stmt->fetchAll();
    }

    // Boutiques sélectionnées en page boutiques ce mois
    public function findSelectedBoutiquesThisMonth(): array
    {
        $currentMonth = date('Y-m');

        $stmt = $this->pdo->prepare(
            "SELECT re.*, s.name AS shop_name, s.slug AS shop_slug, u.avatar
             FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
             INNER JOIN users u ON u.id = s.user_id
             WHERE re.type = 'boutiques'
             AND re.period = :period
             AND re.status = 'selected'"
        );
        $stmt->execute(['period' => $currentMonth]);

        return $stmt->fetchAll();
    }

    // Boutiques sélectionnées en page d'accueil cette semaine
    public function findFeaturedHomepageThisWeek(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT re.*, s.name AS shop_name, s.slug AS shop_slug,
                    s.bio AS shop_bio
             FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
             WHERE re.type = 'homepage'
             AND re.status = 'selected'
             AND re.featured_until >= CURDATE()"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Recherche paginée d'inscriptions aux tirages avec filtres
     * combinables (page Tirage au sort de l'admin).
     *
     * @param array $filters q, type ('boutiques'|'homepage'),
     *                       status ('entered'|'selected'|'not_selected'|'cancelled'),
     *                       page, per_page
     * @return array{entries: array, total: int}
     */
    public function adminSearch(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(s.name LIKE :q1 OR u.username LIKE :q2)';
            $params['q1'] = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['type'])) {
            $where[] = 're.type = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = 're.status = :status';
            $params['status'] = $filters['status'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
             INNER JOIN users u ON u.id = s.user_id
             {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT re.id, re.type, re.period, re.status, re.featured_until, re.created_at,
                    s.name AS shop_name, s.slug AS shop_slug,
                    u.username
             FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
             INNER JOIN users u ON u.id = s.user_id
             {$whereSql}
             ORDER BY re.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return ['entries' => $stmt->fetchAll(), 'total' => $total];
    }
}
