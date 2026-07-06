<?php

namespace App\Models;

class SubscriptionPlan extends BaseModel
{
    protected string $table = 'subscription_plan';

    // Paliers disponibles
    public function findAll(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscription_plan ORDER BY price ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
