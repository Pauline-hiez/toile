<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::statistics()).
 *
 * @var int $days
 * @var array{labels: array, values: array} $signupsChart
 * @var array{labels: array, values: array} $ordersChart
 * @var array{labels: array, values: array} $revenueChart
 * @var array{labels: array, values: array} $commissionsChart
 */

$periods = [14 => '14 jours', 30 => '30 jours', 90 => '90 jours'];

$charts = [
    ['title' => 'Inscriptions', 'data' => $signupsChart, 'suffix' => ' inscription(s)'],
    ['title' => 'Commandes', 'data' => $ordersChart, 'suffix' => ' commande(s)'],
    ['title' => 'Revenus', 'data' => $revenueChart, 'suffix' => ' €'],
    ['title' => 'Commissions', 'data' => $commissionsChart, 'suffix' => ' €'],
];
?>

<div class="flex items-center gap-2 mb-6">
    <?php foreach ($periods as $value => $label): ?>
        <a href="/admin/statistics?days=<?= $value ?>"
            class="inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium no-underline transition-colors <?= $days === $value ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">
            <?= htmlspecialchars($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 min-[1024px]:grid-cols-2 gap-6">
    <?php foreach ($charts as $chart): ?>
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
    <?php endforeach; ?>
</div>

<script src="/assets/js/admin-chart.js"></script>
