<?php
/**
 * Layout admin. Variables injectées par App\Core\Renderer::render()
 * via extract($data) avant l'inclusion de ce fichier :
 *
 * @var string $content Rendu HTML de la vue admin/* englobée.
 * @var string|null $pageTitle Titre de l'onglet navigateur.
 * @var string|null $pageHeading Titre affiché dans le bandeau (ex: "Dashboard").
 * @var string|null $pageSubtitle Sous-titre affiché sous le titre.
 */

use App\Models\Notification;
use App\Models\Shop;
use App\Models\User;
use App\Models\Setting;

$adminUsername = 'Admin';
$adminAvatar = null;
$adminShop = null;
$settingModel = new Setting();

if (isset($_SESSION['user_id'])) {
    $notificationModel = new Notification();
    $unreadCount = $notificationModel->countUnread($_SESSION['user_id']);

    $userModel = new User();
    $currentAdmin = $userModel->findById($_SESSION['user_id']);
    if ($currentAdmin !== null) {
        $adminUsername = $currentAdmin['username'];
        $adminAvatar = $currentAdmin['avatar'] ?? null;
    }

    $shopModel = new Shop();
    $adminShop = $shopModel->findByUserId($_SESSION['user_id']);
} else {
    $unreadCount = 0;
}

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH);

$navItems = [
    ['label' => 'Dashboard', 'href' => '/admin', 'icon' => 'dashboard.png', 'match' => 'exact'],
    ['label' => 'Utilisateurs', 'href' => '/admin/users', 'icon' => 'utilisateurs.png', 'match' => 'prefix'],
    ['label' => 'Boutiques', 'href' => '/admin/shops', 'icon' => 'boutique.png', 'match' => 'prefix'],
    ['label' => 'Commandes', 'href' => '/admin/orders', 'icon' => 'commandes.png', 'match' => 'prefix'],
    ['label' => 'Abonnements', 'href' => '/admin/subscriptions', 'icon' => 'abonnements.png', 'match' => 'prefix'],
    ['label' => 'Tirage au sort', 'href' => '/admin/raffle', 'icon' => 'tirage.png', 'match' => 'prefix'],
    ['label' => 'Signalements', 'href' => '/admin/reports', 'icon' => 'signalements.png', 'match' => 'prefix'],
    ['label' => 'Paramètres', 'href' => '/admin/settings', 'icon' => 'parametres.png', 'match' => 'prefix'],
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Administration — Toile') ?></title>
    <link rel="icon" href="<?= ($favicon = $settingModel->get('site_favicon')) ? '/uploads/branding/' . htmlspecialchars($favicon) : '/assets/images/site/favicon-logo.png' ?>">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/tailwind-config.js"></script>
    <style>
        /* États pilotés par JS (sidebar mobile) — priorité garantie sur les
           utilitaires Tailwind via sélecteurs #id. */
        #adminSidebar {
            transition: transform 0.25s ease;
        }

        @media (max-width: 960px) {
            #adminSidebar {
                transform: translateX(-100%);
            }

            #adminSidebar.is-open {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            }

            #adminSidebarOverlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.35);
                z-index: 99;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
            }

            #adminSidebarOverlay.is-visible {
                opacity: 1;
                pointer-events: auto;
            }

            #adminSidebarIllustration {
                display: none;
            }

            #adminMain {
                margin-left: 0;
            }
        }

        @media (max-height: 650px) {
            #adminSidebarIllustration {
                display: none;
            }
        }

        /* Texture aquarelle du lien de nav actif */
        #adminSidebarNav a.active {
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
    </style>
</head>

