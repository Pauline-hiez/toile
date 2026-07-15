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

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-4 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[88px] h-[88px] object-contain" src="/assets/images/icones/commandes.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Commandes totales</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-4 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[88px] h-[88px] object-contain" src="/assets/images/icones/commandes-attente.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['pending'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">En attente</div>
    </div>

    <div class="bg-white border border-border rounded-md p-4 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[88px] h-[88px] object-contain" src="/assets/images/icones/commandes-cours.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['in_progress'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">En cours</div>
    </div>

    <div class="bg-white border border-border rounded-md p-4 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[88px] h-[88px] object-contain" src="/assets/images/icones/commandes-terminees.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['completed'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Terminées</div>
    </div>

    <div class="bg-white border border-border rounded-md p-4 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[88px] h-[88px] object-contain" src="/assets/images/icones/commandes-annulees.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['cancelled'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Annulées</div>
    </div>

    <div class="bg-white border border-border rounded-md p-4 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[88px] h-[88px] object-contain" src="/assets/images/icones/commissions.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total_revenue'] / 100, 2, ',', ' ') ?>€</div>
        <div class="text-[0.8rem] text-muted font-medium">Revenu total</div>
        <div class="text-[0.75rem] text-success">↗ +<?= number_format($stats['new_revenue_this_week'] / 100, 2, ',', ' ') ?>€ cette semaine</div>
    </div>

    <a href="/admin/orders?archived=only" class="bg-white border border-border rounded-md p-4 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]" title="Voir les commandes archivées">
        <svg class="w-[88px] h-[88px] object-contain" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-primary-muted);">
            <rect x="3" y="4" width="18" height="4" rx="1"></rect>
            <path d="M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8"></path>
            <line x1="10" y1="12" x2="14" y2="12"></line>
        </svg>
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['archived'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Archivées</div>
    </a>
</div>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <form action="/admin/orders" method="GET" class="p-5 border-b border-border flex items-center gap-3 flex-wrap [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-4 [&_select]:py-[0.4rem] [&_select]:text-[0.85rem] [&_select]:outline-none [&_select]:bg-bg [&_select]:font-main">
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
        <p class="text-muted text-[0.85rem] text-center p-6">Aucune commande ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2] [&_input]:w-4 [&_input]:h-4 [&_input]:accent-primary [&_input]:cursor-pointer" data-bulk-table data-bulk-form="ordersBulkForm">
                <thead>
                    <tr>
                        <th>
                            <span class="inline-flex items-center gap-2">
                                <input type="checkbox" data-select-all aria-label="Tout sélectionner">
                                <button type="submit" form="ordersBulkForm" class="inline-flex items-center bg-transparent border-0 cursor-pointer p-1 rounded-sm text-muted transition-colors hover:text-primary hover:bg-primary-light [&_svg]:w-4 [&_svg]:h-4" data-bulk-trigger hidden title="Archiver la sélection">
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
                        <?php $statusInfo = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                        <tr>
                            <td><input type="checkbox" class="js-row-select" value="<?= (int) $order['id'] ?>" aria-label="Sélectionner la commande #<?= (int) $order['id'] ?>"></td>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['client_name']) ?></td>
                            <td><?= htmlspecialchars($order['shop_name']) ?></td>
                            <td><?= htmlspecialchars($order['service_title'] ?? $order['title']) ?></td>
                            <td><?= number_format($order['total_price'] / 100, 2, ',', ' ') ?> €</td>
                            <td><?= number_format($order['commission_amount'] / 100, 2, ',', ' ') ?> €</td>
                            <td><span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $order['created_at']) ?></td>
                            <td>
                                <div class="flex items-center gap-2 [&_a]:bg-transparent [&_a]:border-0 [&_a]:cursor-pointer [&_a]:p-1 [&_a]:rounded-sm [&_a]:text-muted [&_a]:transition-colors [&_a]:flex [&_a]:items-center [&_a]:no-underline [&_a:hover]:text-primary [&_a:hover]:bg-primary-light [&_button]:bg-transparent [&_button]:border-0 [&_button]:cursor-pointer [&_button]:p-1 [&_button]:rounded-sm [&_button]:text-muted [&_button]:transition-colors [&_button]:flex [&_button]:items-center [&_button:hover]:text-primary [&_button:hover]:bg-primary-light [&_button.danger:hover]:text-danger [&_button.danger:hover]:bg-danger-bg [&_svg]:w-4 [&_svg]:h-4 [&_img]:w-4 [&_img]:h-4 [&_img]:object-contain">
                                    <a href="/commandes/<?= (int) $order['id'] ?>" title="Voir la commande">
                                        <img src="/assets/images/icones/voir.png" alt="Voir">
                                    </a>
                                    <a href="#" aria-disabled="true" class="opacity-35 cursor-not-allowed pointer-events-none" title="Modifier (bientôt disponible)">
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
