<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::orders()).
 *
 * @var array $orders
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, status: string, registered: string, archived: string} $filters
 * @var array $stats
 * @var array<int, int|string> $pageNumbers
 */

$statusLabels = \App\Models\Order::statusLabels();

$totalPages = max(1, (int) ceil($total / $perPage));
$rangeStart = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$rangeEnd = min($total, $page * $perPage);

$queryWithout = function (array $overrides = []) use ($filters) {
    $params = array_merge(
        array_diff_key($filters, array_flip(['page', 'per_page'])),
        $overrides
    );
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '/admin/orders' . ($params !== [] ? '?' . http_build_query($params) : '');
};
?>

<div class="admin-stats">
    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commandes.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Commandes totales</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commandes.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['pending'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">En attente</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commandes.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['in_progress'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">En cours</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commandes.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['completed'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Terminées</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['cancelled'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Annulées</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commissions.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total_revenue'] / 100, 2, ',', ' ') ?>€</div>
        <div class="admin-stat-card__label">Revenu total</div>
        <div class="admin-stat-card__trend">↗ +<?= number_format($stats['new_revenue_this_week'] / 100, 2, ',', ' ') ?>€ cette semaine</div>
    </div>
</div>

<div class="admin-table-wrapper">
    <form action="/admin/orders" method="GET" class="admin-table-filters">
        <?php
        $searchStandalone = false;
        $searchValue = $filters['q'];
        ?>
        <?php require __DIR__ . '/../components/search-bar.php'; ?>

        <select name="status" onchange="this.form.submit()">
            <option value="">Statut : Tous</option>
            <?php foreach ($statusLabels as $value => $info): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($info['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="registered" onchange="this.form.submit()">
            <option value="">Date : Tous</option>
            <option value="week" <?= $filters['registered'] === 'week' ? 'selected' : '' ?>>Cette semaine</option>
            <option value="month" <?= $filters['registered'] === 'month' ? 'selected' : '' ?>>Ce mois-ci</option>
        </select>

        <select name="archived" onchange="this.form.submit()">
            <option value="" <?= $filters['archived'] === '' ? 'selected' : '' ?>>Archivage : Actives</option>
            <option value="only" <?= $filters['archived'] === 'only' ? 'selected' : '' ?>>Archivées</option>
            <option value="all" <?= $filters['archived'] === 'all' ? 'selected' : '' ?>>Toutes</option>
        </select>
    </form>

    <form method="POST" action="/admin/orders/bulk-archive" id="ordersBulkForm" data-bulk-form data-confirm="Archiver les %d commande(s) sélectionnée(s) ?" hidden>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
    </form>

    <?php if (empty($orders)): ?>
        <p class="admin-panel__empty" style="padding: 1.5rem;">Aucune commande ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="admin-table-scroll">
            <table class="admin-table" data-bulk-table data-bulk-form="ordersBulkForm">
                <thead>
                    <tr>
                        <th>
                            <span class="admin-th-select">
                                <input type="checkbox" data-select-all aria-label="Tout sélectionner">
                                <button type="submit" form="ordersBulkForm" class="admin-bulk-trigger" data-bulk-trigger hidden title="Archiver la sélection">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="4" rx="1"></rect>
                                        <path d="M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8"></path>
                                        <line x1="10" y1="12" x2="14" y2="12"></line>
                                    </svg>
                                </button>
                            </span>
                        </th>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Artiste</th>
                        <th>Prestation</th>
                        <th>Montant</th>
                        <th>Commission</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php $statusInfo = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => 'badge--neutral']; ?>
                        <tr>
                            <td><input type="checkbox" class="js-row-select" value="<?= (int) $order['id'] ?>" aria-label="Sélectionner la commande #<?= (int) $order['id'] ?>"></td>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['client_name']) ?></td>
                            <td><?= htmlspecialchars($order['shop_name']) ?></td>
                            <td><?= htmlspecialchars($order['service_title'] ?? $order['title']) ?></td>
                            <td><?= number_format($order['total_price'] / 100, 2, ',', ' ') ?> €</td>
                            <td><?= number_format($order['commission_amount'] / 100, 2, ',', ' ') ?> €</td>
                            <td><span class="badge <?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="/commandes/<?= (int) $order['id'] ?>" title="Voir la commande">
                                        <img src="/assets/images/icones/voir.png" alt="Voir">
                                    </a>
                                    <a href="#" aria-disabled="true" title="Modifier (bientôt disponible)">
                                        <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                    </a>
                                    <form method="POST" action="/admin/orders/<?= (int) $order['id'] ?>/toggle-archive">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                        <?php if ($order['is_archived']): ?>
                                            <button type="submit" title="Restaurer">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 10 3 4 9 4"></polyline>
                                                    <path d="M3.5 15a9 9 0 1 0 2-9.5L3 10"></path>
                                                </svg>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" title="Archiver">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="4" rx="1"></rect>
                                                    <path d="M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8"></path>
                                                    <line x1="10" y1="12" x2="14" y2="12"></line>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php $entityLabel = 'commandes'; ?>
    <?php require __DIR__ . '/../components/pagination.php'; ?>
</div>

<script src="/assets/js/admin-table-select.js"></script>
