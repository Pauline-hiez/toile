<?php

namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function countPendingArtistRequests(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE artist_request_status = 'pending'");
        return (int) $stmt->fetchColumn();
    }

    public function setArtistRequestStatus(int $userId, string $status): bool
    {
        return $this->update($userId, ['artist_request_status' => $status]);
    }

    // Enregistre une candidature artiste complète (voir ArtistController::submitRequest())
    public function submitArtistRequest(int $userId, array $data): bool
    {
        return $this->update($userId, [
            'artist_request_status' => 'pending',
            'artist_display_name' => $data['artist_display_name'],
            'requested_shop_name' => $data['requested_shop_name'],
            'artist_contact_email' => $data['artist_contact_email'],
            'artist_presentation' => $data['artist_presentation'],
            'artist_motivation' => $data['artist_motivation'],
            'artist_terms_accepted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findPendingArtistRequests(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users
            WHERE artist_request_status = :status
            ORDER BY created_at ASC'
        );
        $stmt->execute(['status' => 'pending']);
        return $stmt->fetchAll();
    }

    public function approveArtistRequest(int $userId): bool
    {
        return $this->update($userId, [
            'role' => 'artist',
            'artist_request_status' => 'approved',
        ]);
    }

    public function rejectArtistRequest(int $userId): bool
    {
        return $this->update($userId, [
            'artist_request_status' => 'rejected',
        ]);
    }

    public function findAllUsers(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, username, role, is_banned,
                    artist_request_status, created_at
            FROM users
            ORDER BY created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Recherche paginée d'utilisateurs avec filtres combinables (page
     * Utilisateurs de l'admin).
     *
     * @param array $filters q, role, status ('active'|'banned'),
     *                       registered ('week'|'month'), page, per_page
     * @return array{users: array, total: int}
     */
    public function search(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $keyword = \App\Core\SearchHelper::buildKeywordWhere($filters['q'], ['username', 'email'], 'q');
            if ($keyword['sql'] !== '') {
                $where[] = $keyword['sql'];
                $params = array_merge($params, $keyword['params']);
            }
        }

        if (!empty($filters['role'])) {
            $where[] = 'role = :role';
            $params['role'] = $filters['role'];
        }

        if (($filters['status'] ?? '') === 'active') {
            $where[] = 'is_banned = 0';
        } elseif (($filters['status'] ?? '') === 'banned') {
            $where[] = 'is_banned = 1';
        }

        if (($filters['registered'] ?? '') === 'week') {
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        } elseif (($filters['registered'] ?? '') === 'month') {
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM users {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT id, email, username, avatar, role, is_banned, artist_request_status, created_at
             FROM users
             {$whereSql}
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return ['users' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Suggestions d'autocomplétion pour la recherche admin (page
     * Utilisateurs, et la barre de recherche globale de la sidebar admin).
     */
    public function findSuggestions(string $q, int $limit = 8): array
    {
        return \App\Core\SearchHelper::suggest(
            $this->pdo,
            'users',
            [
                ['column' => 'username', 'avatarColumn' => 'avatar'],
                ['column' => 'email', 'avatarColumn' => 'avatar'],
            ],
            $q,
            $limit
        );
    }

    public function ban(int $userId): bool
    {
        return $this->update($userId, ['is_banned' => 1]);
    }

    /**
     * Suspend plusieurs utilisateurs en une requête. Exclut toujours
     * les admins et l'utilisateur courant, quels que soient les ids
     * fournis (filet de sécurité côté modèle, en plus du contrôleur).
     *
     * @param int[] $userIds
     */
    public function banMany(array $userIds, int $excludeUserId): int
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        $userIds = array_diff($userIds, [$excludeUserId]);

        if ($userIds === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $this->pdo->prepare(
            "UPDATE users SET is_banned = 1 WHERE role != 'admin' AND id IN ({$placeholders})"
        );
        $stmt->execute(array_values($userIds));

        return $stmt->rowCount();
    }

    public function unban(int $userId): bool
    {
        return $this->update($userId, ['is_banned' => 0]);
    }

    public function changeRole(int $userId, string $role): bool
    {
        return $this->update($userId, ['role' => $role]);
    }
}
