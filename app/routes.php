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
 *   [a:slug] -> alphanumérique uniquement (pas de tiret)
 *   [*:slug] -> tout caractère (utilisé pour les slugs avec tirets), ex: /boutiques/[*:slug]
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

// Devenir artiste
$router->map('GET', '/become-artist', [
    'controller' => ['App\Controllers\ArtistController', 'showRequest'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('POST', '/become-artist', [
    'controller' => ['App\Controllers\ArtistController', 'submitRequest'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Boutique artiste
$router->map('GET', '/my-shop', [
    'controller' => ['App\Controllers\ShopController', 'manage'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-shop', [
    'controller' => ['App\Controllers\ShopController', 'save'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-shop/toggle', [
    'controller' => ['App\Controllers\ShopController', 'toggleOpen'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-shop/toggle-quotes', [
    'controller' => ['App\Controllers\ShopController', 'toggleAcceptsQuotes'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

// Services (côté artiste)
$router->map('GET', '/my-services', [
    'controller' => ['App\Controllers\ServiceController', 'index'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('GET', '/my-services/create', [
    'controller' => ['App\Controllers\ServiceController', 'create'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('GET', '/my-services/[i:id]/edit', [
    'controller' => ['App\Controllers\ServiceController', 'edit'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-services/save', [
    'controller' => ['App\Controllers\ServiceController', 'save'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-services/[i:id]/delete', [
    'controller' => ['App\Controllers\ServiceController', 'delete'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-services/options/[i:id]/delete', [
    'controller' => ['App\Controllers\ServiceController', 'deleteOption'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-services/bases/[i:id]/delete', [
    'controller' => ['App\Controllers\ServiceController', 'deleteBase'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

// Portfolio
$router->map('GET', '/my-portfolio', [
    'controller' => ['App\Controllers\PortfolioController', 'index'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-portfolio/upload', [
    'controller' => ['App\Controllers\PortfolioController', 'upload'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-portfolio/reorder', [
    'controller' => ['App\Controllers\PortfolioController', 'reorder'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-portfolio/[i:id]/label', [
    'controller' => ['App\Controllers\PortfolioController', 'updateLabel'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-portfolio/[i:id]/delete', [
    'controller' => ['App\Controllers\PortfolioController', 'delete'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

// Demande de devis générale (sans prestation précise), soumise depuis la
// modale de shop/show.php.
$router->map('POST', '/boutiques/[*:slug]/devis', [
    'controller' => ['App\Controllers\OrderController', 'storeGeneric'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Boutique
$router->map('GET', '/boutiques/[*:slug]', ['App\Controllers\ShopController', 'show']);

// Recherche 
$router->map('GET', '/boutiques', ['App\Controllers\ShopController', 'search']);

// Tunnel de commande
$router->map('GET', '/commander/[i:serviceId]', [
    'controller' => ['App\Controllers\OrderController', 'create'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('POST', '/commander', [
    'controller' => ['App\Controllers\OrderController', 'store'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Espace client
$router->map('GET', '/mes-commandes', [
    'controller' => ['App\Controllers\OrderController', 'myOrders'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Espace artiste
$router->map('GET', '/my-dashboard', [
    'controller' => ['App\Controllers\ArtistDashboardController', 'index'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('GET', '/commandes-recues', [
    'controller' => ['App\Controllers\OrderController', 'receivedOrders'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

// Etats des commandes
$router->map('POST', '/commandes/[i:id]/transition', [
    'controller' => ['App\Controllers\OrderController', 'transition'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Paiement du prix proposé sur un devis
$router->map('GET', '/commandes/[i:id]/payer-devis', [
    'controller' => ['App\Controllers\OrderController', 'payQuote'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('GET', '/commandes/[i:id]/confirmer-devis', [
    'controller' => ['App\Controllers\OrderController', 'confirmQuotePayment'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Suivi de commande
$router->map('GET', '/commandes/[i:id]', [
    'controller' => ['App\Controllers\OrderController', 'show'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Messagerie de commande
$router->map('POST', '/commandes/[i:id]/messages', [
    'controller' => ['App\Controllers\OrderController', 'sendMessage'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Signalements (avis / images de portfolio)
$router->map('POST', '/reports', [
    'controller' => ['App\Controllers\ReportController', 'store'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Notifications
$router->map('GET', '/notifications', [
    'controller' => ['App\Controllers\NotificationController', 'index'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('POST', '/notifications/mark-read', [
    'controller' => ['App\Controllers\NotificationController', 'markRead'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Avis et commentaires
$router->map('POST', '/commandes/[i:id]/review', [
    'controller' => ['App\Controllers\ReviewController', 'store'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Favoris
$router->map('POST', '/favoris/toggle/[i:shopId]', [
    'controller' => ['App\Controllers\FavoriteController', 'toggle'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('GET', '/mes-favoris', [
    'controller' => ['App\Controllers\FavoriteController', 'index'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Paiement
$router->map('GET', '/commander/confirm', [
    'controller' => ['App\Controllers\OrderController', 'confirm'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

// Admin
$router->map('GET', '/admin', [
    'controller' => ['App\Controllers\AdminController', 'dashboard'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/statistics', [
    'controller' => ['App\Controllers\AdminController', 'statistics'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/artist-requests', [
    'controller' => ['App\Controllers\AdminController', 'artistRequests'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/artist-requests/[i:id]/approve', [
    'controller' => ['App\Controllers\AdminController', 'approveArtistRequest'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/artist-requests/[i:id]/reject', [
    'controller' => ['App\Controllers\AdminController', 'rejectArtistRequest'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/shops', [
    'controller' => ['App\Controllers\AdminController', 'shops'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/shops/[i:id]/delete', [
    'controller' => ['App\Controllers\AdminController', 'deleteShop'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/shops/[i:id]/toggle', [
    'controller' => ['App\Controllers\AdminController', 'toggleShopOpen'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/orders', [
    'controller' => ['App\Controllers\AdminController', 'orders'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/orders/[i:id]/toggle-archive', [
    'controller' => ['App\Controllers\AdminController', 'toggleOrderArchive'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/orders/bulk-archive', [
    'controller' => ['App\Controllers\AdminController', 'bulkArchiveOrders'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/subscriptions', [
    'controller' => ['App\Controllers\AdminController', 'subscriptions'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/raffle', [
    'controller' => ['App\Controllers\AdminController', 'raffle'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/reports', [
    'controller' => ['App\Controllers\AdminController', 'reports'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/reports/[i:id]/resolve', [
    'controller' => ['App\Controllers\AdminController', 'resolveReport'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/reports/[i:id]/dismiss', [
    'controller' => ['App\Controllers\AdminController', 'dismissReport'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/reports/[i:id]/delete', [
    'controller' => ['App\Controllers\AdminController', 'deleteReport'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/reviews', [
    'controller' => ['App\Controllers\AdminController', 'reviews'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/reviews/[i:id]/delete', [
    'controller' => ['App\Controllers\AdminController', 'deleteReview'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/settings', [
    'controller' => ['App\Controllers\AdminController', 'settings'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/settings/general', [
    'controller' => ['App\Controllers\AdminController', 'updateGeneralSettings'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/settings/social', [
    'controller' => ['App\Controllers\AdminController', 'updateSocialSettings'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/settings/raffle', [
    'controller' => ['App\Controllers\AdminController', 'updateRaffleSettings'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/settings/subscriptions', [
    'controller' => ['App\Controllers\AdminController', 'updateSubscriptionPlans'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/settings/maintenance', [
    'controller' => ['App\Controllers\AdminController', 'updateMaintenanceSettings'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('GET', '/admin/users', [
    'controller' => ['App\Controllers\AdminController', 'users'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/users/[i:id]/ban', [
    'controller' => ['App\Controllers\AdminController', 'banUser'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/users/bulk-ban', [
    'controller' => ['App\Controllers\AdminController', 'bulkBanUsers'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/users/[i:id]/unban', [
    'controller' => ['App\Controllers\AdminController', 'unbanUser'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

$router->map('POST', '/admin/users/[i:id]/role', [
    'controller' => ['App\Controllers\AdminController', 'changeRole'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['admin']),
    ],
]);

// Mot de passe oublié
$router->map('GET', '/forgot-password', ['App\Controllers\AuthController', 'showForgotPassword']);
$router->map('POST', '/forgot-password', ['App\Controllers\AuthController', 'forgotPassword']);
$router->map('GET', '/reset-password', ['App\Controllers\AuthController', 'showResetPassword']);
$router->map('POST', '/reset-password', ['App\Controllers\AuthController', 'resetPassword']);

// Google OAuth
$router->map('GET', '/auth/google', ['App\Controllers\AuthController', 'redirectToGoogle']);
$router->map('GET', '/auth/google/callback', ['App\Controllers\AuthController', 'handleGoogleCallback']);

// Abonnements
$router->map('GET', '/my-subscription', [
    'controller' => ['App\Controllers\SubscriptionController', 'index'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-subscription/subscribe', [
    'controller' => ['App\Controllers\SubscriptionController', 'subscribe'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('GET', '/my-subscription/confirm', [
    'controller' => ['App\Controllers\SubscriptionController', 'confirm'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-subscription/cancel', [
    'controller' => ['App\Controllers\SubscriptionController', 'cancel'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/my-subscription/confirm-free', [
    'controller' => ['App\Controllers\SubscriptionController', 'confirmFree'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

// Tirage au sort
$router->map('GET', '/raffle', [
    'controller' => ['App\Controllers\RaffleController', 'index'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/raffle/enter', [
    'controller' => ['App\Controllers\RaffleController', 'enter'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('GET', '/raffle/confirm', [
    'controller' => ['App\Controllers\RaffleController', 'confirm'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

$router->map('POST', '/raffle/cancel', [
    'controller' => ['App\Controllers\RaffleController', 'cancel'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
        fn() => \App\Middleware\RoleMiddleware::handle(['artist']),
    ],
]);

// Méthodes de paiement
$router->map('GET', '/profile/payment-methods', [
    'controller' => ['App\Controllers\UserController', 'paymentMethods'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);

$router->map('POST', '/profile/payment-methods/delete', [
    'controller' => ['App\Controllers\UserController', 'deletePaymentMethod'],
    'middlewares' => [
        fn() => \App\Middleware\AuthMiddleware::handle(),
    ],
]);
