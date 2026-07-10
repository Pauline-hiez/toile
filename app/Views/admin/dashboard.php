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

$orderStatusLabels = [
    'quote_requested' => ['label' => 'Devis demandé', 'class' => 'badge--neutral'],
    'pending' => ['label' => 'En attente', 'class' => 'badge--warning'],
    'accepted' => ['label' => 'Acceptée', 'class' => 'badge--info'],
    'rejected' => ['label' => 'Refusée', 'class' => 'badge--danger'],
    'in_progress' => ['label' => 'En cours', 'class' => 'badge--info'],
    'delivered' => ['label' => 'Livrée', 'class' => 'badge--info'],
    'completed' => ['label' => 'Terminée', 'class' => 'badge--success'],
    'cancelled' => ['label' => 'Annulée', 'class' => 'badge--danger'],
];
?>

<?php if ($stats['pending_artist_requests'] > 0): ?>
    <div class="admin-alert">
        <span><strong><?= $stats['pending_artist_requests'] ?></strong> demande(s) artiste en attente de traitement.</span>
        <a href="/admin/artist-requests">Traiter →</a>
    </div>
<?php endif; ?>

<div class="admin-stats">
    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/users.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total_users'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Utilisateurs</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_users'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/artiste.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total_shops'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Artistes</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_shops'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commandes.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total_orders'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Commandes</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_orders'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commissions.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total_commissions'] / 100, 2, ',', ' ') ?>€</div>
        <div class="admin-stat-card__label">Commissions</div>
        <div class="admin-stat-card__trend">↗ +<?= number_format($stats['new_commissions'] / 100, 2, ',', ' ') ?>€ cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/essentiel.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total_revenue'] / 100, 2, ',', ' ') ?>€</div>
        <div class="admin-stat-card__label">Revenus</div>
        <div class="admin-stat-card__trend">↗ +<?= number_format($stats['new_revenue'] / 100, 2, ',', ' ') ?>€ cette semaine</div>
    </div>
</div>

<div class="admin-panels">
    <div class="admin-panel">
        <h2>Aperçu de l'activité</h2>
        <div
            class="admin-chart"
            data-admin-chart
            data-labels="<?= htmlspecialchars(json_encode($activityChart['labels']), ENT_QUOTES) ?>"
            data-values="<?= htmlspecialchars(json_encode($activityChart['values']), ENT_QUOTES) ?>"
            data-suffix=" commande(s)"
            role="img"
            aria-label="Nombre de commandes par jour sur les 14 derniers jours"
        ></div>
        <a href="#" class="btn btn--primary">Voir toutes les statistiques</a>
    </div>

    <div class="admin-panel">
        <h2>Inscriptions cette semaine</h2>
        <?php if (empty($recentSignups)): ?>
            <p class="admin-panel__empty">Aucune nouvelle inscription cette semaine.</p>
        <?php else: ?>
            <div class="admin-panel__signups">
                <?php foreach ($recentSignups as $signup): ?>
                    <div class="admin-signup-item">
                        <?php if (!empty($signup['avatar'])): ?>
                            <img src="/uploads/avatars/<?= htmlspecialchars($signup['avatar']) ?>" alt="">
                        <?php else: ?>
                            <img src="/assets/images/icones/new-user.png" alt="">
                        <?php endif; ?>
                        <div>
                            <span class="admin-signup-item__name"><?= htmlspecialchars($signup['username']) ?></span>
                            <span class="admin-signup-item__date"><?= date('d M Y', strtotime($signup['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-section">
    <div class="admin-section__header">
        <h2>Derniers artistes inscrits</h2>
    </div>

    <div class="admin-table-wrapper">
        <?php if (empty($recentArtists)): ?>
            <p class="admin-panel__empty" style="padding: 1.5rem;">Aucun artiste inscrit pour le moment.</p>
        <?php else: ?>
            <div class="admin-table-scroll">
                <table class="admin-table">
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
                                        <img class="admin-table-avatar" src="/uploads/avatars/<?= htmlspecialchars($artist['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <img class="admin-table-avatar" src="/assets/images/icones/new-user.png" alt="">
                                    <?php endif; ?>
                                </td>
                                <td><a href="/boutiques/<?= htmlspecialchars($artist['slug']) ?>"><?= htmlspecialchars($artist['username']) ?></a></td>
                                <td><?= date('d M Y', strtotime($artist['created_at'])) ?></td>
                                <td><?= (int) $artist['service_count'] ?></td>
                                <td>
                                    <?php if ($artist['is_open']): ?>
                                        <span class="badge badge--success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge--warning">En attente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="admin-table-footer">
            <a href="/admin/shops" class="btn btn--primary">Voir tout</a>
        </div>
    </div>
</div>

<div class="admin-section">
    <div class="admin-section__header">
        <h2>Dernières commandes</h2>
    </div>

    <div class="admin-table-wrapper">
        <?php if (empty($recentOrders)): ?>
            <p class="admin-panel__empty" style="padding: 1.5rem;">Aucune commande pour le moment.</p>
        <?php else: ?>
            <div class="admin-table-scroll">
                <table class="admin-table">
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
                            <?php $statusInfo = $orderStatusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => 'badge--neutral']; ?>
                            <tr>
                                <td>#<?= (int) $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['client_name']) ?></td>
                                <td><?= htmlspecialchars($order['shop_name']) ?></td>
                                <td><?= htmlspecialchars($order['title']) ?></td>
                                <td><span class="badge <?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="admin-table-footer">
            <a href="#" class="btn btn--primary">Voir tout</a>
        </div>
    </div>
</div>

<script src="/assets/js/admin-chart.js"></script>
