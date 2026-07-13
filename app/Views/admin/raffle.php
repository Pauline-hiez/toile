<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::raffle()).
 *
 * @var array $entries
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, type: string, status: string} $filters
 * @var array $stats
 * @var array $boutiquesWinners
 * @var string $nextBoutiquesDraw
 * @var string $nextHomepageDraw
 * @var array<int, int|string> $pageNumbers
 */

$typeLabels = [
    'boutiques' => 'Vitrine boutiques',
    'homepage' => 'Page d\'accueil',
];

$statusLabels = [
    'entered' => ['label' => 'Inscrit', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-info-bg text-info'],
    'selected' => ['label' => 'Gagnant', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-success-bg text-success'],
    'not_selected' => ['label' => 'Non retenu', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-border text-muted'],
    'cancelled' => ['label' => 'Annulé', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-danger-bg text-danger'],
];

$totalPages = max(1, (int) ceil($total / $perPage));
$rangeStart = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$rangeEnd = min($total, $page * $perPage);

$queryWithout = function (array $overrides = []) use ($filters) {
    $params = array_merge(
        array_diff_key($filters, array_flip(['page', 'per_page'])),
        $overrides
    );
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '/admin/raffle' . ($params !== [] ? '?' . http_build_query($params) : '');
};
?>

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/essentiel.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['entries_boutiques'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Inscrits — Boutiques (mois en cours)</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/abonnements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['entries_homepage'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Inscrits — Accueil (semaine en cours)</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/essentiel.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['winners_boutiques'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Gagnants — Boutiques (ce mois-ci)</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/abonnements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['winners_homepage'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Gagnants — Accueil (cette semaine)</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/commissions.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format(($stats['revenue_boutiques'] + $stats['revenue_homepage']) / 100, 2, ',', ' ') ?>€</div>
        <div class="text-[0.8rem] text-muted font-medium">Recettes attendues (inscriptions en cours)</div>
    </div>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col mb-8">
    <h2 class="text-base font-semibold mb-5 text-ink">Prochains tirages</h2>
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-5 py-4 px-5 border border-border rounded-md bg-bg flex-wrap">
            <img class="w-14 h-14 object-contain shrink-0" src="/assets/images/icones/essentiel.png" alt="">
            <div class="flex-1 min-w-[180px]">
                <div class="text-[0.8rem] text-muted font-semibold uppercase tracking-[0.03em]">Vitrine boutiques — 10 gagnants</div>
                <div class="text-[1.05rem] font-semibold text-ink"><?= \App\Core\FrenchDate::format('d MMMM y', $nextBoutiquesDraw) ?> à 00h00</div>
            </div>
            <div class="font-cursive text-[1.4rem] text-primary whitespace-nowrap" data-countdown="<?= htmlspecialchars($nextBoutiquesDraw) ?>"></div>
        </div>

        <div class="flex items-center gap-5 py-4 px-5 border border-border rounded-md bg-bg flex-wrap">
            <img class="w-14 h-14 object-contain shrink-0" src="/assets/images/icones/abonnements.png" alt="">
            <div class="flex-1 min-w-[180px]">
                <div class="text-[0.8rem] text-muted font-semibold uppercase tracking-[0.03em]">Page d'accueil — 5 gagnants</div>
                <div class="text-[1.05rem] font-semibold text-ink"><?= \App\Core\FrenchDate::format('d MMMM y', $nextHomepageDraw) ?> à 00h00</div>
            </div>
            <div class="font-cursive text-[1.4rem] text-primary whitespace-nowrap" data-countdown="<?= htmlspecialchars($nextHomepageDraw) ?>"></div>
        </div>
    </div>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col mb-8">
    <h2 class="text-base font-semibold mb-5 text-ink">Gagnants — Vitrine boutiques (ce mois-ci)</h2>
    <?php if (empty($boutiquesWinners)): ?>
        <p class="text-muted text-[0.85rem] my-auto text-center">Aucun gagnant pour le moment ce mois-ci.</p>
    <?php else: ?>
        <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-3">
            <?php foreach ($boutiquesWinners as $i => $winner): ?>
                <div class="flex items-center gap-[0.65rem] p-2 rounded-sm bg-bg">
                    <span class="font-bold text-primary w-[1.2rem] text-center shrink-0"><?= $i + 1 ?></span>
                    <?php if (!empty($winner['avatar'])): ?>
                        <img class="w-10 h-10 rounded-full object-cover shrink-0" src="/uploads/avatars/<?= htmlspecialchars($winner['avatar']) ?>" alt="">
                    <?php else: ?>
                        <img class="w-10 h-10 rounded-full object-cover shrink-0" src="/assets/images/icones/new-user.png" alt="">
                    <?php endif; ?>
                    <div>
                        <span class="text-[0.85rem] font-semibold block"><?= htmlspecialchars($winner['shop_name']) ?></span>
                        <span class="text-[0.75rem] text-muted">/<?= htmlspecialchars($winner['shop_slug']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <form action="/admin/raffle" method="GET" class="p-5 border-b border-border flex items-center gap-3 flex-wrap [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-4 [&_select]:py-[0.4rem] [&_select]:text-[0.85rem] [&_select]:outline-none [&_select]:bg-bg [&_select]:font-main">
        <?php
        $searchStandalone = false;
        $searchValue = $filters['q'];
        ?>
        <?php require __DIR__ . '/../components/search-bar.php'; ?>

        <select name="type" onchange="this.form.submit()">
            <option value="">Tirage : Tous</option>
            <?php foreach ($typeLabels as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" onchange="this.form.submit()">
            <option value="">Statut : Tous</option>
            <?php foreach ($statusLabels as $value => $info): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($info['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (empty($entries)): ?>
        <p class="text-muted text-[0.85rem] text-center p-6">Aucune inscription ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2] [&_input]:w-4 [&_input]:h-4 [&_input]:accent-primary [&_input]:cursor-pointer">
                <thead>
                    <tr>
                        <th>Artiste</th>
                        <th>Boutique</th>
                        <th>Tirage</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th>Date d'achat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <?php $statusInfo = $statusLabels[$entry['status']] ?? ['label' => $entry['status'], 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-border text-muted']; ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['username']) ?></td>
                            <td><a href="/boutiques/<?= htmlspecialchars($entry['shop_slug']) ?>"><?= htmlspecialchars($entry['shop_name']) ?></a></td>
                            <td><?= htmlspecialchars($typeLabels[$entry['type']] ?? $entry['type']) ?></td>
                            <td><?= htmlspecialchars($entry['period']) ?></td>
                            <td><span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= \App\Core\FrenchDate::format("d MMM y 'à' HH'h'mm", $entry['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php $entityLabel = 'inscriptions'; ?>
    <?php require __DIR__ . '/../components/pagination.php'; ?>
</div>

<script src="/assets/js/admin-countdown.js"></script>
