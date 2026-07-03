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

    public function setArtistRequestStatus(int $userId, string $status): bool
    {
        return $this->update($userId, ['artist_request_status' => $status]);
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

    public function ban(int $userId): bool
    {
        return $this->update($userId, ['is_banned' => 1]);
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
