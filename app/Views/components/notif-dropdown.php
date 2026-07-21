<?php
/**
 * Menu déroulant de notifications ("aperçu à la Facebook"), réutilisé dans
 * partials/header.php, layouts/artist.php et layouts/admin.php. Requiert
 * $unreadCount et $recentNotifications dans le scope appelant, ainsi que
 * le script notif-dropdown.js chargé sur la page.
 *
 * @var int $unreadCount
 * @var array $recentNotifications
 */
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
