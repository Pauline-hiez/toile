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
            "SELECT re.*, s.name AS shop_name, s.slug AS shop_slug
             FROM raffle_entry re
             INNER JOIN shop s ON s.id = re.shop_id
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
}
