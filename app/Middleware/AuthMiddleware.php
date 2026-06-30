<?php

namespace App\Middleware;

// Bloque l'accès à une route si un utilisateur n'est pas connecté
class AuthMiddleware
{
    public static function handle(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
}
