<?php

namespace App\Middleware;

use App\Core\Csrf;

// Bloque les requêtes POST si token invalide
class CsrfMiddleware
{
    public static function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Le webhook Stripe est appelé directement par Stripe (pas de
        // session/formulaire, donc pas de jeton CSRF possible) — sa
        // sécurité repose sur la vérification de signature Stripe elle-même
        // (voir StripeWebhookController::handle()).
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($requestPath === '/webhooks/stripe') {
            return;
        }

        $submittedToken = $_POST['csrf_token'] ?? null;

        if (!Csrf::verify($submittedToken)) {
            http_response_code(403);
            echo 'Requête refusée : jeton de sécurité invalide ou expiré.';
            exit;
        }
    }
}
