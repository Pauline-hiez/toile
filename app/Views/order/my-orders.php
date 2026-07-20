<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir OrderController::myOrders()).
 *
 * @var array $orders
 */
$pageTitle = 'Mes commandes — Toile';
$statusLabels = \App\Models\Order::statusLabels();
?>

<div class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="text-center font-cursive text-[1.9rem] font-semibold text-ink mb-8">Mes commandes</h1>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <?php if (empty($orders)): ?>
            <div class="text-center p-10">
                <p class="text-muted text-[0.85rem] mb-4">Tu n'as pas encore passé de commande.</p>
                <a href="/boutiques" class="btn btn--primary">Découvrir les artistes</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2]">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Boutique</th>
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
                                <td>
                                    <a href="/boutiques/<?= htmlspecialchars($order['shop_slug']) ?>" class="text-ink no-underline hover:text-primary transition-colors">
                                        <?= htmlspecialchars($order['shop_name']) ?>
                                    </a>
                                </td>
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
</div>
