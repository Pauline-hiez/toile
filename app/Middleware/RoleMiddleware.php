<?php

namespace App\Middleware;

// Bloque l'accès en fonction du rôle de l'utilisateur
class RoleMiddleware
{
    public static function handle(array $allowedRoles): void
    {
        $userRole = $_SESSION['user_role'] ?? null;

        if (!in_array($userRole, $allowedRoles, true)) {
            http_response_code(403);
            (new \App\Core\Renderer(__DIR__ . '/../Views'))->render('errors/403', [
                'pageTitle' => 'Accès refusé — Toile',
            ]);
            exit;
        }
    }
}
