<?php

// Défaut sûr pour la fenêtre de bootstrap (avant de connaître APP_ENV) :
// on logge toujours, mais on n'affiche jamais les erreurs à l'écran — pas
// de fuite de chemins/SQL/config si une erreur survient avant le chargement
// du .env. L'affichage est réactivé juste après, uniquement hors production.
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', '0');

// PHP est en UTC par défaut, alors que MySQL suit l'heure système
// (Europe/Paris) — sans ça, tout calcul de durée entre time() et un
// created_at venant de la base est faussé de 1h/2h selon la saison.
date_default_timezone_set('Europe/Paris');

require __DIR__ . '/../vendor/autoload.php';

// Charger le .env EN PREMIER, avant tout autre code
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Erreurs affichées à l'écran uniquement en dev/local ; en production
// (APP_ENV=production) elles restent masquées et sont seulement journalisées.
if (in_array($_ENV['APP_ENV'] ?? 'production', ['development', 'local'], true)) {
    ini_set('display_errors', '1');
}

session_start();

// Headers de sécurité HTTP, posés sur toutes les réponses. La CSP reste
// permissive sur script-src/style-src ('unsafe-inline') car de nombreuses
// vues du site s'appuient sur des <script>/style="" en ligne (config des
// modales, graphiques, crop d'image...) — les retirer serait un chantier
// à part entière. Elle limite quand même réellement la surface d'attaque :
// seuls les domaines listés ci-dessous peuvent charger un script/une
// police/une frame, tout le reste (y compris une exfiltration vers un
// domaine arbitraire via connect-src) est bloqué par défaut.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: "
    . "default-src 'self'; "
    . "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://js.stripe.com; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
    . "font-src 'self' https://fonts.gstatic.com; "
    // blob: nécessaire pour les aperçus d'image et le recadrage Cropper.js,
    // qui affichent des images via URL.createObjectURL() (URL blob:) — avatar,
    // bannières, visuels de style (voir image-crop.js / profile.php).
    . "img-src 'self' data: https: blob:; "
    . "connect-src 'self' https://api.stripe.com; "
    . "frame-src https://js.stripe.com; "
    . "frame-ancestors 'self'; "
    . "base-uri 'self'; "
    . "form-action 'self'; "
    . "object-src 'none';"
);

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
    // Le déclenchement peut être programmé pour plus tard (voir
    // AdminController::updateMaintenanceSettings()) — tant que l'heure
    // prévue n'est pas atteinte, le site reste accessible normalement.
    $maintenanceStartsAt = $settingModel->get('maintenance_starts_at');
    $hasMaintenanceStarted = !empty($maintenanceStartsAt) && strtotime($maintenanceStartsAt) <= time();

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
    $isExemptPath = in_array($requestPath, ['/login', '/logout'], true)
        || str_starts_with($requestPath, '/admin')
        || str_starts_with($requestPath, '/webhooks/');

    if ($hasMaintenanceStarted && !$isAdmin && !$isExemptPath) {
        $durationMinutes = (int) $settingModel->get('maintenance_duration_minutes', '0');
        $endsAt = $durationMinutes > 0
            ? date('Y-m-d H:i:s', strtotime($maintenanceStartsAt) + $durationMinutes * 60)
            : null;

        http_response_code(503);
        (new Renderer(__DIR__ . '/../app/Views'))->render('errors/maintenance', [
            'endsAt' => $endsAt,
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
