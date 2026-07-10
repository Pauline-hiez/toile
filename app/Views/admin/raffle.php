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
    'entered' => ['label' => 'Inscrit', 'class' => 'badge--info'],
    'selected' => ['label' => 'Gagnant', 'class' => 'badge--success'],
    'not_selected' => ['label' => 'Non retenu', 'class' => 'badge--neutral'],
    'cancelled' => ['label' => 'Annulé', 'class' => 'badge--danger'],
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

<div class="admin-stats">
    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/essentiel.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['entries_boutiques'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Inscrits — Boutiques (mois en cours)</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/abonnements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['entries_homepage'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Inscrits — Accueil (semaine en cours)</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/essentiel.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['winners_boutiques'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Gagnants — Boutiques (ce mois-ci)</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/abonnements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['winners_homepage'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Gagnants — Accueil (cette semaine)</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commissions.png" alt="">
        <div class="admin-stat-card__value"><?= number_format(($stats['revenue_boutiques'] + $stats['revenue_homepage']) / 100, 2, ',', ' ') ?>€</div>
        <div class="admin-stat-card__label">Recettes attendues (inscriptions en cours)</div>
    </div>
</div>

<div class="admin-panel" style="margin-bottom: 2rem;">
    <h2>Prochains tirages</h2>
    <div class="admin-raffle-draws">
        <div class="admin-raffle-draw">
            <img class="admin-raffle-draw__icon" src="/assets/images/icones/essentiel.png" alt="">
            <div class="admin-raffle-draw__info">
                <div class="admin-raffle-draw__label">Vitrine boutiques — 10 gagnants</div>
                <div class="admin-raffle-draw__date"><?= \App\Core\FrenchDate::format('d MMMM y', $nextBoutiquesDraw) ?> à 00h00</div>
            </div>
            <div class="admin-raffle-draw__countdown" data-countdown="<?= htmlspecialchars($nextBoutiquesDraw) ?>"></div>
        </div>

        <div class="admin-raffle-draw">
            <img class="admin-raffle-draw__icon" src="/assets/images/icones/abonnements.png" alt="">
            <div class="admin-raffle-draw__info">
                <div class="admin-raffle-draw__label">Page d'accueil — 5 gagnants</div>
                <div class="admin-raffle-draw__date"><?= \App\Core\FrenchDate::format('d MMMM y', $nextHomepageDraw) ?> à 00h00</div>
            </div>
            <div class="admin-raffle-draw__countdown" data-countdown="<?= htmlspecialchars($nextHomepageDraw) ?>"></div>
        </div>
    </div>
</div>

<div class="admin-panel" style="margin-bottom: 2rem;">
    <h2>Gagnants — Vitrine boutiques (ce mois-ci)</h2>
    <?php if (empty($boutiquesWinners)): ?>
        <p class="admin-panel__empty">Aucun gagnant pour le moment ce mois-ci.</p>
    <?php else: ?>
        <div class="admin-winners-grid">
            <?php foreach ($boutiquesWinners as $i => $winner): ?>
                <div class="admin-winner">
                    <span class="admin-winner__rank"><?= $i + 1 ?></span>
                    <?php if (!empty($winner['avatar'])): ?>
                        <img class="admin-winner__avatar" src="/uploads/avatars/<?= htmlspecialchars($winner['avatar']) ?>" alt="">
                    <?php else: ?>
                        <img class="admin-winner__avatar" src="/assets/images/icones/new-user.png" alt="">
                    <?php endif; ?>
                    <div>
                        <span class="admin-winner__name"><?= htmlspecialchars($winner['shop_name']) ?></span>
                        <span class="admin-winner__slug">/<?= htmlspecialchars($winner['shop_slug']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="admin-table-wrapper">
    <form action="/admin/raffle" method="GET" class="admin-table-filters">
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
        <p class="admin-panel__empty" style="padding: 1.5rem;">Aucune inscription ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="admin-table-scroll">
            <table class="admin-table">
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
                        <?php $statusInfo = $statusLabels[$entry['status']] ?? ['label' => $entry['status'], 'class' => 'badge--neutral']; ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['username']) ?></td>
                            <td><a href="/boutiques/<?= htmlspecialchars($entry['shop_slug']) ?>"><?= htmlspecialchars($entry['shop_name']) ?></a></td>
                            <td><?= htmlspecialchars($typeLabels[$entry['type']] ?? $entry['type']) ?></td>
                            <td><?= htmlspecialchars($entry['period']) ?></td>
                            <td><span class="badge <?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
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