<body>
    <div class="flex min-h-screen">
        <div id="adminSidebarOverlay" class="hidden"></div>

        <aside id="adminSidebar" class="w-[220px] bg-bg border-r border-border flex flex-col fixed top-0 left-0 h-screen overflow-y-auto z-[100]">
            <a href="/admin" class="pt-[1.1rem] px-5 pb-[0.85rem] text-center border-b border-border">
                <img src="<?= ($logo = $settingModel->get('site_logo')) ? '/uploads/branding/' . htmlspecialchars($logo) : '/assets/images/site/logo-toile.png' ?>" alt="<?= htmlspecialchars($settingModel->get('site_name', 'Toile')) ?>" class="w-[120px] h-auto mx-auto">
            </a>

            <nav id="adminSidebarNav" class="py-4 flex-1">
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
        </aside>

        <div id="adminSidebarIllustration" class="fixed left-5 bottom-[-35px] z-[110] pointer-events-none">
            <img src="/assets/images/decor/pot.png" alt="" class="block w-[210px] h-auto">
        </div>

        <div id="adminMain" class="ml-[220px] flex-1 flex flex-col min-h-screen">
            <header class="bg-bg flex items-center justify-between gap-6 px-8 py-5 sticky top-0 z-50 max-[720px]:px-5 max-[720px]:py-4 max-[720px]:gap-3">
                <div class="flex items-center gap-4 min-w-0">
                    <button type="button" id="adminMenuBtn" aria-label="Ouvrir le menu" class="hidden max-[960px]:flex items-center justify-center w-10 h-10 border border-border rounded-full bg-white text-title cursor-pointer shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <line x1="4" y1="7" x2="20" y2="7"></line>
                            <line x1="4" y1="12" x2="20" y2="12"></line>
                            <line x1="4" y1="17" x2="20" y2="17"></line>
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <h1 class="font-title text-[2.4rem] max-[720px]:text-[1.9rem] text-title font-semibold leading-none"><?= htmlspecialchars($pageHeading ?? 'Administration') ?></h1>
                        <?php if (!empty($pageSubtitle)): ?>
                            <p class="text-[0.8rem] text-muted max-[720px]:hidden"><?= nl2br(htmlspecialchars($pageSubtitle)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center gap-5">
                    <?php
                    $searchAction = '/admin/users';
                    $searchStandalone = true;
                    $searchValue = '';
                    ?>
                    <?php require __DIR__ . '/../components/search-bar.php'; ?>

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
                            <?php if (!empty($adminAvatar)): ?>
                                <img src="/uploads/avatars/<?= htmlspecialchars($adminAvatar) ?>" alt="Profil" class="w-[38px] h-[38px] rounded-full object-cover border-2 border-border">
                            <?php else: ?>
                                <img src="/uploads/avatars/default.png" alt="Profil" class="w-[38px] h-[38px] rounded-full object-cover border-2 border-border">
                            <?php endif; ?>
                            <span class="max-[480px]:hidden">
                                <strong class="block text-[0.85rem] font-semibold"><?= htmlspecialchars($adminUsername) ?></strong>
                                <span class="text-[0.75rem] text-muted">Administrateur</span>
                            </span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-[14px] h-[14px] text-title shrink-0">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </a>

                        <div class="site-header__account-menu">
                            <div class="site-header__account-menu-inner">
                                <a href="/">Accueil</a>
                                <a href="/profile">Profil</a>
                                <?php if ($adminShop): ?>
                                    <a href="/boutiques/<?= htmlspecialchars($adminShop['slug']) ?>">Ma boutique</a>
                                <?php endif; ?>
                                <hr>
                                <a href="/logout">Se déconnecter</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-8 flex-1 max-[720px]:p-5">
                <?= $content ?>
            </main>

            <?php require __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>

    <script>
        (function () {
            var sidebar = document.getElementById('adminSidebar');
            var overlay = document.getElementById('adminSidebarOverlay');
            var menuBtn = document.getElementById('adminMenuBtn');

            function closeSidebar() {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-visible');
            }

            menuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('is-open');
                overlay.classList.toggle('is-visible');
            });

            overlay.addEventListener('click', closeSidebar);
        })();
    </script>
</body>

</html>
