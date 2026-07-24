<?php

namespace App\Core;

/**
 * Levée dès qu'on tente une action réseau désactivée en mode démonstration
 * (voir App\Core\Demo). Interceptée de façon centralisée dans
 * public/index.php pour afficher une page « fonctionnalité indisponible »
 * au lieu de laisser l'appel Stripe échouer/geler.
 */
class DemoModeException extends \RuntimeException
{
}
