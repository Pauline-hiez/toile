<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir OrderController::receivedOrders()).
 *
 * @var array $orders
 * @var array{total: int, in_progress: int, pending: int} $stats
 * @var int $pendingCount
 */
$statusLabels = \App\Models\Order::statusLabels();
?>

<?php if ($pendingCount > 0): ?>
    <div class="flex items-center justify-between gap-4 flex-wrap bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <span>Tu as <?= $pendingCount ?> demande<?= $pendingCount > 1 ? 's' : '' ?> en attente</span>
        <a href="#toutes" class="btn btn--primary">Voir les demandes</a>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 min-[481px]:grid-cols-3 gap-4 mb-8">
    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/commandes.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= (int) $stats['total'] ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Nombre de commandes</div>
    </div>

    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/commandes-cours.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= (int) $stats['in_progress'] ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Commande en cours</div>
    </div>

    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/commandes-attente.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= (int) $stats['pending'] ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Commandes en attente</div>
    </div>
</div>

<div id="toutes" class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <?php if (empty($orders)): ?>
        <p class="text-muted text-[0.85rem] text-center p-6">Tu n'as pas encore reçu de commande.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="data-table max-[720px]:min-w-[640px]">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Prestation</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php $statusInfo = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['client_name']) ?></td>
                            <td><?= htmlspecialchars($order['title']) ?></td>
                            <td><?= $order['total_price'] > 0 ? number_format($order['total_price'] / 100, 2, ',', ' ') . ' €' : 'À convenir' ?></td>
                            <td><span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $order['created_at']) ?></td>
                            <td>
                                <a href="/commandes/<?= (int) $order['id'] ?>" title="Voir la commande" class="inline-flex items-center justify-center p-1 rounded-sm text-muted transition-colors hover:text-primary hover:bg-primary-light">
                                    <img src="/assets/images/icones/voir.png" alt="Voir" class="w-4 h-4 object-contain">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
