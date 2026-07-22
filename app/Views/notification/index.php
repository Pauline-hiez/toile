<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir NotificationController::index()).
 *
 * @var array $notifications
 */
$pageTitle = 'Notifications — Toile';

// Icônes par type de notification — même map que partials/header.php
// (menu déroulant), dupliquée ici volontairement (pas de partial partagé
// pour ce petit tableau, cohérent avec l'existant).
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

<div class="max-w-[800px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-8">Notifications</h1>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <?php if (empty($notifications)): ?>
            <div class="text-center p-10">
                <p class="text-muted text-[0.85rem] mb-4">Tu n'as aucune notification pour l'instant.</p>
                <a href="/" class="btn btn--primary">Retour à l'accueil</a>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $i => $notification): ?>
                <?php $typeInfo = \App\Models\Notification::typeInfo($notification['type']); ?>
                <div class="flex items-start gap-4 p-5 <?= $i > 0 ? 'border-t border-border' : '' ?> <?= $notification['is_read'] ? '' : 'bg-primary-light' ?>">
                    <span class="w-10 h-10 rounded-full bg-white border border-border shrink-0 flex items-center justify-center text-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><?= $notifIcons[$typeInfo['icon']] ?? $notifIcons['bell'] ?></svg>
                    </span>

                    <div class="flex-1 min-w-0">
                        <?php if ($notification['link']): ?>
                            <a href="<?= htmlspecialchars($notification['link']) ?>" class="text-ink font-medium no-underline hover:text-primary transition-colors">
                                <?= htmlspecialchars($notification['message']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-ink font-medium"><?= htmlspecialchars($notification['message']) ?></span>
                        <?php endif; ?>
                        <div class="text-[0.78rem] text-muted mt-1"><?= \App\Core\RelativeTime::format($notification['created_at']) ?></div>
                    </div>

                    <?php if (!$notification['is_read']): ?>
                        <span class="w-2 h-2 rounded-full bg-primary mt-2 shrink-0" aria-hidden="true"></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
