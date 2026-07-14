<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir OrderController::receivedOrders()).
 *
 * @var array $orders
 * @var array $recentOrders
 * @var array{total: int, in_progress: int, pending: int} $stats
 * @var int $pendingCount
 */
$statusLabels = \App\Models\Order::statusLabels();
?>

<?php if ($pendingCount > 0): ?>
    <div class="flex items-center justify-between gap-4 flex-wrap bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <span>Vous avez <?= $pendingCount ?> demande<?= $pendingCount > 1 ? 's' : '' ?> en attente</span>
        <a href="#recentes" class="btn btn--primary">Voir les demandes</a>
    </div>
<?php endif; ?>

<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Nombre de commandes</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= (int) $stats['total'] ?></div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Commande en cours</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= (int) $stats['in_progress'] ?></div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Commandes en attente</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= (int) $stats['pending'] ?></div>
    </div>
</div>

<div id="recentes" class="bg-white border border-border rounded-md p-6 shadow-sm">
    <h2 class="text-base font-semibold mb-5 text-ink">Commandes récentes</h2>

    <?php if (empty($recentOrders)): ?>
        <p class="text-muted text-[0.85rem] text-center py-6">Tu n'as pas encore reçu de commande.</p>
    <?php else: ?>
        <div class="flex flex-col gap-3">
            <?php foreach ($recentOrders as $order): ?>
                <?php $statusInfo = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                <a href="/commandes/<?= $order['id'] ?>" class="flex items-center gap-4 p-3 border border-border rounded-md no-underline text-inherit transition-colors hover:border-primary">
                    <img src="/assets/images/icones/commande.png" alt="" class="w-10 h-10 rounded-full object-contain bg-primary-light p-2 shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[0.95rem] text-ink"><?= htmlspecialchars($order['title']) ?></p>
                        <p class="text-[0.8rem] text-muted">
                            Commandé par <?= htmlspecialchars($order['client_name']) ?><br>
                            Le <?= \App\Core\FrenchDate::format("d MMM y 'à' HH'h'mm", $order['created_at']) ?>
                        </p>
                    </div>
                    <span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-6">
            <a href="#toutes" class="btn btn--primary">Voir toutes les commandes</a>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($orders)): ?>
    <div id="toutes" class="bg-white border border-border rounded-md p-6 shadow-sm mt-8">
        <h2 class="text-base font-semibold mb-5 text-ink">Toutes les commandes</h2>

        <div class="flex flex-col gap-3">
            <?php foreach ($orders as $order): ?>
                <?php $statusInfo = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                <a href="/commandes/<?= $order['id'] ?>" class="flex items-center gap-4 p-3 border border-border rounded-md no-underline text-inherit transition-colors hover:border-primary">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[0.95rem] text-ink"><?= htmlspecialchars($order['title']) ?></p>
                        <p class="text-[0.8rem] text-muted">
                            Commandé par <?= htmlspecialchars($order['client_name']) ?>
                            — <?= number_format($order['total_price'] / 100, 2) ?> €
                            — <?= \App\Core\FrenchDate::format('d MMM y', $order['created_at']) ?>
                        </p>
                    </div>
                    <span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
