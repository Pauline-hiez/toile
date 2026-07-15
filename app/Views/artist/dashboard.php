<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ArtistDashboardController::index()).
 *
 * @var array|null $shop
 * @var array{order_count: int, new_orders: int, favorite_count: int, new_favorites: int, client_count: int, new_clients: int} $stats
 * @var array $history
 * @var array $recentOrders
 * @var array{labels: array, values: array} $activityChart
 */

$orderStatusLabels = \App\Models\Order::statusLabels();
?>

<?php if ($shop === null): ?>
    <div class="bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-4 mb-8 text-[0.9rem]">
        Tu n'as pas encore créé de boutique. <a href="/my-shop" class="font-semibold underline">Configure-la maintenant →</a>
    </div>
<?php endif; ?>

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/commandes.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= (int) $stats['order_count'] ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Commandes</div>
        <div class="text-[0.75rem] text-success">↗ +<?= (int) $stats['new_orders'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/favoris.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= (int) $stats['favorite_count'] ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Favoris</div>
        <div class="text-[0.75rem] text-success">↗ +<?= (int) $stats['new_favorites'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/users.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= (int) $stats['client_count'] ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Clients</div>
        <div class="text-[0.75rem] text-success">↗ +<?= (int) $stats['new_clients'] ?> cette semaine</div>
    </div>
</div>

<div class="grid grid-cols-1 min-[1101px]:grid-cols-[2fr_1fr] gap-6 mb-8 items-stretch">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <h2 class="text-base font-semibold mb-5 text-ink">Aperçu de l'activité</h2>
        <div
            class="relative flex-1 min-h-[220px]"
            data-admin-chart
            data-labels="<?= htmlspecialchars(json_encode($activityChart['labels']), ENT_QUOTES) ?>"
            data-values="<?= htmlspecialchars(json_encode($activityChart['values']), ENT_QUOTES) ?>"
            data-suffix=" commande(s)"
            role="img"
            aria-label="Nombre de commandes par jour sur les 14 derniers jours"
        ></div>
    </div>

    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <h2 class="text-base font-semibold mb-5 text-ink">Historique</h2>
        <?php if (empty($history)): ?>
            <p class="text-muted text-[0.85rem] my-auto text-center">Aucune activité pour le moment.</p>
        <?php else: ?>
            <div class="flex-1 flex flex-col gap-[0.9rem] overflow-y-auto max-h-[320px]">
                <?php foreach ($history as $item): ?>
                    <div class="flex items-center gap-3">
                        <img src="/assets/images/icones/notifications.png" alt="" class="w-9 h-9 rounded-full object-contain shrink-0 bg-primary-light p-2">
                        <div class="min-w-0">
                            <p class="text-[0.85rem] text-ink font-medium truncate">
                                <?php if (!empty($item['link'])): ?>
                                    <a href="<?= htmlspecialchars($item['link']) ?>" class="no-underline text-ink hover:underline"><?= htmlspecialchars($item['message']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($item['message']) ?>
                                <?php endif; ?>
                            </p>
                            <p class="text-[0.75rem] text-muted"><?= \App\Core\FrenchDate::format('d MMM y', $item['created_at']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="/notifications" class="btn btn--primary">Voir tout</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mb-8">
    <div class="flex items-center justify-between mb-4 max-[720px]:flex-col max-[720px]:items-start max-[720px]:gap-2">
        <h2 class="text-[1.05rem] font-semibold">Dernières commandes</h2>
    </div>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <?php if (empty($recentOrders)): ?>
            <p class="text-muted text-[0.85rem] text-center p-6">Aucune commande pour le moment.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2] [&_a]:no-underline [&_a]:text-inherit">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Titre</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <?php $statusInfo = $orderStatusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                            <tr>
                                <td><a href="/commandes/<?= $order['id'] ?>">#<?= (int) $order['id'] ?></a></td>
                                <td><a href="/commandes/<?= $order['id'] ?>"><?= htmlspecialchars($order['client_name']) ?></a></td>
                                <td><a href="/commandes/<?= $order['id'] ?>"><?= htmlspecialchars($order['title']) ?></a></td>
                                <td><?= number_format($order['total_price'] / 100, 2, ',', ' ') ?>€</td>
                                <td><span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                                <td><?= \App\Core\FrenchDate::format('d MMM y', $order['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="p-5 text-center border-t border-border">
            <a href="/commandes-recues" class="btn btn--primary">Voir tout</a>
        </div>
    </div>
</div>

<script src="/assets/js/admin-chart.js"></script>
