<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::statistics()).
 *
 * @var int $days
 * @var array{labels: array, values: array} $signupsChart
 * @var array{labels: array, values: array} $ordersChart
 * @var array{labels: array, values: array} $ordersRevenueChart
 * @var array{labels: array, values: array} $commissionsChart
 * @var array{labels: array, values: array} $subscriptionsChart
 * @var array{labels: array, values: array} $raffleChart
 * @var array{labels: array, values: array} $totalRevenueChart
 */

$periods = [14 => '14 jours', 30 => '30 jours', 90 => '90 jours'];

$activityCharts = [
    ['title' => 'Inscriptions', 'data' => $signupsChart, 'suffix' => ' inscription(s)', 'icon' => 'utilisateurs.png', 'unit' => ''],
    ['title' => 'Commandes', 'data' => $ordersChart, 'suffix' => ' commande(s)', 'icon' => 'commandes.png', 'unit' => ''],
];

$revenueCharts = [
    ['title' => 'Revenus totaux plateforme', 'data' => $totalRevenueChart, 'suffix' => ' €', 'icon' => 'paiement.png', 'unit' => '€'],
    ['title' => 'Commissions (commandes)', 'data' => $commissionsChart, 'suffix' => ' €', 'icon' => 'commissions.png', 'unit' => '€'],
    ['title' => 'Abonnements', 'data' => $subscriptionsChart, 'suffix' => ' €', 'icon' => 'abonnements.png', 'unit' => '€'],
    ['title' => 'Tirage au sort', 'data' => $raffleChart, 'suffix' => ' €', 'icon' => 'tirage.png', 'unit' => '€'],
    ['title' => 'Montant total des commandes', 'data' => $ordersRevenueChart, 'suffix' => ' €', 'icon' => 'commandes.png', 'unit' => '€'],
];

$renderStatTile = function (array $chart) {
    $total = array_sum($chart['data']['values']);
    $formatted = $chart['unit'] === '€'
        ? number_format($total, 2, ',', ' ') . ' €'
        : number_format($total, 0, ',', ' ');
    ?>
    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/<?= $chart['icon'] ?>" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= $formatted ?></div>
        <div class="font-cursive text-[0.9rem] text-success"><?= htmlspecialchars($chart['title']) ?></div>
    </div>
    <?php
};

$renderChartCard = function (array $chart, int $days) {
    ?>
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <h2 class="text-base font-semibold mb-5 text-ink"><?= htmlspecialchars($chart['title']) ?></h2>
        <div
            class="relative min-h-[220px]"
            data-admin-chart
            data-labels="<?= htmlspecialchars(json_encode($chart['data']['labels']), ENT_QUOTES) ?>"
            data-values="<?= htmlspecialchars(json_encode($chart['data']['values']), ENT_QUOTES) ?>"
            data-suffix="<?= htmlspecialchars($chart['suffix']) ?>"
            role="img"
            aria-label="<?= htmlspecialchars($chart['title']) ?> sur les <?= $days ?> derniers jours"
        ></div>
    </div>
    <?php
};
?>

<div class="flex items-center gap-2 mb-4">
    <button type="button" data-stats-tab="activity" class="stats-tab-btn inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium transition-colors bg-primary text-white border-primary">
        Activité
    </button>
    <button type="button" data-stats-tab="revenue" class="stats-tab-btn inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium transition-colors bg-white text-ink border-border hover:border-primary">
        Revenus
    </button>
</div>

<div class="flex items-center gap-2 mb-6">
    <?php foreach ($periods as $value => $label): ?>
        <a href="/admin/statistics?days=<?= $value ?>"
            class="inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium no-underline transition-colors <?= $days === $value ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">
            <?= htmlspecialchars($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<div data-stats-panel="activity">
    <p class="text-[0.8rem] text-muted mb-3">Totaux sur les <?= $days ?> derniers jours</p>
    <div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
        <?php foreach ($activityCharts as $chart): ?>
            <?php $renderStatTile($chart); ?>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 min-[1024px]:grid-cols-2 gap-6">
        <?php foreach ($activityCharts as $chart): ?>
            <?php $renderChartCard($chart, $days); ?>
        <?php endforeach; ?>
    </div>
</div>

<div data-stats-panel="revenue" class="hidden">
    <p class="text-[0.8rem] text-muted mb-3">Totaux sur les <?= $days ?> derniers jours</p>
    <div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
        <?php foreach ($revenueCharts as $chart): ?>
            <?php $renderStatTile($chart); ?>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 min-[1024px]:grid-cols-2 gap-6">
        <?php foreach ($revenueCharts as $chart): ?>
            <?php $renderChartCard($chart, $days); ?>
        <?php endforeach; ?>
    </div>
</div>

<script src="/assets/js/admin-chart.js"></script>
<script>
    (function () {
        var buttons = document.querySelectorAll('[data-stats-tab]');
        var panels = document.querySelectorAll('[data-stats-panel]');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.dataset.statsTab;

                buttons.forEach(function (b) {
                    var isActive = b === btn;
                    b.classList.toggle('bg-primary', isActive);
                    b.classList.toggle('text-white', isActive);
                    b.classList.toggle('border-primary', isActive);
                    b.classList.toggle('bg-white', !isActive);
                    b.classList.toggle('text-ink', !isActive);
                    b.classList.toggle('border-border', !isActive);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.statsPanel !== target);
                });
            });
        });
    })();
</script>
