<?php

namespace App\Core;

/**
 * Mode démonstration : activé par APP_DEMO=true dans le .env (typiquement
 * sur un hébergement mutualisé qui bloque les connexions sortantes, comme
 * InfinityFree gratuit). Dans ce mode, les fonctionnalités qui dépendent
 * d'un appel réseau externe — paiements Stripe, envoi d'emails SMTP,
 * connexion Google — sont neutralisées proprement plutôt que de faire
 * planter ou geler la page en attendant un timeout.
 */
class Demo
{
    public static function isEnabled(): bool
    {
        return ($_ENV['APP_DEMO'] ?? 'false') === 'true';
    }
}
