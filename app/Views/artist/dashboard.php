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
    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/commandes.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= (int) $stats['order_count'] ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Commandes</div>
        <div class="text-[0.75rem] text-success">↗ +<?= (int) $stats['new_orders'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/favoris.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= (int) $stats['favorite_count'] ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Favoris</div>
        <div class="text-[0.75rem] text-success">↗ +<?= (int) $stats['new_favorites'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/utilisateurs.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= (int) $stats['client_count'] ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Clients</div>
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
            <?php
            // Icônes par type de notification — même map que
            // components/notif-dropdown.php et notification/index.php.
            $notifIcons = [
                'message' => '<path d="M21 11.5a8.4 8.4 0 0 1-8.9 8.4 9 9 0 0 1-3.5-.8L3 21l1.9-5.7A8.4 8.4 0 1 1 21 11.5Z"></path>',
                'document' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M9 13h6M9 17h6"></path>',
                'money' => '<circle cx="12" cy="12" r="9"></circle><path d="M9 15s.8 1 3 1 3-.9 3-2-1.5-1.7-3-2-3-.9-3-2 1.3-2 3-2 3 1 3 1"></path>',
                'refund' => '<path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 3v5h5"></path>',
                'check' => '<circle cx="12" cy="12" r="9"></circle><path d="m8.5 12.5 2.5 2.5 5-5"></path>',
                'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>',
                'x' => '<circle cx="12" cy="12" r="9"></circle><path d="m9 9 6 6M15 9l-6 6"></path>',
                'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 3.5 1 5 2 6H4c1-1 2-2.5 2-6Z"></path><path d="M10 20a2 2 0 0 0 4 0"></path>',
            ];
            ?>
            <div class="flex-1 flex flex-col gap-[0.9rem] overflow-y-auto max-h-[320px]">
                <?php foreach ($history as $item): ?>
                    <?php $typeInfo = \App\Models\Notification::typeInfo($item['type']); ?>
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-full bg-primary-light text-primary shrink-0 flex items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><?= $notifIcons[$typeInfo['icon']] ?? $notifIcons['bell'] ?></svg>
                        </span>
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
                                <td><?= $order['total_price'] > 0 ? number_format($order['total_price'] / 100, 2, ',', ' ') . '€' : 'À convenir' ?></td>
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
