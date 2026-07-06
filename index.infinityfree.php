<?php

require __DIR__ . '/vendor/autoload.php';

session_start();

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

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Core\Renderer;

$router = new AltoRouter();

require __DIR__ . '/app/routes.php';

$renderer = new Renderer(__DIR__ . '/app/Views');

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

$controller = new $controllerClass($renderer);
call_user_func_array([$controller, $method], $match['params']);
