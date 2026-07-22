<?php

/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir RaffleController::index()).
 *
 * @var array|null $shop
 * @var array|null $boutiqueEntry
 * @var array|null $homepageEntry
 * @var string $currentMonth
 * @var string $currentMonday
 * @var int $rafflePrice
 * @var int $homepagePrice
 * @var int $boutiqueEntriesCount
 * @var int $homepageEntriesCount
 * @var string $nextBoutiquesDraw
 * @var string $nextHomepageDraw
 * @var array $recentTickets
 * @var array $recentWinners
 */

$statusLabels = [
    'entered' => ['label' => 'En attente du tirage', 'class' => \App\Core\Badge::classes('info')],
    'selected' => ['label' => 'Sélectionné·e !', 'class' => \App\Core\Badge::classes('success')],
    'not_selected' => ['label' => 'Non sélectionné·e', 'class' => \App\Core\Badge::classes('neutral')],
    'cancelled' => ['label' => 'Annulé', 'class' => \App\Core\Badge::classes('danger')],
];

$typeLabels = [
    'boutiques' => 'Vitrine boutiques',
    'homepage' => 'Page d\'accueil',
];

$cards = [
    [
        'type' => 'boutiques',
        'tag' => 'Chaque mois',
        'placement' => "Mise en avant\nPage de recherches",
        'icon' => 'boutique.png',
        'price' => $rafflePrice,
        'entriesCount' => $boutiqueEntriesCount,
        'nextDraw' => $nextBoutiquesDraw,
        'entry' => $boutiqueEntry,
    ],
    [
        'type' => 'homepage',
        'tag' => 'Chaque semaine',
        'placement' => "Mise en avant\nPage d'accueil",
        'icon' => 'accueil.png',
        'price' => $homepagePrice,
        'entriesCount' => $homepageEntriesCount,
        'nextDraw' => $nextHomepageDraw,
        'entry' => $homepageEntry,
    ],
];

$steps = [
    ['icon' => 'acheter-ticket.png', 'label' => 'Achetez un ticket', 'desc' => "3€ ou 5€\nselon le tirage"],
    ['icon' => 'tirage.png', 'label' => 'Tirage au sort', 'desc' => "Automatique parmi\ntous les participants"],
    ['icon' => 'artistes-selectionnes.png', 'label' => 'Artistes sélectionnés', 'desc' => "Mise en avant sur la page\nconcernée selon le tirage"],
    ['icon' => 'commissions.png', 'label' => 'Prélèvement immédiat', 'desc' => "Gagnant·e ou non,\ndès l'achat du ticket"],
];
?>

