<?php

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Vérifie le token CSRF pour toute requête POST, avant le routage
\App\Middleware\CsrfMiddleware::handle();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Core\Renderer;

// On instancie le routeur AltoRouter.
$router = new AltoRouter();

// AltoRouter doit connaître le dossier de base si le projet n'est pas
// à la racine du domaine. Avec Laragon (virtual host), le projet est
// généralement servi à la racine, donc on laisse le préfixe vide.
// $router->setBasePath('/toile');

// On charge toutes les routes définies dans app/routes.php.
// Ce fichier utilise la variable $router définie juste au-dessus.
require __DIR__ . '/../app/routes.php';

// On instancie le service Renderer, utilisé par tous les contrôleurs.
$renderer = new Renderer(__DIR__ . '/../app/Views');

// On demande à AltoRouter de trouver la route correspondant à la requête actuelle.
$match = $router->match();

if ($match === false) {
    http_response_code(404);
    echo '404 — Page non trouvée';
    exit;
}

$target = $match['target'];

if (isset($target['controller'])) {
    foreach ($target['middlewares'] ?? [] as $middleware) {
        $middleware();
    }

    [$controllerClass, $method] = $target['controller'];
} else {
    [$controllerClass, $method] = $target;
}

// On instancie le contrôleur en lui passant le Renderer.
$controller = new $controllerClass($renderer);

// On appelle la méthode du contrôleur, en lui passant les paramètres
// dynamiques de l'URL capturés par AltoRouter (ex: id, slug...).
call_user_func_array([$controller, $method], $match['params']);
