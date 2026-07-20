<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir FavoriteController::index()).
 *
 * @var array $shops
 */
$pageTitle = 'Mes favoris — Toile';
?>

<div class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="text-center font-cursive text-[1.9rem] font-semibold text-ink mb-8">Mes favoris</h1>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <?php if (empty($shops)): ?>
            <div class="text-center p-10">
                <p class="text-muted text-[0.85rem] mb-4">Tu n'as pas encore de boutique en favori.</p>
                <a href="/boutiques" class="btn btn--primary">Découvrir les artistes</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 min-[641px]:grid-cols-3 min-[900px]:grid-cols-4 min-[1200px]:grid-cols-5 gap-5 p-5">
                <?php foreach ($shops as $shop): ?>
                    <?php
                    $styles = $shop['styles'] ? json_decode($shop['styles'], true) : [];
                    $rating = $shop['avg_rating'] !== null ? number_format((float) $shop['avg_rating'], 1) : null;
                    ?>
                    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm flex flex-col transition hover:shadow-[0_4px_16px_rgba(0,0,0,0.1)] hover:-translate-y-0.5">
                        <div class="relative h-[100px] bg-primary-light">
                            <?php if (!empty($shop['banner'])): ?>
                                <img class="w-full h-full object-cover block" src="/uploads/banners/<?= htmlspecialchars($shop['banner']) ?>" alt="">
                            <?php endif; ?>

                            <?php if (!$shop['is_open']): ?>
                                <span class="absolute top-2 left-2 <?= \App\Core\Badge::classes('warning') ?>">Fermée</span>
                            <?php endif; ?>

                            <form method="POST" action="/favoris/toggle/<?= $shop['id'] ?>" class="absolute top-2 right-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <input type="hidden" name="redirect" value="/favoris">
                                <button type="submit" title="Retirer des favoris" class="w-7 h-7 rounded-full bg-white/90 flex items-center justify-center text-danger border-0 cursor-pointer shadow-sm hover:bg-white transition-colors">
                                    <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-[15px] h-[15px]">
                                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
                                    </svg>
                                </button>
                            </form>

                            <img class="absolute left-[0.9rem] bottom-[-22px] w-[52px] h-[52px] rounded-full border-[3px] border-white object-cover bg-bg"
                                src="<?= !empty($shop['avatar']) ? '/uploads/avatars/' . htmlspecialchars($shop['avatar']) : '/uploads/avatars/default.png' ?>" alt="">
                        </div>

                        <div class="pt-7 px-[0.9rem] pb-[0.9rem] flex-1 flex flex-col">
                            <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="font-semibold text-[0.95rem] text-ink no-underline block"><?= htmlspecialchars($shop['name']) ?></a>
                            <div class="text-[0.75rem] text-muted mb-[0.65rem]">Par <?= htmlspecialchars($shop['username']) ?></div>

                            <?php if (!empty($styles)): ?>
                                <div class="flex flex-wrap gap-[0.35rem] mb-3">
                                    <?php foreach (array_slice($styles, 0, 3) as $style): ?>
                                        <span class="<?= \App\Core\Badge::classes('neutral') ?>"><?= htmlspecialchars(ucfirst($style)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center gap-[0.9rem] text-[0.8rem] text-muted mb-3">
                                <?php if ($rating !== null): ?>
                                    <span title="Note moyenne" class="inline-flex items-center gap-[0.3rem]">
                                        <svg viewBox="0 0 24 24" fill="currentColor" stroke="none" class="w-[14px] h-[14px] text-title">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>
                                        </svg>
                                        <?= $rating ?> (<?= (int) $shop['review_count'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span>Pas encore d'avis</span>
                                <?php endif; ?>

                                <span title="Favoris" class="inline-flex items-center gap-[0.3rem]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-[14px] h-[14px]">
                                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
                                    </svg>
                                    <?= (int) $shop['favorite_count'] ?>
                                </span>
                            </div>

                            <div class="mt-auto pt-[0.65rem] border-t border-border flex items-center justify-between gap-2">
                                <?php if ($shop['min_price'] !== null): ?>
                                    <span class="text-[0.85rem] font-semibold text-ink">Dès <?= number_format($shop['min_price'] / 100, 2) ?> €</span>
                                <?php else: ?>
                                    <span class="text-[0.8rem] text-muted">Aucune prestation</span>
                                <?php endif; ?>

                                <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="btn btn--outline" style="padding: 0.3rem 0.9rem; font-size: 0.8rem;">Voir</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