<section class="max-w-[1100px] mx-auto px-5 py-10 min-[641px]:px-10 relative">
    <img src="/assets/images/decor/tirage-page.png" alt="" class="hidden min-[1200px]:block absolute -right-16 top-56 w-[260px] h-auto opacity-90 z-10 -scale-x-100">

    <div class="text-center mb-10">
        <h1 class="font-title text-title text-shine text-[2.6rem] min-[641px]:text-[3.2rem] leading-none mb-2">Tirage au sort</h1>
        <p class="font-cursive text-primary text-[0.95rem] font-semibold">2 tirages au sort sont organisés chaque mois.</p>
    </div>

    <?php if ($shop === null): ?>
        <div class="page-alert page-alert--info max-w-[560px] mx-auto text-center">
            Tu dois avoir une boutique pour participer aux tirages au sort.
            <a href="/my-shop">Gérer ma boutique →</a>
        </div>
    <?php else: ?>

        <div class="grid grid-cols-1 min-[768px]:grid-cols-2 gap-6 mb-14">
            <?php foreach ($cards as $card): ?>
                <div class="bg-white border border-border rounded-2xl p-5 shadow-sm relative">
                    <div class="flex items-start justify-between gap-3 mb-4 flex-wrap">
                        <span class="inline-flex items-center rounded-full bg-title-bg text-title text-[0.75rem] font-semibold px-3 py-1"><?= htmlspecialchars($card['tag']) ?></span>
                        <span class="inline-flex items-center rounded-full bg-primary-light text-primary text-[0.7rem] font-semibold px-3 py-1 text-center leading-tight whitespace-pre-line"><?= htmlspecialchars($card['placement']) ?></span>
                    </div>

                    <div class="flex items-center gap-4 mb-3">
                        <img src="/assets/images/icones/<?= $card['icon'] ?>" alt="" class="w-16 h-16 object-contain shrink-0">
                        <div>
                            <div class="font-cursive text-[1.9rem] font-bold text-primary leading-none"><?= number_format($card['price'] / 100, 0) ?>€</div>
                            <div class="text-[0.8rem] text-muted"><?= $card['entriesCount'] ?> inscrit<?= $card['entriesCount'] > 1 ? 's' : '' ?> pour ce tirage</div>
                        </div>
                    </div>

                    <div class="text-[0.8rem] text-muted mb-4">
                        Prochain tirage le <?= \App\Core\FrenchDate::format('d MMMM y', $card['nextDraw']) ?>
                        · <span class="font-semibold text-primary" data-countdown="<?= htmlspecialchars($card['nextDraw']) ?>"></span>
                    </div>

                    <?php if ($card['entry'] !== null): ?>
                        <?php $info = $statusLabels[$card['entry']['status']] ?? ['label' => $card['entry']['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                        <span class="<?= $info['class'] ?>"><?= htmlspecialchars($info['label']) ?></span>
                        <?php if ($card['entry']['status'] === 'entered'): ?>
                            <form method="POST" action="/raffle/cancel" class="mt-3">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <input type="hidden" name="type" value="<?= htmlspecialchars($card['type']) ?>">
                                <button type="submit" onclick="return confirm('Annuler l\'inscription ? Le ticket sera remboursé.');" class="text-danger text-[0.8rem] font-semibold hover:underline">
                                    Annuler mon inscription (remboursée)
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mb-10">
            <h2 class="font-title text-primary text-[2rem] mb-8">Comment ça marche&nbsp;?</h2>

            <div class="flex flex-col min-[900px]:flex-row items-center min-[900px]:items-start justify-center gap-6 min-[900px]:gap-3">
                <?php foreach ($steps as $i => $step): ?>
                    <?php if ($i > 0): ?>
                        <span class="hidden min-[900px]:inline-flex items-center text-border mt-9" aria-hidden="true">
                            <svg width="28" height="16" viewBox="0 0 28 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 8h24M18 2l6 6-6 6"></path>
                            </svg>
                        </span>
                    <?php endif; ?>
                    <div class="text-center w-[170px]">
                        <span class="inline-flex items-center justify-center w-[90px] h-[90px] rounded-full mb-3 bg-[radial-gradient(circle,var(--color-title-bg)_0%,transparent_72%)]">
                            <img src="/assets/images/icones/<?= $step['icon'] ?>" alt="" class="w-[76px] h-[76px] object-contain">
                        </span>
                        <h3 class="font-cursive text-[0.95rem] font-semibold text-ink mb-1"><?= htmlspecialchars($step['label']) ?></h3>
                        <p class="text-[0.75rem] text-muted leading-[1.4] whitespace-pre-line"><?= htmlspecialchars($step['desc']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-center mb-8">
            <span class="inline-flex items-center rounded-full bg-primary text-white font-cursive text-[0.95rem] font-semibold px-6 py-2 shadow-sm">Choisissez votre ticket</span>
        </div>

        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-8 mb-14">
            <?php foreach ($cards as $card): ?>
                <div class="text-center">
                    <?php
                    // Le prix est superposé au cartouche vide de ticket.png
                    // (une seule image générique pour les 2 tirages) plutôt
                    // que gravé dans l'image, pour rester à jour si l'admin
                    // change le prix depuis les réglages.
                    $priceOverlay = '<span class="absolute inset-0 flex items-center justify-center font-cursive font-bold text-[2.1rem] text-[#cdc5b6]" style="left:28.5%;top:40.6%;width:22.2%;height:35.2%;right:auto;bottom:auto;">'
                        . number_format($card['price'] / 100, 0) . '€</span>';
                    ?>
                    <?php if ($card['entry'] === null): ?>
                        <form method="POST" action="/raffle/enter">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <input type="hidden" name="type" value="<?= htmlspecialchars($card['type']) ?>">
                            <button type="submit" class="relative inline-block bg-transparent border-0 p-0 cursor-pointer transition-transform hover:-translate-y-1" aria-label="Participer au tirage <?= htmlspecialchars($typeLabels[$card['type']]) ?>">
                                <img src="/assets/images/decor/ticket.png" alt="" class="w-full max-w-[280px] h-auto mx-auto drop-shadow-sm">
                                <?= $priceOverlay ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="relative inline-block opacity-50 grayscale-[30%]">
                            <img src="/assets/images/decor/ticket.png" alt="" class="w-full max-w-[280px] h-auto mx-auto">
                            <?= $priceOverlay ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 min-[768px]:grid-cols-2 gap-6">
            <div class="bg-white border border-border rounded-2xl p-5 shadow-sm min-h-[160px]">
                <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-4">Mes derniers tickets</h2>
                <?php if (empty($recentTickets)): ?>
                    <p class="text-muted text-[0.85rem] text-center py-6">Tu n'as encore participé à aucun tirage.</p>
                <?php else: ?>
                    <div class="flex flex-col gap-2">
                        <?php foreach ($recentTickets as $ticket): ?>
                            <?php $info = $statusLabels[$ticket['status']] ?? ['label' => $ticket['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                            <div class="flex items-center justify-between gap-3 py-2 px-3 rounded-sm bg-bg">
                                <div>
                                    <span class="text-[0.85rem] font-semibold block"><?= htmlspecialchars($typeLabels[$ticket['type']] ?? $ticket['type']) ?></span>
                                    <span class="text-[0.75rem] text-muted"><?= htmlspecialchars($ticket['period']) ?></span>
                                </div>
                                <span class="<?= $info['class'] ?>"><?= htmlspecialchars($info['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white border border-border rounded-2xl p-5 shadow-sm min-h-[160px]">
                <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-4">Les derniers gagnants</h2>
                <?php if (empty($recentWinners)): ?>
                    <p class="text-muted text-[0.85rem] text-center py-6">Aucun gagnant pour le moment.</p>
                <?php else: ?>
                    <div class="flex flex-col gap-2">
                        <?php foreach ($recentWinners as $winner): ?>
                            <a href="/boutiques/<?= htmlspecialchars($winner['shop_slug']) ?>" class="flex items-center gap-3 py-2 px-3 rounded-sm bg-bg no-underline text-inherit transition-colors hover:bg-primary-light">
                                <img class="w-9 h-9 rounded-full object-cover shrink-0" src="/uploads/avatars/<?= htmlspecialchars($winner['avatar'] ?: 'default.png') ?>" alt="">
                                <div>
                                    <span class="text-[0.85rem] font-semibold block"><?= htmlspecialchars($winner['shop_name']) ?></span>
                                    <span class="text-[0.75rem] text-muted"><?= htmlspecialchars($typeLabels[$winner['type']] ?? $winner['type']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</section>

<script src="/assets/js/admin-countdown.js"></script>