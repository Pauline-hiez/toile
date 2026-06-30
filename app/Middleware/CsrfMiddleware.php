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

        $submittedToken = $_POST['csrf_token'] ?? null;

        if (!Csrf::verify($submittedToken)) {
            http_response_code(403);
            echo 'Requête refusée : jeton de sécurité invalide ou expiré.';
            exit;
        }
    }
}
