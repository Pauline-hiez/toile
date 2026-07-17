<?php

namespace App\Models;

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

    /**
     * Notifications les plus récentes pour l'aperçu en menu déroulant
     * (header) — la page /notifications complète reste sur findByUserId().
     */
    public function findRecentByUserId(int $userId, int $limit = 8): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notification
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notification
             WHERE user_id = :user_id AND is_read = 0'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function markAllAsRead(int $userId): bool
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

    /**
     * Titre court + icône par type de notification, pour l'aperçu en menu
     * déroulant (header.php) — le message complet reste la description.
     */
    public static function typeInfo(string $type): array
    {
        $map = [
            'new_order' => ['label' => 'Nouvelle commande', 'icon' => 'document'],
            'payment_captured' => ['label' => 'Paiement débité', 'icon' => 'money'],
            'payment_cancelled' => ['label' => 'Paiement annulé', 'icon' => 'money'],
            'payment_refunded' => ['label' => 'Remboursement', 'icon' => 'refund'],
            'order_status' => ['label' => 'Commande mise à jour', 'icon' => 'check'],
            'new_message' => ['label' => 'Nouveau message', 'icon' => 'message'],
            'new_review' => ['label' => 'Nouvel avis', 'icon' => 'star'],
            'artist_approved' => ['label' => 'Demande acceptée', 'icon' => 'check'],
            'artist_rejected' => ['label' => 'Demande refusée', 'icon' => 'x'],
        ];

        return $map[$type] ?? ['label' => 'Notification', 'icon' => 'bell'];
    }
}
