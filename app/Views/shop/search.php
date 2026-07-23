<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ShopController::search()).
 *
 * @var array $shops
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, style: string, min_price: string, max_price: string, sort: string} $filters
 * @var array<int, string> $availableStyles
 * @var array<int, int|string> $pageNumbers
 */
$pageTitle = 'Découvrir les artistes — Toile';

$totalPages = max(1, (int) ceil($total / $perPage));
$rangeStart = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$rangeEnd = min($total, $page * $perPage);

$queryWithout = function (array $overrides = []) use ($filters) {
    $params = array_merge(
        array_diff_key($filters, array_flip(['page', 'per_page'])),
        $overrides
    );
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '/boutiques' . ($params !== [] ? '?' . http_build_query($params) : '');
};
?>

<div class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-8">Découvrir les artistes</h1>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <form action="/boutiques" method="GET" class="p-5 border-b border-border flex items-end gap-3 flex-wrap [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-4 [&_select]:py-[0.4rem] [&_select]:text-[0.85rem] [&_select]:outline-none [&_select]:bg-bg [&_select]:font-main [&_select]:focus:border-primary">
            <?php
            $searchStandalone = false;
            $searchValue = $filters['q'];
            $searchPlaceholder = 'Nom de la boutique...';
            $searchAutocompleteUrl = '/boutiques/autocomplete';
            ?>
            <?php require __DIR__ . '/../components/search-bar.php'; ?>

            <select name="style">
                <option value="">Tous les styles</option>
                <?php foreach ($availableStyles as $style): ?>
                    <option value="<?= htmlspecialchars($style) ?>" <?= $filters['style'] === $style ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($style)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div>
                <label for="min_price" class="sr-only">Prix min (€)</label>
                <input type="number" id="min_price" name="min_price" min="0" placeholder="Prix min (€)" value="<?= htmlspecialchars($filters['min_price']) ?>"
                    class="w-[120px] border border-border rounded-full px-4 py-[0.35rem] text-[0.85rem] outline-none bg-bg font-main focus:border-primary">
            </div>

            <div>
                <label for="max_price" class="sr-only">Prix max (€)</label>
                <input type="number" id="max_price" name="max_price" min="0" placeholder="Prix max (€)" value="<?= htmlspecialchars($filters['max_price']) ?>"
                    class="w-[120px] border border-border rounded-full px-4 py-[0.35rem] text-[0.85rem] outline-none bg-bg font-main focus:border-primary">
            </div>

            <select name="sort">
                <option value="rating" <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Meilleure note</option>
                <option value="price" <?= $filters['sort'] === 'price' ? 'selected' : '' ?>>Prix croissant</option>
            </select>

            <button type="submit" class="btn btn--primary">Rechercher</button>
        </form>

        <?php if (empty($shops)): ?>
            <p class="text-muted text-[0.85rem] text-center p-6">Aucune boutique ne correspond à ta recherche.</p>
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

        <?php $entityLabel = 'boutiques'; ?>
        <?php require __DIR__ . '/../components/pagination.php'; ?>
    </div>
</div>
