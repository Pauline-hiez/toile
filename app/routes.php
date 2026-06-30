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

$router->map('GET', '/', ['App\Controllers\HomeController', 'index']);
