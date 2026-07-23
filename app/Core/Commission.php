<?php

namespace App\Core;

/**
 * Calcul de la commission plateforme sur une commande — extrait de
 * OrderController::transition() (cas 'accepted') pour être testable
 * indépendamment (pas de DB, pas de Stripe : un pur calcul).
 */
class Commission
{
    /**
     * @param int $totalPriceCents Prix total payé par le client, en centimes.
     * @param float $ratePercent Taux de commission de la boutique (ex: 10.00 pour 10%).
     * @return int Montant de la commission, en centimes, arrondi à l'entier le plus proche.
     */
    public static function calculateAmount(int $totalPriceCents, float $ratePercent): int
    {
        return (int) round($totalPriceCents * $ratePercent / 100);
    }
}
