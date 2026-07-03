<?php

namespace App\Models;

class PasswordReset extends BaseModel
{
    protected string $table = 'password_reset';

    // Crée un token de reset valable 24h
    public function createToken(int $userId): string
    {
        // Invalide les anciens tokens de l'user
        $stmt = $this->pdo->prepare(
            'DELETE FROM password_reset WHERE user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        $token = bin2hex(random_bytes(32));

        $this->create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 86400),
        ]);
        return $token;
    }

    // Trouve un token valide
    public function findValidToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM password_reset
            WHERE token = :token
            AND used = 0
            AND expires_at > NOW()'
        );
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Marque un token comme utilisé
    public function markAsUsed(int $id): bool
    {
        return $this->update($id, ['used' => 1]);
    }
}
