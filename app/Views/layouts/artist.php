<?php
/**
 * Layout espace artiste. Variables injectées par
 * App\Core\Renderer::render() via extract($data) avant l'inclusion
 * de ce fichier :
 *
 * @var string $content Rendu HTML de la vue englobée.
 * @var string|null $pageTitle Titre de l'onglet navigateur.
 */

use App\Models\Notification;
use App\Models\Shop;
use App\Models\User;
use App\Models\Setting;

$settingModel = new Setting();
$userModel = new User();
$shopModel = new Shop();

$currentArtist = $userModel->findById($_SESSION['user_id']);
$artistShop = $shopModel->findByUserId($_SESSION['user_id']);

$unreadCount = (new Notification())->countUnread($_SESSION['user_id']);
$userShop = $artistShop;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/my-dashboard', PHP_URL_PATH);

$navItems = [
    ['label' => 'Dashboard', 'href' => '/my-dashboard', 'icon' => 'dashboard.png', 'match' => 'exact'],
    ['label' => 'Mon profil', 'href' => '/profile', 'icon' => 'new-user.png', 'match' => 'exact'],
    ['label' => 'Mes commandes', 'href' => '/commandes-recues', 'icon' => 'commande.png', 'match' => 'prefix'],
    ['label' => 'Mes prestations', 'href' => '/my-services', 'icon' => 'pro.png', 'match' => 'prefix'],
    ['label' => 'Portfolio', 'href' => '/my-portfolio', 'icon' => 'create.png', 'match' => 'prefix'],
    ['label' => 'Ma boutique', 'href' => '/my-shop', 'icon' => 'boutique.png', 'match' => 'exact'],
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Mon espace — Toile') ?></title>
    <link rel="icon" href="<?= ($favicon = $settingModel->get('site_favicon')) ? '/uploads/branding/' . htmlspecialchars($favicon) : '/assets/images/site/favicon-logo.png' ?>">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/tailwind-config.js"></script>
</head>

<body>
    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main>
        <div class="relative max-w-[1400px] mx-auto px-5 pt-6 pb-10 min-[641px]:px-10 min-[641px]:pt-8">
            <img src="/assets/images/decor/hero.png" alt="" class="w-full max-h-[420px] object-cover rounded-md mb-8">

            <h1 class="font-cursive text-[2rem] font-semibold text-ink mb-1">Bonjour <?= htmlspecialchars($currentArtist['username'] ?? 'Artiste') ?> !</h1>
            <p class="text-muted text-[0.95rem] mb-8">Bienvenue sur ton espace</p>

            <div class="flex flex-col min-[900px]:flex-row gap-8">
                <nav class="flex flex-row min-[900px]:flex-col flex-wrap gap-2 min-[900px]:w-[220px] shrink-0">
                    <?php foreach ($navItems as $item): ?>
                        <?php
                        $isActive = ($item['match'] === 'exact' && $currentPath === $item['href'])
                            || ($item['match'] === 'prefix' && str_starts_with($currentPath, $item['href']));
                        ?>
                        <a href="<?= htmlspecialchars($item['href']) ?>" class="flex items-center gap-3 px-4 py-[0.6rem] rounded-full text-[0.9rem] font-medium no-underline transition-colors <?= $isActive ? 'bg-primary text-white' : 'text-ink hover:bg-primary-light hover:text-primary' ?>">
                            <img src="/assets/images/icones/<?= $item['icon'] ?>" alt="" class="w-6 h-6 object-contain shrink-0">
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="flex-1 min-w-0">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
