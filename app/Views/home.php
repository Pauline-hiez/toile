<?php

/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::index()).
 *
 * @var array $featuredShops Boutiques mises en avant ("Nos artistes de la semaine").
 * @var array<int, array{name: string, image: string|null}> $styleTiles Styles artistiques avec une image de portfolio représentative.
 * @var bool $hasMoreStyles Vrai s'il existe des styles au-delà des 5 affichés (lien "Voir plus" vers /styles).
 */
$pageTitle = 'Accueil — Toile';
?>

<section class="max-w-[1400px] mx-auto pt-6 pb-6 min-[641px]:pt-10 grid grid-cols-1 min-[1100px]:grid-cols-2 items-center gap-4">
    <div class="order-first min-[1100px]:order-none px-5 min-[641px]:px-10">
        <h1 class="font-title text-title text-[2.8rem] min-[641px]:text-[3.8rem] leading-[1.15] mb-3">Donnez vie à<br>vos idées.</h1>
        <p class="font-title italic text-[1.6rem] text-primary mb-4">L'art n'attend que vous.</p>
        <p class="text-muted text-base leading-[1.6] max-w-[480px] mb-7">
            Toile est un marketplace de comissions artistiques
            qui met en relation des artistes talentieux
            et des passionnés en quête de créations uniques.
        </p>

        <form method="GET" action="/boutiques" class="flex items-center max-w-[480px] bg-white border border-border rounded-full p-[0.3rem] pl-5 shadow-sm">
            <input type="text" name="q" placeholder="Que souhaitez-vous créer ? Ex: Illustration, portrait, paysage..." class="flex-1 border-0 outline-none bg-transparent font-main text-[0.9rem] text-ink placeholder:text-muted">
            <button type="submit" aria-label="Rechercher" class="flex items-center justify-center w-[42px] h-[42px] rounded-full bg-primary text-white shrink-0 hover:bg-primary-hover transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>
    </div>

    <div class="order-first min-[1100px]:order-none">
        <img src="/assets/images/decor/hero.png" alt="Artiste illustrant une commande" class="w-full h-auto block">
    </div>
</section>

<?php if (!empty($featuredShops)): ?>
    <section class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
        <h2 class="text-center font-title text-primary text-[2.3rem] mb-8">Nos artistes de la semaine</h2>

        <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-6">
            <?php foreach ($featuredShops as $shop): ?>
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
    </section>
<?php endif; ?>

<section class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h2 class="text-center font-title text-primary text-[2.3rem] mb-8">Explorez l'univers de la création</h2>

    <div class="grid grid-cols-2 min-[641px]:grid-cols-3 min-[1100px]:grid-cols-5 gap-6">
        <?php foreach ($styleTiles as $tile): ?>
            <?php $tileImage = $tile['image'] ? htmlspecialchars($tile['image']) : '/assets/images/default/style.png'; ?>
            <a href="/boutiques?style=<?= urlencode($tile['name']) ?>" class="group relative flex items-end h-[200px] rounded-md overflow-hidden bg-primary-light shadow-sm no-underline transition hover:shadow-[0_4px_16px_rgba(0,0,0,0.12)] hover:-translate-y-0.5">
                <img src="<?= $tileImage ?>" alt="" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[250ms] ease group-hover:scale-105">
                <span class="absolute inset-0 bg-gradient-to-b from-transparent from-40% to-black/55"></span>
                <span class="relative z-10 w-full p-4 font-cursive text-[1.1rem] font-semibold text-white"><?= htmlspecialchars(ucfirst($tile['name'])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($hasMoreStyles): ?>
        <div class="text-center mt-8">
            <a href="/styles" class="btn btn--outline">Voir plus de styles →</a>
        </div>
    <?php endif; ?>
</section>

<section class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h2 class="text-center font-title text-primary text-[2.3rem] mb-8">Comment ça marche ?</h2>

    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[1100px]:grid-cols-4 gap-8">
        <div class="text-center">
            <span class="inline-flex items-center justify-center w-[140px] h-[140px] rounded-full mb-4 bg-[radial-gradient(circle,var(--color-title-bg)_0%,transparent_72%)]">
                <img src="/assets/images/icones/rechercher.png" alt="" class="w-[130px] h-[130px] object-contain">
            </span>
            <h3 class="font-cursive text-[1.1rem] font-semibold text-ink mb-2">1. Recherchez</h3>
            <p class="text-[0.85rem] text-muted leading-[1.5]">Découvrez les artistes et trouvez celui qui correspond à votre projet.</p>
        </div>

        <div class="text-center">
            <span class="inline-flex items-center justify-center w-[140px] h-[140px] rounded-full mb-4 bg-[radial-gradient(circle,var(--color-title-bg)_0%,transparent_72%)]">
                <img src="/assets/images/icones/echangez.png" alt="" class="w-[130px] h-[130px] object-contain">
            </span>
            <h3 class="font-cursive text-[1.1rem] font-semibold text-ink mb-2">2. Echangez</h3>
            <p class="text-[0.85rem] text-muted leading-[1.5]">Discutez de votre idée avec les artistes et trouvez la prestation idéale.</p>
        </div>

        <div class="text-center">
            <span class="inline-flex items-center justify-center w-[140px] h-[140px] rounded-full mb-4 bg-[radial-gradient(circle,var(--color-title-bg)_0%,transparent_72%)]">
                <img src="/assets/images/icones/creez.png" alt="" class="w-[130px] h-[130px] object-contain">
            </span>
            <h3 class="font-cursive text-[1.1rem] font-semibold text-ink mb-2">3. Créez</h3>
            <p class="text-[0.85rem] text-muted leading-[1.5]">L'artiste donne vie à votre projet avec passion et créativité.</p>
        </div>

        <div class="text-center">
            <span class="inline-flex items-center justify-center w-[140px] h-[140px] rounded-full mb-4 bg-[radial-gradient(circle,var(--color-title-bg)_0%,transparent_72%)]">
                <img src="/assets/images/icones/recevez.png" alt="" class="w-[130px] h-[130px] object-contain">
            </span>
            <h3 class="font-cursive text-[1.1rem] font-semibold text-ink mb-2">4. Recevez</h3>
            <p class="text-[0.85rem] text-muted leading-[1.5]">Recevez votre commande et partagez votre expérience avec la communauté.</p>
        </div>
    </div>
</section>
