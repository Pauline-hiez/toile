<?php

namespace App\Models;

class Report extends BaseModel
{
    protected string $table = 'report';

    public static function reasonLabels(): array
    {
        return [
            'plagiat' => 'Plagiat',
            'contenu_inapproprie' => 'Contenu inapproprié',
            'arnaque' => 'Arnaque / fraude',
            'commentaire_inapproprie' => 'Commentaire inapproprié',
            'spam' => 'Spam',
            'autre' => 'Autre',
        ];
    }

    // Empêche un même utilisateur de signaler deux fois le même contenu.
    public function findExisting(int $reporterId, string $reportableType, int $reportableId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM report
             WHERE reporter_id = :reporter_id AND reportable_type = :type AND reportable_id = :id'
        );
        $stmt->execute([
            'reporter_id' => $reporterId,
            'type' => $reportableType,
            'id' => $reportableId,
        ]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function markResolved(int $id, int $adminId): bool
    {
        return $this->update($id, [
            'status' => 'resolved',
            'resolved_by' => $adminId,
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markDismissed(int $id, int $adminId): bool
    {
        return $this->update($id, [
            'status' => 'dismissed',
            'resolved_by' => $adminId,
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Recherche paginée de signalements avec filtres combinables (page
     * Signalements de l'admin). Résout le contenu signalé (boutique ou
     * avis) vers la boutique/artiste concerné.
     *
     * @param array $filters q, type ('shop'|'review'),
     *                       status ('pending'|'resolved'|'dismissed'),
     *                       page, per_page
     * @return array{reports: array, total: int}
     */
    public function adminSearch(array $filters): array
    {
        $where = [];
        $params = [];

        // Résolution de la boutique/artiste concerné(e) par la cible du
        // signalement, selon son type (association polymorphe).
        $shopNameSql = "CASE r.reportable_type
                WHEN 'shop' THEN (
                    SELECT s.name FROM shop s WHERE s.id = r.reportable_id
                )
                WHEN 'review' THEN (
                    SELECT s.name FROM review rv
                    INNER JOIN orders o ON o.id = rv.order_id
                    INNER JOIN shop s ON s.id = o.shop_id
                    WHERE rv.id = r.reportable_id
                )
            END";

        $shopSlugSql = "CASE r.reportable_type
                WHEN 'shop' THEN (
                    SELECT s.slug FROM shop s WHERE s.id = r.reportable_id
                )
                WHEN 'review' THEN (
                    SELECT s.slug FROM review rv
                    INNER JOIN orders o ON o.id = rv.order_id
                    INNER JOIN shop s ON s.id = o.shop_id
                    WHERE rv.id = r.reportable_id
                )
            END";

        if (!empty($filters['q'])) {
            $keyword = \App\Core\SearchHelper::buildKeywordWhere($filters['q'], ['u.username', "({$shopNameSql})"], 'q');
            if ($keyword['sql'] !== '') {
                $where[] = $keyword['sql'];
                $params = array_merge($params, $keyword['params']);
            }
        }

        if (!empty($filters['type'])) {
            $where[] = 'r.reportable_type = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'r.status = :status';
            $params['status'] = $filters['status'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM report r
             INNER JOIN users u ON u.id = r.reporter_id
             {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT r.id, r.reportable_type, r.reportable_id, r.reason, r.message, r.status, r.created_at,
                    u.username AS reporter_username,
                    ({$shopNameSql}) AS shop_name,
                    ({$shopSlugSql}) AS shop_slug
             FROM report r
             INNER JOIN users u ON u.id = r.reporter_id
             {$whereSql}
             ORDER BY r.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return ['reports' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Suggestions d'autocomplétion pour la recherche admin (page
     * Signalements) : pseudos des personnes ayant signalé, et noms de
     * boutique (simple liste, sans la résolution polymorphe exacte de
     * adminSearch — suffisant pour des suggestions).
     */
    public function findAdminSuggestions(string $q, int $limit = 8): array
    {
        $usernames = \App\Core\SearchHelper::suggest(
            $this->pdo,
            'report r INNER JOIN users u ON u.id = r.reporter_id',
            [['column' => 'u.username', 'avatarColumn' => 'u.avatar']],
            $q,
            $limit
        );

        $shopNames = \App\Core\SearchHelper::suggest(
            $this->pdo,
            'shop INNER JOIN users su ON su.id = shop.user_id',
            [['column' => 'shop.name', 'avatarColumn' => 'su.avatar']],
            $q,
            $limit
        );

        return \App\Core\SearchHelper::mergeSuggestionLists([$usernames, $shopNames], $limit);
    }
}
