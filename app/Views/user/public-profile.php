<?php
/**
 * Page profil publique d'un utilisateur (accessible sans connexion).
 * Variables injectées par App\Core\Renderer::render() via extract($data)
 * (voir UserController::publicProfile()).
 *
 * @var array $profileUser
 * @var bool $isOwnProfile
 * @var array $reviews Avis laissés par cet utilisateur (voir Review::findByClientId()).
 */
$pageTitle = htmlspecialchars($profileUser['username']) . ' — Toile';

$renderStars = function (float $rating, int $size = 12): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $filled = $i <= round($rating);
        $html .= '<svg viewBox="0 0 24 24" fill="' . ($filled ? 'currentColor' : 'none') . '" stroke="currentColor" stroke-width="1.5" class="w-[' . $size . 'px] h-[' . $size . 'px] ' . ($filled ? 'text-title' : 'text-border') . '">'
            . '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>'
            . '</svg>';
    }
    return $html;
};
?>

<div class="max-w-[700px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <div class="bg-white border border-border rounded-md shadow-sm p-6 mb-6 text-center">
        <img src="<?= !empty($profileUser['avatar']) ? '/uploads/avatars/' . htmlspecialchars($profileUser['avatar']) : '/uploads/avatars/default.png' ?>" alt="<?= htmlspecialchars($profileUser['username']) ?>" class="w-28 h-28 object-cover avatar-circle mx-auto mb-3">

        <h1 class="font-title text-title text-shine text-[1.8rem] mb-1"><?= htmlspecialchars($profileUser['username']) ?></h1>
        <p class="text-[0.8rem] text-muted mb-3">Membre depuis le <?= \App\Core\FrenchDate::format('d MMMM y', $profileUser['created_at']) ?></p>

        <?php if (!empty($profileUser['bio'])): ?>
            <p class="text-[0.85rem] text-ink leading-[1.6] max-w-[480px] mx-auto"><?= nl2br(htmlspecialchars($profileUser['bio'])) ?></p>
        <?php endif; ?>

        <?php if ($isOwnProfile): ?>
            <div class="flex items-center justify-center gap-3 flex-wrap mt-5">
                <a href="/profile" class="btn btn--outline">Modifier mes informations</a>
                <a href="/profile/payment-methods" class="btn btn--outline">Mes moyens de paiement</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white border border-border rounded-md shadow-sm p-6">
        <h2 class="text-base font-semibold text-ink mb-4">Avis laissés</h2>

        <?php if (empty($reviews)): ?>
            <p class="text-muted text-[0.8rem] text-center py-4">Aucun avis pour le moment.</p>
        <?php else: ?>
            <div class="flex flex-col gap-3">
                <?php foreach ($reviews as $review): ?>
                    <div class="border border-border rounded-md p-3">
                        <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                            <a href="/boutiques/<?= htmlspecialchars($review['shop_slug']) ?>" class="font-semibold text-[0.85rem] text-primary no-underline hover:underline"><?= htmlspecialchars($review['shop_name']) ?></a>
                            <div class="flex items-center gap-2">
                                <?= $renderStars((float) $review['rating']) ?>
                                <span class="text-[0.7rem] text-muted"><?= \App\Core\FrenchDate::format('d MMM y', $review['created_at']) ?></span>
                            </div>
                        </div>
                        <?php if (!empty($review['comment'])): ?>
                            <p class="text-[0.8rem] text-muted leading-[1.5]"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
