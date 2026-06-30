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
}
