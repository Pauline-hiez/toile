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
    'pending' => ['label' => 'En attente', 'class' => 'badge--warning'],
    'resolved' => ['label' => 'Résolu', 'class' => 'badge--success'],
    'dismissed' => ['label' => 'Rejeté', 'class' => 'badge--neutral'],
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

<div class="admin-stats">
    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Total signalements</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['pending'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">En attente</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['resolved'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Résolus</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['dismissed'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Rejetés</div>
    </div>
</div>

<div class="admin-table-wrapper">
    <form action="/admin/reports" method="GET" class="admin-table-filters">
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
        <p class="admin-panel__empty" style="padding: 1.5rem;">Aucun signalement ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="admin-table-scroll">
            <table class="admin-table" data-bulk-table>
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
                        <?php $statusInfo = $statusLabels[$report['status']] ?? ['label' => $report['status'], 'class' => 'badge--neutral']; ?>
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
                            <td><span class="badge <?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $report['created_at']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <?php if ($report['shop_slug']): ?>
                                        <a href="/boutiques/<?= htmlspecialchars($report['shop_slug']) ?>" title="Voir la boutique">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php else: ?>
                                        <a href="#" aria-disabled="true" title="Contenu supprimé">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($report['status'] === 'pending'): ?>
                                        <details class="action-popover">
                                            <summary title="Traiter le signalement">
                                                <img src="/assets/images/icones/modifier.png" alt="Traiter">
                                            </summary>
                                            <div class="action-popover__panel">
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
                                        <a href="#" aria-disabled="true" title="Déjà traité">
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
