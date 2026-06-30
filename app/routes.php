<?php

/**
 * Fichier centralisé des routes de l'application.
 *
 * Chaque route associe :
 *   - une méthode HTTP (GET, POST, ...)
 *   - un chemin d'URL (avec éventuellement des paramètres dynamiques)
 *   - le contrôleur et la méthode à exécuter
 *
 * Syntaxe AltoRouter pour les paramètres dynamiques :
 *   [i:id]   -> un entier,  ex: /commandes/[i:id]
 *   [a:slug] -> alphanumérique + tiret, ex: /boutiques/[a:slug]
 *
 * Ce fichier reçoit la variable $router (instance d'AltoRouter)
 * déjà créée dans public/index.php.
 */

// Accueil
$router->map('GET', '/', ['App\Controllers\HomeController', 'index']);

// Auth
$router->map('GET', '/register', ['App\Controllers\AuthController', 'showRegister']);
$router->map('POST', '/register', ['App\Controllers\AuthController', 'register']);

$router->map('GET', '/login', ['App\Controllers\AuthController', 'showLogin']);
$router->map('POST', '/login', ['App\Controllers\AuthController', 'login']);
$router->map('GET', '/logout', ['App\Controllers\AuthController', 'logout']);

// Profil
$router->map('GET', '/profile', [
    'controller' => ['App\Controllers\UserController', 'showProfile'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('POST', '/profile', [
    'controller' => ['App\Controllers\UserController', 'updateProfile'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('POST', '/profile/password', [
    'controller' => ['App\Controllers\UserController', 'updatePassword'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);
