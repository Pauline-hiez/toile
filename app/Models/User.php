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
}
