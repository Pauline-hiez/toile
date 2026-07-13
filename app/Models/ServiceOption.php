<?php

namespace App\Models;

class ServiceOption extends BaseModel
{
    protected string $table = 'service_option';

    public function findByServiceId(int $serviceId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM service_option WHERE service_id = :service_id');
        $stmt->execute(['service_id' => $serviceId]);

        return $stmt->fetchAll();
    }

    public function deleteByServiceId(int $serviceId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM service_option WHERE service_id = :service_id');
        return $stmt->execute(['service_id' => $serviceId]);
    }

    /**
     * Toutes les options des prestations d'une boutique, avec le titre de
     * la prestation parente (page "Mes prestations" — onglet Options).
     */
    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT so.*, s.title AS service_title
            FROM service_option so
            INNER JOIN service s ON s.id = so.service_id
            WHERE s.shop_id = :shop_id
            ORDER BY s.title, so.label'
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }
}
