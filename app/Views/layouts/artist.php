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
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Models\Setting;

$settingModel = new Setting();
$userModel = new User();
$shopModel = new Shop();

$currentArtist = $userModel->findById($_SESSION['user_id']);
$artistShop = $shopModel->findByUserId($_SESSION['user_id']);

$notificationModel = new Notification();
$unreadCount = $notificationModel->countUnread($_SESSION['user_id']);
$recentNotifications = $notificationModel->findRecentByUserId($_SESSION['user_id']);
$userShop = $artistShop;

// Pastille "Mes commandes" — nombre de commandes en attente d'action,
// vide si la boutique n'existe pas encore (premier passage sans boutique).
$pendingOrdersCount = $artistShop ? (new Order())->countPendingByShopId($artistShop['id']) : 0;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/my-dashboard', PHP_URL_PATH);

$navItems = [
    ['label' => 'Dashboard', 'href' => '/my-dashboard', 'icon' => 'dashboard', 'match' => 'exact'],
    ['label' => 'Mon profil', 'href' => '/profile', 'icon' => 'user', 'match' => 'exact'],
    ['label' => 'Mes commandes', 'href' => '/commandes-recues', 'icon' => 'package', 'match' => 'prefix', 'badge' => $pendingOrdersCount],
    ['label' => 'Mes prestations', 'href' => '/my-services', 'icon' => 'tag', 'match' => 'prefix'],
    ['label' => 'Portfolio', 'href' => '/my-portfolio', 'icon' => 'image', 'match' => 'prefix'],
    ['label' => 'Ma boutique', 'href' => '/my-shop', 'icon' => 'shop', 'match' => 'exact'],
    ['label' => 'Mes paiements', 'href' => '/my-payouts', 'icon' => 'card', 'match' => 'exact'],
];

// SVG des icônes de la sidebar (remplace les anciens PNG) — même
// convention que $notifIcons dans partials/header.php.
$navIcons = [
    'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect>',
    'user' => '<circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"></path>',
    'package' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path>',
    'tag' => '<path d="M12.6 2H4a2 2 0 0 0-2 2v8.6a2 2 0 0 0 .6 1.4l9 9a2 2 0 0 0 2.8 0l7.6-7.6a2 2 0 0 0 0-2.8l-9-9a2 2 0 0 0-1.4-.6Z"></path><circle cx="7.5" cy="7.5" r="1.5"></circle>',
    'image' => '<rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path>',
    'shop' => '<path d="M3 9.5 4.5 3h15L21 9.5"></path><path d="M3 9.5V20a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9.5"></path><path d="M9 21v-6h6v6"></path><path d="M3 9.5h18"></path>',
    'card' => '<rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>',
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

        /* Un item de nav a été ajouté (Mes paiements) — masquer
           l'illustration décorative un peu plus tôt pour ne pas
           chevaucher le dernier item en bas de liste. */
        @media (max-height: 800px) {
            #artistSidebarIllustration {
                display: none;
            }
        }

        /* Icônes de nav vertes au repos, blanches sur le lien actif (même
           fond vert) — même principe que la sidebar admin. */
        #artistSidebarNav .nav-icon {
            color: var(--color-primary);
        }

        #artistSidebarNav a.active .nav-icon {
            color: var(--color-white);
        }
    </style>
</head>

<body>
    <aside id="artistSidebar" class="flex flex-col min-[900px]:fixed min-[900px]:top-0 min-[900px]:left-0 min-[900px]:w-[220px] min-[900px]:h-screen min-[900px]:bg-bg min-[900px]:border-r min-[900px]:border-border min-[900px]:z-[100] max-[899px]:max-w-[1400px] max-[899px]:mx-auto max-[899px]:w-full max-[899px]:px-5 max-[899px]:pt-6 min-[641px]:max-[899px]:px-10 min-[641px]:max-[899px]:pt-8">
        <a href="/my-dashboard" class="shrink-0 text-center border-b border-border">
            <img src="<?= ($logo = $settingModel->get('site_logo')) ? '/uploads/branding/' . htmlspecialchars($logo) : '/assets/images/site/logo-toile.png' ?>" alt="<?= htmlspecialchars($settingModel->get('site_name', 'Toile')) ?>" class="w-[190px] h-auto mx-auto block m-0 p-0">
        </a>

        <nav id="artistSidebarNav" class="flex flex-row min-[900px]:flex-col flex-wrap min-[900px]:flex-nowrap gap-1 pt-2 min-[900px]:py-4 min-[900px]:overflow-y-auto min-[900px]:min-h-0 min-[900px]:shrink-0">
            <?php foreach ($navItems as $item): ?>
                <?php
                $isActive = ($item['match'] === 'exact' && $currentPath === $item['href'])
                    || ($item['match'] === 'prefix' && str_starts_with($currentPath, $item['href']));
                ?>
                <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $isActive ? 'active' : '' ?> flex items-center gap-3 px-5 py-[0.45rem] text-ink no-underline text-[0.9rem] font-medium rounded-sm mx-2 my-[0.1rem] transition-colors hover:bg-primary-light hover:text-primary">
                    <span class="w-9 h-9 shrink-0 flex items-center justify-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="nav-icon w-[22px] h-[22px]"><?= $navIcons[$item['icon']] ?? '' ?></svg>
                    </span>
                    <span class="flex-1 min-w-0 truncate"><?= htmlspecialchars($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="shrink-0 inline-flex items-center justify-center min-w-[20px] h-5 px-[5px] rounded-full bg-danger text-white text-[0.7rem] font-semibold"><?= (int) $item['badge'] ?></span>
                    <?php endif; ?>
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
                        <h1 class="font-title text-shine text-[2.4rem] max-[720px]:text-[1.9rem] text-title font-semibold leading-none"><?= htmlspecialchars($pageHeading ?? 'Mon espace') ?></h1>
                        <?php if (!empty($pageSubtitle)): ?>
                            <p class="text-[0.8rem] text-muted max-[720px]:hidden"><?= nl2br(htmlspecialchars($pageSubtitle)) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-5">
                        <?php require __DIR__ . '/../components/notif-dropdown.php'; ?>

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
                <img src="<?= $heroBanner ?>" alt="" class="relative w-full aspect-[579/160] object-cover shop-banner-frame -mb-10">

                <div class="relative">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

    <?php require __DIR__ . '/../components/contact-modal.php'; ?>
    <script src="/assets/js/input-sparks.js"></script>
    <script src="/assets/js/contact-modal.js"></script>
    <script src="/assets/js/notif-dropdown.js"></script>
</body>

</html>
