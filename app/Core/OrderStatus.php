<?php

namespace App\Core;

class OrderStatus
{
    private const LABELS = [
        'quote_requested' => 'Devis demandé',
        'pending' => 'En attente',
        'accepted' => 'Acceptée',
        'rejected' => 'Refusée',
        'in_progress' => 'En cours de création',
        'delivered' => 'Livrée - En attente de validation',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
    ];

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? $status;
    }
}
