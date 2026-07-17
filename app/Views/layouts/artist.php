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
    ['label' => 'Mon profil', 'href' => '/profile', 'icon' => 'profile.png', 'match' => 'exact'],
    ['label' => 'Mes commandes', 'href' => '/commandes-recues', 'icon' => 'commandes.png', 'match' => 'prefix'],
    ['label' => 'Mes prestations', 'href' => '/my-services', 'icon' => 'prestations.png', 'match' => 'prefix'],
    ['label' => 'Portfolio', 'href' => '/my-portfolio', 'icon' => 'portfolio.png', 'match' => 'prefix'],
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
    <style>
        /* Texture aquarelle du lien de nav actif — même traitement que la sidebar admin */
        #artistSidebarNav a.active {
            background-color: var(--color-primary);
            background-image:
                radial-gradient(circle at 18% 25%, rgba(255, 255, 255, 0.4) 0%, transparent 42%),
                radial-gradient(circle at 78% 15%, rgba(0, 0, 0, 0.28) 0%, transparent 48%),
                radial-gradient(circle at 65% 80%, rgba(255, 255, 255, 0.32) 0%, transparent 50%),
                radial-gradient(circle at 12% 82%, rgba(0, 0, 0, 0.26) 0%, transparent 46%),
                radial-gradient(circle at 45% 45%, rgba(255, 255, 255, 0.16) 0%, transparent 60%);
            background-blend-mode: soft-light, multiply, soft-light, multiply, soft-light;
            color: var(--color-white);
            border-radius: 999px;
        }

        @media (max-height: 750px) {
            #artistSidebarIllustration {
                display: none;
            }
        }
    </style>
</head>

<body>
    <aside id="artistSidebar" class="flex flex-col min-[900px]:fixed min-[900px]:top-0 min-[900px]:left-0 min-[900px]:w-[220px] min-[900px]:h-screen min-[900px]:bg-bg min-[900px]:border-r min-[900px]:border-border min-[900px]:z-[100] max-[899px]:max-w-[1400px] max-[899px]:mx-auto max-[899px]:w-full max-[899px]:px-5 max-[899px]:pt-6 min-[641px]:max-[899px]:px-10 min-[641px]:max-[899px]:pt-8">
        <a href="/my-dashboard" class="shrink-0 pt-[1.1rem] px-5 pb-[0.85rem] text-center border-b border-border">
            <img src="<?= ($logo = $settingModel->get('site_logo')) ? '/uploads/branding/' . htmlspecialchars($logo) : '/assets/images/site/logo-toile.png' ?>" alt="<?= htmlspecialchars($settingModel->get('site_name', 'Toile')) ?>" class="w-[120px] h-auto mx-auto">
        </a>

        <nav id="artistSidebarNav" class="flex flex-row min-[900px]:flex-col flex-wrap min-[900px]:flex-nowrap gap-1 pt-2 min-[900px]:py-4 min-[900px]:overflow-y-auto min-[900px]:min-h-0 min-[900px]:shrink-0">
            <?php foreach ($navItems as $item): ?>
                <?php
                $isActive = ($item['match'] === 'exact' && $currentPath === $item['href'])
                    || ($item['match'] === 'prefix' && str_starts_with($currentPath, $item['href']));
                ?>
                <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $isActive ? 'active' : '' ?> flex items-center gap-3 px-5 py-[0.45rem] text-ink no-underline text-[0.9rem] font-medium rounded-sm mx-2 my-[0.1rem] transition-colors hover:bg-primary-light hover:text-primary">
                    <img src="/assets/images/icones/<?= $item['icon'] ?>" alt="" class="w-9 h-9 object-contain shrink-0">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div id="artistSidebarIllustration" class="hidden min-[900px]:block min-[900px]:shrink-0 mt-16 pointer-events-none">
            <img src="/assets/images/decor/main.png?v=<?= filemtime(__DIR__ . '/../../../public/assets/images/decor/main.png') ?>" alt="" class="h-[240px] w-auto max-w-none object-contain">
        </div>
    </aside>

    <main>
        <div class="relative max-w-[1400px] mx-auto px-5 pt-6 pb-10 min-[641px]:px-10 min-[641px]:pt-8">
            <div class="min-[900px]:ml-[220px]">
                <header class="flex items-center justify-between gap-6 pb-6 mb-8 flex-wrap max-[720px]:gap-3">
                    <div class="min-w-0">
                        <h1 class="font-title text-[2.4rem] max-[720px]:text-[1.9rem] text-title font-semibold leading-none"><?= htmlspecialchars($pageHeading ?? 'Mon espace') ?></h1>
                        <?php if (!empty($pageSubtitle)): ?>
                            <p class="text-[0.8rem] text-muted max-[720px]:hidden"><?= nl2br(htmlspecialchars($pageSubtitle)) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-5">
                        <a href="/notifications" aria-label="Notifications" title="Notifications" class="relative cursor-pointer">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-title">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <?php if ($unreadCount > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-danger text-white text-[0.65rem] font-semibold rounded-full px-1 min-w-[16px] h-4 flex items-center justify-center"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </a>

                        <div class="site-header__account">
                            <a href="/profile" class="flex items-center gap-3 cursor-pointer no-underline text-inherit">
                                <?php if (!empty($currentArtist['avatar'])): ?>
                                    <img src="/uploads/avatars/<?= htmlspecialchars($currentArtist['avatar']) ?>" alt="Profil" class="w-[38px] h-[38px] rounded-full object-cover border-2 border-border">
                                <?php else: ?>
                                    <img src="/uploads/avatars/default.png" alt="Profil" class="w-[38px] h-[38px] rounded-full object-cover border-2 border-border">
                                <?php endif; ?>
                                <span class="max-[480px]:hidden">
                                    <strong class="block text-[0.85rem] font-semibold"><?= htmlspecialchars($currentArtist['username'] ?? 'Artiste') ?></strong>
                                    <span class="text-[0.75rem] text-muted">Artiste</span>
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-[14px] h-[14px] text-title shrink-0">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </a>

                            <div class="site-header__account-menu">
                                <div class="site-header__account-menu-inner">
                                    <a href="/">Accueil</a>
                                    <a href="/profile">Profil</a>
                                    <?php if ($artistShop): ?>
                                        <a href="/my-shop">Ma boutique</a>
                                    <?php endif; ?>
                                    <hr>
                                    <a href="/logout">Se déconnecter</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <?php $heroBanner = !empty($artistShop['banner']) ? '/uploads/banners/' . htmlspecialchars($artistShop['banner']) : '/assets/images/decor/hero.png'; ?>
                <img src="<?= $heroBanner ?>" alt="" class="relative w-full aspect-[579/226] object-cover shop-banner-shape -mb-10">

                <div class="relative">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
