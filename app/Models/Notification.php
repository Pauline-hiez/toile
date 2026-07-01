<?php

namespace App\Models;

use Override;

class Notification extends BaseModel
{
    protected string $table = 'notification';

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notification
            WHERE user_id = :user_id
            ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notification SET is_read = 1
            WHERE user_id = :user_id AND is_read = 0'
        );
        return $stmt->execute(['user_id' => $userId]);
    }

    public function notify(int $userId, string $type, string $message, ?string $link = null): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'link' => $link,
        ]);
    }
}
