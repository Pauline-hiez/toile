<?php
/**
 * Header public. Variables définies par layouts/default.php avant
 * l'inclusion de ce partial (require, pas de scope isolé).
 *
 * @var int $unreadCount Nombre de notifications non lues.
 * @var array|null $userShop Boutique de l'utilisateur connecté (artiste/admin), ou null.
 */
?>
<header class="site-header">
    <div class="site-header__inner">

        <a href="/" class="site-header__logo">
            <img src="/assets/images/site/logo-toile.png" alt="Toile">
        </a>

        <nav class="site-header__nav">
            <a href="/boutiques">Boutiques</a>

            <?php if (($_SESSION['user_role'] ?? '') !== 'artist'): ?>
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

                <a href="/notifications" class="site-header__icon-btn site-header__notif" aria-label="Notifications" title="Notifications">
                    <img src="/assets/images/icones/notifications.png" alt="">
                    <?php if ($unreadCount > 0): ?>
                        <span class="site-header__notif-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>

                <div class="site-header__account">
                    <a href="/profile" class="site-header__profile" aria-label="Mon compte" title="Mon compte">
                        <?php if (!empty($_SESSION['user_avatar'])): ?>
                            <img src="/uploads/avatars/<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Profil" class="site-header__avatar">
                        <?php else: ?>
                            <img src="/assets/images/icones/new-user.png" alt="Profil" class="site-header__avatar">
                        <?php endif; ?>
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
                                <a href="/profile">Profil</a>
                                <?php if ($userShop): ?>
                                    <a href="/boutiques/<?= htmlspecialchars($userShop['slug']) ?>">Voir ma boutique</a>
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
                <a href="/login" class="btn btn--outline">Se connecter</a>
                <a href="/register" class="btn btn--primary">S'inscrire</a>
            <?php endif; ?>
        </div>

    </div>
</header>
