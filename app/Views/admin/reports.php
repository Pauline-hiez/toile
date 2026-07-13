<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::reports()).
 *
 * @var array $reports
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, type: string, status: string} $filters
 * @var array $stats
 * @var array<int, int|string> $pageNumbers
 */

$typeLabels = [
    'shop' => 'Boutique',
    'review' => 'Avis',
];

$reasonLabels = \App\Models\Report::reasonLabels();

$statusLabels = [
    'pending' => ['label' => 'En attente', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-title-bg text-title'],
    'resolved' => ['label' => 'Résolu', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-success-bg text-success'],
    'dismissed' => ['label' => 'Rejeté', 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-border text-muted'],
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
    return '/admin/reports' . ($params !== [] ? '?' . http_build_query($params) : '');
};
?>

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/avertissements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Total signalements</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/avertissements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['pending'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">En attente</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/avertissements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['resolved'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Résolus</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/avertissements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['dismissed'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Rejetés</div>
    </div>
</div>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <form action="/admin/reports" method="GET" class="p-5 border-b border-border flex items-center gap-3 flex-wrap [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-4 [&_select]:py-[0.4rem] [&_select]:text-[0.85rem] [&_select]:outline-none [&_select]:bg-bg [&_select]:font-main">
        <?php
        $searchStandalone = false;
        $searchValue = $filters['q'];
        ?>
        <?php require __DIR__ . '/../components/search-bar.php'; ?>

        <select name="type" onchange="this.form.submit()">
            <option value="">Contenu : Tous</option>
            <?php foreach ($typeLabels as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" onchange="this.form.submit()">
            <option value="">Statuts : Tous</option>
            <?php foreach ($statusLabels as $value => $info): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($info['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (empty($reports)): ?>
        <p class="text-muted text-[0.85rem] text-center p-6">Aucun signalement ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2] [&_input]:w-4 [&_input]:h-4 [&_input]:accent-primary [&_input]:cursor-pointer" data-bulk-table>
                <thead>
                    <tr>
                        <th><input type="checkbox" data-select-all aria-label="Tout sélectionner"></th>
                        <th>ID</th>
                        <th>Signalé</th>
                        <th>Signalé par</th>
                        <th>Contenu</th>
                        <th>Raison</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <?php $statusInfo = $statusLabels[$report['status']] ?? ['label' => $report['status'], 'class' => 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-border text-muted']; ?>
                        <tr>
                            <td><input type="checkbox" class="js-row-select" value="<?= (int) $report['id'] ?>" aria-label="Sélectionner le signalement #<?= (int) $report['id'] ?>"></td>
                            <td><?= sprintf('SIG-%04d', $report['id']) ?></td>
                            <td>
                                <?php if ($report['shop_slug']): ?>
                                    <a href="/boutiques/<?= htmlspecialchars($report['shop_slug']) ?>"><?= htmlspecialchars($report['shop_name']) ?></a>
                                <?php else: ?>
                                    <em>Contenu supprimé</em>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($report['reporter_username']) ?></td>
                            <td><?= htmlspecialchars($typeLabels[$report['reportable_type']] ?? $report['reportable_type']) ?></td>
                            <td><?= htmlspecialchars($reasonLabels[$report['reason']] ?? $report['reason']) ?></td>
                            <td><span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $report['created_at']) ?></td>
                            <td>
                                <div class="flex items-center gap-2 [&_a]:bg-transparent [&_a]:border-0 [&_a]:cursor-pointer [&_a]:p-1 [&_a]:rounded-sm [&_a]:text-muted [&_a]:transition-colors [&_a]:flex [&_a]:items-center [&_a]:no-underline [&_a:hover]:text-primary [&_a:hover]:bg-primary-light [&_button]:bg-transparent [&_button]:border-0 [&_button]:cursor-pointer [&_button]:p-1 [&_button]:rounded-sm [&_button]:text-muted [&_button]:transition-colors [&_button]:flex [&_button]:items-center [&_button:hover]:text-primary [&_button:hover]:bg-primary-light [&_button.danger:hover]:text-danger [&_button.danger:hover]:bg-danger-bg [&_svg]:w-4 [&_svg]:h-4 [&_img]:w-4 [&_img]:h-4 [&_img]:object-contain">
                                    <?php if ($report['shop_slug']): ?>
                                        <a href="/boutiques/<?= htmlspecialchars($report['shop_slug']) ?>" title="Voir la boutique">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php else: ?>
                                        <a href="#" aria-disabled="true" class="opacity-35 cursor-not-allowed pointer-events-none" title="Contenu supprimé">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($report['status'] === 'pending'): ?>
                                        <details class="relative inline-flex">
                                            <summary class="list-none cursor-pointer p-1 rounded-sm text-muted flex items-center transition-colors hover:text-primary hover:bg-primary-light [&::-webkit-details-marker]:hidden" title="Traiter le signalement">
                                                <img src="/assets/images/icones/modifier.png" alt="Traiter">
                                            </summary>
                                            <div class="absolute top-full right-0 mt-[0.4rem] bg-white border border-border rounded-md shadow-sm p-3 min-w-[170px] z-30 [&_label]:block [&_label]:text-[0.75rem] [&_label]:text-muted [&_label]:mb-[0.35rem] [&_select]:w-full [&_select]:border [&_select]:border-border [&_select]:rounded-sm [&_select]:px-2 [&_select]:py-[0.35rem] [&_select]:text-[0.85rem] [&_select]:font-main [&_select]:bg-bg">
                                                <?php if (!empty($report['message'])): ?>
                                                    <p style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.6rem; max-width: 220px;">
                                                        « <?= nl2br(htmlspecialchars($report['message'])) ?> »
                                                    </p>
                                                <?php endif; ?>
                                                <label>Statut du signalement</label>
                                                <form method="POST" action="/admin/reports/<?= $report['id'] ?>/resolve" style="margin-bottom: 0.4rem;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                                    <button type="submit" class="btn btn--primary" style="width: 100%;">Marquer résolu</button>
                                                </form>
                                                <form method="POST" action="/admin/reports/<?= $report['id'] ?>/dismiss">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                                    <button type="submit" class="btn btn--outline" style="width: 100%;">Rejeter</button>
                                                </form>
                                            </div>
                                        </details>
                                    <?php else: ?>
                                        <a href="#" aria-disabled="true" class="opacity-35 cursor-not-allowed pointer-events-none" title="Déjà traité">
                                            <img src="/assets/images/icones/modifier.png" alt="Traiter">
                                        </a>
                                    <?php endif; ?>

                                    <form method="POST" action="/admin/reports/<?= $report['id'] ?>/delete">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                        <button
                                            type="submit"
                                            class="danger"
                                            title="Supprimer le signalement"
                                            onclick="return confirm('Supprimer ce signalement ? Le contenu signalé ne sera pas affecté.');">
                                            <img src="/assets/images/icones/supprimer.png" alt="Supprimer">
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php $entityLabel = 'signalements'; ?>
    <?php require __DIR__ . '/../components/pagination.php'; ?>
</div>

<script src="/assets/js/admin-table-select.js"></script>
