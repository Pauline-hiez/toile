<?php
/**
 * Header public. Variables définies par layouts/default.php avant
 * l'inclusion de ce partial (require, pas de scope isolé).
 *
 * @var int $unreadCount Nombre de notifications non lues.
 * @var array $recentNotifications Aperçu des dernières notifications (menu déroulant).
 * @var array|null $userShop Boutique de l'utilisateur connecté (artiste/admin), ou null.
 * @var string|null $currentUserAvatar Nom de fichier de l'avatar courant, ou null.
 * @var \App\Models\Setting $settingModel
 */
?>
<header class="site-header">
    <div class="site-header__inner">

        <a href="/" class="site-header__logo">
            <img src="<?= ($logo = $settingModel->get('site_logo')) ? '/uploads/branding/' . htmlspecialchars($logo) : '/assets/images/site/logo-toile.png' ?>" alt="<?= htmlspecialchars($settingModel->get('site_name', 'Toile')) ?>">
        </a>

        <button type="button" class="site-header__menu-btn" id="siteMenuBtn" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="siteNav">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="7" x2="20" y2="7"></line>
                <line x1="4" y1="12" x2="20" y2="12"></line>
                <line x1="4" y1="17" x2="20" y2="17"></line>
            </svg>
        </button>

        <nav class="site-header__nav" id="siteNav">
            <a href="/boutiques">Boutiques</a>

            <?php if (($_SESSION['user_role'] ?? '') === 'artist'): ?>
                <a href="/raffle">Tirage au sort</a>
            <?php else: ?>
                <a href="/become-artist">Devenir artiste</a>
            <?php endif; ?>

            <a href="/comment-ca-marche">Comment ça marche&nbsp;?</a>
        </nav>

        <div class="site-header__icons">
            <?php
            $searchAction = '/boutiques';
            $searchStandalone = true;
            $searchValue = '';
            ?>
            <?php require __DIR__ . '/../components/search-bar.php'; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/mes-favoris" class="site-header__icon-btn" aria-label="Favoris" title="Favoris">
                    <img src="/assets/images/icones/favoris.png" alt="">
                </a>

                <a href="/mes-commandes" class="site-header__icon-btn" aria-label="Mes commandes" title="Mes commandes">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                        <path d="M3 6h18"></path>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </a>

                <?php
                // Icônes par type de notification (voir Notification::typeInfo()) —
                // même style trait/stroke que les autres icônes SVG du header.
                $notifIcons = [
                    'message' => '<path d="M21 11.5a8.4 8.4 0 0 1-8.9 8.4 9 9 0 0 1-3.5-.8L3 21l1.9-5.7A8.4 8.4 0 1 1 21 11.5Z"></path>',
                    'document' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M9 13h6M9 17h6"></path>',
                    'money' => '<circle cx="12" cy="12" r="9"></circle><path d="M9 15s.8 1 3 1 3-.9 3-2-1.5-1.7-3-2-3-.9-3-2 1.3-2 3-2 3 1 3 1"></path>',
                    'refund' => '<path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 3v5h5"></path>',
                    'check' => '<circle cx="12" cy="12" r="9"></circle><path d="m8.5 12.5 2.5 2.5 5-5"></path>',
                    'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>',
                    'x' => '<circle cx="12" cy="12" r="9"></circle><path d="m9 9 6 6M15 9l-6 6"></path>',
                    'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 3.5 1 5 2 6H4c1-1 2-2.5 2-6Z"></path><path d="M10 20a2 2 0 0 0 4 0"></path>',
                ];
                ?>
                <div class="site-header__notif-wrapper">
                    <a href="/notifications" class="site-header__icon-btn site-header__notif" aria-label="Notifications" title="Notifications" data-notif-toggle>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $notifIcons['bell'] ?></svg>
                        <?php if ($unreadCount > 0): ?>
                            <span class="site-header__notif-badge" data-notif-badge><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="site-header__notif-menu" data-notif-menu>
                        <div class="site-header__notif-menu-inner">
                            <input type="hidden" data-notif-csrf value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

                            <div class="site-header__notif-head">
                                <span class="site-header__notif-head-title">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $notifIcons['bell'] ?></svg>
                                    Notifications
                                </span>
                                <?php if ($unreadCount > 0): ?>
                                    <button type="button" class="site-header__notif-markall" data-notif-markall>Tout marquer comme lu</button>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($recentNotifications)): ?>
                                <p class="site-header__notif-empty">Aucune notification pour l'instant.</p>
                            <?php else: ?>
                                <div class="site-header__notif-list">
                                    <?php foreach ($recentNotifications as $notification): ?>
                                        <?php $typeInfo = \App\Models\Notification::typeInfo($notification['type']); ?>
                                        <a href="<?= htmlspecialchars($notification['link'] ?: '/notifications') ?>"
                                            class="site-header__notif-item<?= $notification['is_read'] ? '' : ' site-header__notif-item--unread' ?>">
                                            <span class="site-header__notif-icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $notifIcons[$typeInfo['icon']] ?? $notifIcons['bell'] ?></svg>
                                            </span>
                                            <span class="site-header__notif-body">
                                                <span class="site-header__notif-item-title"><?= htmlspecialchars($typeInfo['label']) ?></span>
                                                <span class="site-header__notif-message"><?= htmlspecialchars($notification['message']) ?></span>
                                                <span class="site-header__notif-date"><?= \App\Core\RelativeTime::format($notification['created_at']) ?></span>
                                            </span>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="site-header__notif-dot" aria-hidden="true"></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="/notifications" class="site-header__notif-viewall">Voir toutes les notifications →</a>
                        </div>
                    </div>
                </div>

                <div class="site-header__account">
                    <a href="/profile" class="site-header__profile" aria-label="Mon compte" title="Mon compte">
                        <img src="/uploads/avatars/<?= htmlspecialchars($currentUserAvatar ?? 'default.png') ?>" alt="Profil" class="site-header__avatar">
                        <svg class="site-header__account-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>

                    <div class="site-header__account-menu">
                        <div class="site-header__account-menu-inner">
                            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                <a href="/admin">Administration</a>
                                <a href="/profile">Profil</a>
                                <?php if ($userShop): ?>
                                    <a href="/boutiques/<?= htmlspecialchars($userShop['slug']) ?>">Ma boutique</a>
                                <?php endif; ?>
                            <?php elseif (($_SESSION['user_role'] ?? '') === 'artist'): ?>
                                <a href="/my-dashboard">Mon espace</a>
                                <a href="/profile">Profil</a>
                                <?php if ($userShop): ?>
                                    <a href="/my-shop">Ma boutique</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/profile">Profil</a>
                            <?php endif; ?>

                            <hr>
                            <a href="/logout">Se déconnecter</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <a href="/login" data-auth-open="login" class="btn btn--outline">Se connecter</a>
                <a href="/register" data-auth-open="register" class="btn btn--primary">S'inscrire</a>
            <?php endif; ?>
        </div>

    </div>
</header>

<script>
    (function () {
        var menuBtn = document.getElementById('siteMenuBtn');
        var nav = document.getElementById('siteNav');

        menuBtn.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('is-open');
            menuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    })();
</script>
