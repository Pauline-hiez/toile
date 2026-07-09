<?php

namespace App\Models;

class RaffleEntry extends BaseModel
{
    protected string $table = 'raffle_entry';

    // Trouve l'inscription d'une boutique pour un mois donné
    public function findByShopAndMonth(int $shopId, string $month): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM raffle_entry
            WHERE shop_id = :shop_id AND month = :month'
        );
        $stmt->execute(['shop_id' => $shopId, 'month' => $month]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Trouve les inscriptions d'un mois
    public function findByMonth(string $month): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT re.*, s.name AS shop_name, s.slug AS shop_slug
            FROM raffle_entry re
            INNER JOIN shop s ON s.id = re.shop_id
            WHERE re.month = :month'
        );
        $stmt->execute(['month' => $month]);
        return $stmt->fetchAll();
    }

    // Toutes les entrées séléctionnées pour un mois
    public function findSelectedByMonth(string $month): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT re.*, s.name AS shop_name, s.slug AS shop_slug
            FROM raffle_entry re
            INNER JOIN shop s ON s.id = re.shop_id
            WHERE re.month = :month AND re.status = 'selected'"
        );
        $stmt->execute(['mont' => $month]);
        return $stmt->fetchAll();
    }

    // Trouve une boutique mise en avant aujourd'hui
    public function findFeaturedToday(): ?array
    {
        $currentMonth = date('Y-m');

        $stmt = $this->pdo->prepare(
            "SELECT re.*, s.name AS shop_name, s.slug AS shop_slug,
                    s.bio AS shop_bio
             FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
             WHERE re.month = :month
             AND re.featured_today = 1
             AND re.status = 'selected'"
        );
        $stmt->execute(['month' => $currentMonth]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Réinitialise toutes les mises en avant du mois
    public function resetFeatured(string $month): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE raffle_entry SET featured_today = 0 WHERE month = :month'
        );
        $stmt->execute(['month' => $month]);
    }
}
