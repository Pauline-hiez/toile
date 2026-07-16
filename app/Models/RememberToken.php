<?php

namespace App\Models;

class RememberToken extends BaseModel
{
    protected string $table = 'remember_token';

    /**
     * Émet un nouveau jeton "se souvenir de moi" (30 jours) pour un
     * utilisateur et retourne sa valeur en clair (à poser en cookie —
     * seul le hash est conservé en base).
     */
    public function issue(int $userId): string
    {
        $token = bin2hex(random_bytes(32));

        $this->create([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 30 * 86400),
        ]);

        return $token;
    }

    /**
     * Retrouve le jeton (et son utilisateur) à partir de la valeur en
     * clair envoyée par le cookie, si non expiré.
     */
    public function findValid(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM remember_token WHERE token_hash = :hash AND expires_at > NOW()'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function deleteByUserId(int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM remember_token WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }
}
