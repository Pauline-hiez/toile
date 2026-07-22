<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// PHP est en UTC par défaut, alors que MySQL suit l'heure système
// (Europe/Paris) — sans ça, tout calcul de durée entre time() et un
// created_at venant de la base est faussé de 1h/2h selon la saison.
date_default_timezone_set('Europe/Paris');

require __DIR__ . '/../vendor/autoload.php';

// Charger le .env EN PREMIER, avant tout autre code
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

if (!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {
    $rememberTokenModel = new \App\Models\RememberToken();
    $rememberEntry = $rememberTokenModel->findValid($_COOKIE['remember_token']);

    if ($rememberEntry !== null) {
        $userModel = new \App\Models\User();
        $rememberedUser = $userModel->findById($rememberEntry['user_id']);

        if ($rememberedUser !== null && !$rememberedUser['is_banned']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $rememberedUser['id'];
            $_SESSION['user_role'] = $rememberedUser['role'];

            // Rotation du jeton à chaque reconnexion automatique : limite
            // la fenêtre d'exploitation si le cookie venait à fuiter.
            $rememberTokenModel->delete($rememberEntry['id']);
            $newToken = $rememberTokenModel->issue($rememberedUser['id']);
            setcookie('remember_token', $newToken, [
                'expires' => time() + 30 * 86400,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
        }
    } else {
        setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
    }
}

if (isset($_SESSION['user_id'])) {
    $userModel = new \App\Models\User();
    $currentUser = $userModel->findById($_SESSION['user_id']);

    if ($currentUser && $currentUser['is_banned']) {
        session_destroy();
        header('Location: /login?banned=1');
        exit;
    }

    if ($currentUser && $_SESSION['user_role'] !== $currentUser['role']) {
        $_SESSION['user_role'] = $currentUser['role'];
    }
}

\App\Middleware\CsrfMiddleware::handle();

use App\Core\Renderer;

$settingModel = new \App\Models\Setting();
if ($settingModel->get('maintenance_mode', '0') === '1') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
    $isExemptPath = in_array($requestPath, ['/login', '/logout'], true) || str_starts_with($requestPath, '/admin');

    if (!$isAdmin && !$isExemptPath) {
        http_response_code(503);
        (new Renderer(__DIR__ . '/../app/Views'))->render('errors/maintenance', [
            'message' => $settingModel->get('maintenance_message') ?: 'Le site est actuellement en maintenance, merci de revenir un peu plus tard.',
            'pageTitle' => 'Maintenance — ' . $settingModel->get('site_name', 'Toile'),
        ], false);
        exit;
    }
}

$router = new AltoRouter();

require __DIR__ . '/../app/routes.php';

$renderer = new Renderer(__DIR__ . '/../app/Views');

$match = $router->match();

if ($match === false) {
    http_response_code(404);
    $renderer->render('errors/404', ['pageTitle' => 'Page introuvable — Toile']);
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

$controller = new $controllerClass($renderer);
call_user_func_array([$controller, $method], $match['params']);
