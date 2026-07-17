<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ShopController::show()).
 *
 * @var array $shop
 * @var array $services
 * @var array $portfolioImages
 * @var array{average: float, count: int} $ratingStats
 * @var array $reviews
 * @var bool $isFavorite
 * @var array|null $artist
 * @var bool $previewMode Aperçu propriétaire : affiche aussi les prestations inactives.
 * @var string $tab 'portfolio'|'prestations'|'avis'
 */
$pageTitle = htmlspecialchars($shop['name']) . ' — Toile';

$styles = $shop['styles'] ? json_decode($shop['styles'], true) : [];

// Préserve le mode aperçu et l'onglet courant dans les liens qui doivent
// ramener sur cette même vue (ex: redirection après le toggle favoris).
$currentUrl = $_SERVER['REQUEST_URI'];

$tabUrl = function (string $t) use ($shop, $previewMode) {
    $params = array_filter(['tab' => $t, 'preview' => $previewMode ? 1 : null]);
    return '/boutiques/' . htmlspecialchars($shop['slug']) . '?' . http_build_query($params);
};

$renderStars = function (?float $rating, int $size = 14): string {
    $rating = $rating ?? 0;
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

<div class="max-w-[1100px] mx-auto px-5 min-[641px]:px-10 pt-4">
    <?php if (!empty($shop['banner'])): ?>
        <img src="/uploads/banners/<?= htmlspecialchars($shop['banner']) ?>" alt="Bannière" class="w-full aspect-[579/226] object-cover shop-banner-shape">
    <?php endif; ?>

    <div class="flex flex-col min-[641px]:flex-row min-[641px]:items-center justify-between gap-3 <?= !empty($shop['banner']) ? '-mt-28 min-[641px]:-mt-40' : 'mt-4' ?> relative z-10">
        <div class="flex flex-col min-[420px]:flex-row min-[420px]:items-center gap-4">
            <?php if (!empty($artist['avatar'])): ?>
                <img src="/uploads/avatars/<?= htmlspecialchars($artist['avatar']) ?>" alt="<?= htmlspecialchars($shop['name']) ?>" class="w-56 min-[641px]:w-80 aspect-[463/431] object-cover avatar-shape border-4 border-white bg-bg shadow-sm shrink-0">
            <?php else: ?>
                <img src="/uploads/avatars/default.png" alt="" class="w-56 min-[641px]:w-80 aspect-[463/431] object-cover avatar-shape border-4 border-white bg-bg shadow-sm shrink-0">
            <?php endif; ?>

            <div class="pb-1">
                <h1 class="font-cursive text-[1.3rem] min-[641px]:text-[1.6rem] font-semibold text-ink leading-tight"><?= htmlspecialchars($shop['name']) ?></h1>
                <?php if (!empty($styles)): ?>
                    <p class="text-[0.85rem] text-muted"><?= htmlspecialchars(implode(', ', array_map('ucfirst', $styles))) ?></p>
                <?php endif; ?>
                <?php if (!empty($shop['bio'])): ?>
                    <p class="mt-2 text-[0.8rem] text-muted leading-[1.6] max-w-[420px]"><?= nl2br(htmlspecialchars($shop['bio'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-col items-start min-[641px]:items-end gap-1 pb-1">
            <span class="inline-flex items-center gap-2 text-[0.78rem] text-muted">
                <?= $renderStars($ratingStats['average']) ?>
                <?php if ($ratingStats['count'] > 0): ?>
                    <?= number_format($ratingStats['average'], 1) ?> (<?= $ratingStats['count'] ?> avis)
                <?php else: ?>
                    Pas encore d'avis
                <?php endif; ?>
            </span>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="/favoris/toggle/<?= $shop['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($currentUrl) ?>">
                    <button type="submit" class="inline-flex items-center gap-[0.3rem] text-[0.78rem] <?= $isFavorite ? 'text-danger' : 'text-muted' ?> bg-transparent border-0 cursor-pointer hover:text-danger transition-colors">
                        <svg viewBox="0 0 24 24" fill="<?= $isFavorite ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-[16px] h-[16px]">
                            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
                        </svg>
                        <?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                    </button>
                </form>
            <?php endif; ?>

            <?php
            $socialLinks = array_filter([
                'instagram' => $shop['social_instagram'] ?? null,
                'facebook' => $shop['social_facebook'] ?? null,
                'pinterest' => $shop['social_pinterest'] ?? null,
                'tiktok' => $shop['social_tiktok'] ?? null,
            ]);
            ?>
            <?php if (!empty($socialLinks)): ?>
                <div class="flex items-center gap-2">
                    <?php foreach ($socialLinks as $network => $url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars(ucfirst($network)) ?>">
                            <img src="/assets/images/reseaux/<?= $network ?>.png" alt="<?= htmlspecialchars(ucfirst($network)) ?>" class="w-8 h-8 object-contain">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== (int) $shop['user_id']): ?>
                <button
                    type="button"
                    class="text-[0.7rem] text-muted bg-transparent border-0 cursor-pointer hover:text-danger transition-colors"
                    data-report-trigger
                    data-report-type="shop"
                    data-report-id="<?= $shop['id'] ?>"
                    data-report-reason="autre"
                    data-report-redirect="/boutiques/<?= htmlspecialchars($shop['slug']) ?>?reported=1">
                    🚩 Signaler cette boutique
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['reported'])): ?>
        <div class="bg-success-bg border border-success/25 text-success rounded-md px-4 py-[0.7rem] mt-4 text-[0.8rem]">
            Merci, ton signalement a bien été envoyé à notre équipe.
        </div>
    <?php endif; ?>

    <?php if (!$shop['is_open']): ?>
        <div class="bg-warning-bg border border-warning/25 text-warning rounded-md px-4 py-[0.7rem] mt-4 text-[0.8rem]">
            Cette boutique est actuellement fermée aux commandes.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['quote_sent'])): ?>
        <div class="bg-success-bg border border-success/25 text-success rounded-md px-4 py-[0.7rem] mt-4 text-[0.8rem] text-center max-w-[420px] mx-auto">
            Ta demande de devis a bien été envoyée. L'artiste va l'examiner et te proposer un prix.
        </div>
    <?php endif; ?>

    <?php if (!empty($shop['accepts_quotes'])): ?>
        <?php if (isset($_GET['quote_error'])): ?>
            <div class="bg-danger-bg border border-danger/25 text-danger rounded-md px-4 py-[0.7rem] mt-4 text-[0.8rem] text-center max-w-[420px] mx-auto">
                Décris ton projet en au moins 10 caractères pour envoyer ta demande.
            </div>
        <?php endif; ?>
        <div class="flex justify-center mt-6">
            <button type="button" data-quote-open class="inline-flex items-center justify-center w-[190px] h-[62px] bg-[url('/assets/images/decor/btn.png')] bg-contain bg-no-repeat bg-center font-cursive font-semibold text-success text-[0.85rem] border-0 cursor-pointer hover:opacity-85 transition-opacity">
                Demander un devis
            </button>
        </div>
    <?php endif; ?>

    <nav class="flex items-center justify-center gap-5 min-[641px]:gap-8 mt-6 mb-6 border-b border-border">
        <a href="<?= $tabUrl('portfolio') ?>" class="pb-2 font-cursive text-[0.9rem] min-[641px]:text-[0.98rem] font-semibold no-underline border-b-2 -mb-px transition-colors <?= $tab === 'portfolio' ? 'text-primary border-primary' : 'text-muted border-transparent hover:text-ink' ?>">Portfolio</a>
        <a href="<?= $tabUrl('prestations') ?>" class="pb-2 font-cursive text-[0.9rem] min-[641px]:text-[0.98rem] font-semibold no-underline border-b-2 -mb-px transition-colors <?= $tab === 'prestations' ? 'text-primary border-primary' : 'text-muted border-transparent hover:text-ink' ?>">Prestations</a>
        <a href="<?= $tabUrl('avis') ?>" class="pb-2 font-cursive text-[0.9rem] min-[641px]:text-[0.98rem] font-semibold no-underline border-b-2 -mb-px transition-colors <?= $tab === 'avis' ? 'text-primary border-primary' : 'text-muted border-transparent hover:text-ink' ?>">Avis</a>
    </nav>

    <?php if ($tab === 'portfolio'): ?>
        <div class="pb-8">
            <?php if (empty($portfolioImages)): ?>
                <p class="text-muted text-[0.8rem] text-center py-8">Aucune image de portfolio pour le moment.</p>
            <?php else: ?>
                <div class="grid grid-cols-2 min-[500px]:grid-cols-3 min-[768px]:grid-cols-4 min-[1000px]:grid-cols-5 gap-3">
                    <?php foreach ($portfolioImages as $image): ?>
                        <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
                            <img src="/uploads/portfolio/<?= htmlspecialchars($image['filename']) ?>" alt="<?= htmlspecialchars($image['label'] ?? '') ?>" class="w-full aspect-[3/4] object-cover block">
                            <?php if (!empty($image['label'])): ?>
                                <p class="text-[0.7rem] text-muted px-2 py-1 truncate"><?= htmlspecialchars($image['label']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($tab === 'prestations'): ?>
        <div class="pb-8">
            <?php if (empty($services)): ?>
                <p class="text-muted text-[0.8rem] text-center py-8">Aucune prestation disponible pour le moment.</p>
            <?php else: ?>
                <div class="flex flex-col gap-3">
                    <?php foreach ($services as $service): ?>
                        <div id="service-<?= $service['id'] ?>" class="bg-white border border-border rounded-md shadow-sm p-3 flex flex-col min-[561px]:flex-row gap-3">
                            <?php if (!empty($service['image'])): ?>
                                <img src="/uploads/services/<?= htmlspecialchars($service['image']) ?>" alt="" class="w-full min-[561px]:w-[100px] aspect-[3/4] min-[561px]:h-[133px] object-cover rounded-md border border-border shrink-0">
                            <?php endif; ?>

                            <div class="flex-1 flex flex-col">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <h3 class="font-semibold text-[0.9rem] text-ink"><?= htmlspecialchars($service['title']) ?></h3>
                                    <?php if ($previewMode && !$service['is_active']): ?>
                                        <span class="<?= \App\Core\Badge::classes('neutral') ?>">Inactive — non visible publiquement</span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-[0.78rem] text-muted mb-2">
                                    à partir de <strong class="text-ink"><?= number_format($service['base_price'] / 100, 2) ?> €</strong>
                                    · délai estimé : <?= $service['delivery_days'] ?> jours
                                </p>

                                <?php if (!empty($service['description'])): ?>
                                    <p class="text-[0.78rem] text-muted leading-[1.5] mb-2"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                                <?php endif; ?>

                                <div class="mt-auto">
                                    <?php if ($shop['is_open']): ?>
                                        <a href="/commander/<?= $service['id'] ?>" class="btn btn--primary" style="padding: 0.35rem 1rem; font-size: 0.8rem;">Commander</a>
                                    <?php else: ?>
                                        <span class="text-[0.75rem] text-muted italic">Boutique actuellement fermée aux commandes</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($tab === 'avis'): ?>
        <div class="pb-8">
            <?php if (empty($reviews)): ?>
                <p class="text-muted text-[0.8rem] text-center py-8">Aucun avis pour le moment.</p>
            <?php else: ?>
                <div class="flex flex-col gap-3 max-w-[620px] mx-auto">
                    <?php foreach ($reviews as $review): ?>
                        <div class="bg-white border border-border rounded-md shadow-sm p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <?php if (!empty($review['client_avatar'])): ?>
                                    <img src="/uploads/avatars/<?= htmlspecialchars($review['client_avatar']) ?>" alt="" class="w-8 h-8 rounded-full object-cover border border-border">
                                <?php else: ?>
                                    <img src="/uploads/avatars/default.png" alt="" class="w-8 h-8 rounded-full object-cover border border-border">
                                <?php endif; ?>

                                <div class="flex-1">
                                    <p class="font-semibold text-[0.82rem] text-ink"><?= htmlspecialchars($review['client_name']) ?></p>
                                    <div class="flex items-center gap-2">
                                        <?= $renderStars((float) $review['rating'], 11) ?>
                                        <span class="text-[0.7rem] text-muted"><?= \App\Core\FrenchDate::format('d MMM y', $review['created_at']) ?></span>
                                    </div>
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
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../components/report-modal.php'; ?>
<script src="/assets/js/report-modal.js"></script>

<?php if (!empty($shop['accepts_quotes'])): ?>
    <?php require __DIR__ . '/../components/quote-modal.php'; ?>
    <script src="/assets/js/quote-modal.js"></script>
<?php endif; ?>
