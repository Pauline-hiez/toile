<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::dashboard()).
 *
 * @var array $stats
 * @var array $recentArtists
 * @var array $recentOrders
 * @var array $recentSignups
 * @var array{labels: array, values: array} $activityChart
 */

$orderStatusLabels = \App\Models\Order::statusLabels();
?>

<?php if ($stats['pending_artist_requests'] > 0): ?>
    <div class="flex items-center justify-between gap-4 flex-wrap bg-danger-bg border border-danger/25 text-danger rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <span><strong><?= $stats['pending_artist_requests'] ?></strong> demande(s) artiste en attente de traitement.</span>
        <a href="/admin/artist-requests" class="text-danger font-semibold underline whitespace-nowrap">Traiter →</a>
    </div>
<?php endif; ?>

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/users.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total_users'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Utilisateurs</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_users'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/artiste.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total_shops'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Artistes</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_shops'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/commandes.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total_orders'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Commandes</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_orders'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/commissions.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total_commissions'] / 100, 2, ',', ' ') ?>€</div>
        <div class="text-[0.8rem] text-muted font-medium">Commissions</div>
        <div class="text-[0.75rem] text-success">↗ +<?= number_format($stats['new_commissions'] / 100, 2, ',', ' ') ?>€ cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/essentiel.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total_revenue'] / 100, 2, ',', ' ') ?>€</div>
        <div class="text-[0.8rem] text-muted font-medium">Revenus</div>
        <div class="text-[0.75rem] text-success">↗ +<?= number_format($stats['new_revenue'] / 100, 2, ',', ' ') ?>€ cette semaine</div>
    </div>
</div>

<div class="grid grid-cols-1 min-[1101px]:grid-cols-[2fr_1fr] gap-6 mb-8 items-stretch">
    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <h2 class="text-base font-semibold mb-5 text-ink">Aperçu de l'activité</h2>
        <div
            class="relative flex-1 min-h-[220px] mb-6"
            data-admin-chart
            data-labels="<?= htmlspecialchars(json_encode($activityChart['labels']), ENT_QUOTES) ?>"
            data-values="<?= htmlspecialchars(json_encode($activityChart['values']), ENT_QUOTES) ?>"
            data-suffix=" commande(s)"
            role="img"
            aria-label="Nombre de commandes par jour sur les 14 derniers jours"
        ></div>
        <a href="#" class="btn btn--primary self-center">Voir toutes les statistiques</a>
    </div>

    <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
        <h2 class="text-base font-semibold mb-5 text-ink">Inscriptions cette semaine</h2>
        <?php if (empty($recentSignups)): ?>
            <p class="text-muted text-[0.85rem] my-auto text-center">Aucune nouvelle inscription cette semaine.</p>
        <?php else: ?>
            <div class="flex-1 flex flex-col gap-[0.9rem]">
                <?php foreach ($recentSignups as $signup): ?>
                    <div class="flex items-center gap-[0.65rem]">
                        <?php if (!empty($signup['avatar'])): ?>
                            <img src="/uploads/avatars/<?= htmlspecialchars($signup['avatar']) ?>" alt="" class="w-9 h-9 rounded-full object-cover border border-border shrink-0">
                        <?php else: ?>
                            <img src="/assets/images/icones/new-user.png" alt="" class="w-9 h-9 rounded-full object-cover border border-border shrink-0">
                        <?php endif; ?>
                        <div>
                            <span class="text-[0.85rem] font-semibold block"><?= htmlspecialchars($signup['username']) ?></span>
                            <span class="text-[0.75rem] text-muted"><?= \App\Core\FrenchDate::format('d MMM y', $signup['created_at']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mb-8">
    <div class="flex items-center justify-between mb-4 max-[720px]:flex-col max-[720px]:items-start max-[720px]:gap-2">
        <h2 class="text-[1.05rem] font-semibold">Derniers artistes inscrits</h2>
    </div>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <?php if (empty($recentArtists)): ?>
            <p class="text-muted text-[0.85rem] text-center p-6">Aucun artiste inscrit pour le moment.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2] [&_input]:w-4 [&_input]:h-4 [&_input]:accent-primary [&_input]:cursor-pointer">
                    <thead>
                        <tr>
                            <th>Artiste</th>
                            <th>Pseudo</th>
                            <th>Inscription</th>
                            <th>Prestations</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentArtists as $artist): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($artist['avatar'])): ?>
                                        <img class="w-9 h-9 rounded-full object-cover border border-border" src="/uploads/avatars/<?= htmlspecialchars($artist['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <img class="w-9 h-9 rounded-full object-cover border border-border" src="/assets/images/icones/new-user.png" alt="">
                                    <?php endif; ?>
                                </td>
                                <td><a href="/boutiques/<?= htmlspecialchars($artist['slug']) ?>"><?= htmlspecialchars($artist['username']) ?></a></td>
                                <td><?= \App\Core\FrenchDate::format('d MMM y', $artist['created_at']) ?></td>
                                <td><?= (int) $artist['service_count'] ?></td>
                                <td>
                                    <?php if ($artist['is_open']): ?>
                                        <span class="<?= \App\Core\Badge::classes('success') ?>">Actif</span>
                                    <?php else: ?>
                                        <span class="<?= \App\Core\Badge::classes('warning') ?>">En attente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="p-5 text-center border-t border-border">
            <a href="/admin/shops" class="btn btn--primary">Voir tout</a>
        </div>
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
                <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2] [&_input]:w-4 [&_input]:h-4 [&_input]:accent-primary [&_input]:cursor-pointer">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Artiste</th>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <?php $statusInfo = $orderStatusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                            <tr>
                                <td>#<?= (int) $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['client_name']) ?></td>
                                <td><?= htmlspecialchars($order['shop_name']) ?></td>
                                <td><?= htmlspecialchars($order['title']) ?></td>
                                <td><span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                                <td><?= \App\Core\FrenchDate::format('d MMM y', $order['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="p-5 text-center border-t border-border">
            <a href="#" class="btn btn--primary">Voir tout</a>
        </div>
    </div>
</div>

<script src="/assets/js/admin-chart.js"></script>
