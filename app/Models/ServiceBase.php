<?php

namespace App\Models;

class ServiceBase extends BaseModel
{
    protected string $table = 'service_base';

    public function findByServiceId(int $serviceId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM service_base WHERE service_id = :service_id ORDER BY category, id');
        $stmt->execute(['service_id' => $serviceId]);

        return $stmt->fetchAll();
    }

    /**
     * Éléments de base d'une prestation, groupés par catégorie
     * (ex. "Format" => [...], "Style" => [...]) — pratique pour le
     * formulaire de commande (un groupe de boutons radio par catégorie).
     *
     * @return array<string, array>
     */
    public function findByServiceIdGrouped(int $serviceId): array
    {
        $grouped = [];

        foreach ($this->findByServiceId($serviceId) as $base) {
            $grouped[$base['category']][] = $base;
        }

        return $grouped;
    }

    public function deleteByServiceId(int $serviceId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM service_base WHERE service_id = :service_id');
        return $stmt->execute(['service_id' => $serviceId]);
    }

    /**
     * Tous les éléments de base des prestations d'une boutique, avec le
     * titre de la prestation parente (page "Mes prestations" — onglet Bases).
     */
    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT sb.*, s.title AS service_title
            FROM service_base sb
            INNER JOIN service s ON s.id = sb.service_id
            WHERE s.shop_id = :shop_id
            ORDER BY s.title, sb.category, sb.id'
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }
}
