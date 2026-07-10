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

$adminUsername = 'Admin';
$adminAvatar = null;
$adminShop = null;

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
    ['label' => 'Utilisateurs', 'href' => '/admin/users', 'icon' => 'users.png', 'match' => 'prefix'],
    ['label' => 'Boutiques', 'href' => '/admin/shops', 'icon' => 'artiste.png', 'match' => 'prefix'],
    ['label' => 'Commandes', 'href' => '/admin/orders', 'icon' => 'commandes.png', 'match' => 'prefix'],
    ['label' => 'Abonnements', 'href' => '/admin/subscriptions', 'icon' => 'commissions.png', 'match' => 'prefix'],
    ['label' => 'Tirage au sort', 'href' => '/admin/raffle', 'icon' => 'abonnements.png', 'match' => 'prefix'],
    ['label' => 'Signalements', 'href' => '/admin/reviews', 'icon' => 'avertissements.png', 'match' => 'prefix'],
    ['label' => 'Paramètres', 'href' => '#', 'icon' => 'parametres.png', 'match' => 'none'],
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Administration — Toile') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
</head>

<body>
    <div class="admin-wrapper">
        <div class="admin-sidebar__overlay" id="adminSidebarOverlay"></div>

        <aside class="admin-sidebar" id="adminSidebar">
            <a href="/admin" class="admin-sidebar__logo">
                <img src="/assets/images/site/logo-toile.png" alt="Toile">
            </a>

            <nav class="admin-sidebar__nav">
                <?php foreach ($navItems as $item): ?>
                    <?php
                    $isActive = ($item['match'] === 'exact' && $currentPath === $item['href'])
                        || ($item['match'] === 'prefix' && str_starts_with($currentPath, $item['href']));
                    ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $isActive ? 'active' : '' ?>">
                        <img src="/assets/images/icones/<?= $item['icon'] ?>" alt="">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="admin-sidebar__illustration">
            <img src="/assets/images/decor/pot.png" alt="">
        </div>

        <div class="admin-main">
            <header class="admin-header">
                <div class="admin-header__left">
                    <button type="button" class="admin-header__menu-btn" id="adminMenuBtn" aria-label="Ouvrir le menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="7" x2="20" y2="7"></line>
                            <line x1="4" y1="12" x2="20" y2="12"></line>
                            <line x1="4" y1="17" x2="20" y2="17"></line>
                        </svg>
                    </button>

                    <div class="admin-header__title">
                        <h1><?= htmlspecialchars($pageHeading ?? 'Administration') ?></h1>
                        <?php if (!empty($pageSubtitle)): ?>
                            <p><?= nl2br(htmlspecialchars($pageSubtitle)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-header__actions">
                    <?php
                    $searchAction = '/admin/users';
                    $searchStandalone = true;
                    $searchValue = '';
                    ?>
                    <?php require __DIR__ . '/../components/search-bar.php'; ?>

                    <a href="/notifications" class="admin-header__bell" aria-label="Notifications" title="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <?php if ($unreadCount > 0): ?>
                            <span class="admin-header__bell-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="site-header__account">
                        <a href="/profile" class="admin-header__profile">
                            <?php if (!empty($adminAvatar)): ?>
                                <img src="/uploads/avatars/<?= htmlspecialchars($adminAvatar) ?>" alt="Profil">
                            <?php else: ?>
                                <img src="/assets/images/icones/new-user.png" alt="Profil">
                            <?php endif; ?>
                            <span>
                                <strong><?= htmlspecialchars($adminUsername) ?></strong>
                                <span>Administrateur</span>
                            </span>
                            <svg class="admin-header__profile-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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

            <main class="admin-content">
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
