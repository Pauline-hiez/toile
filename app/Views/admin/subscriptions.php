<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::subscriptions()).
 *
 * @var array $subscriptions
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, status: string, registered: string, plan: string} $filters
 * @var array $stats
 * @var array<int, int|string> $pageNumbers
 */

$statusLabels = [
    'active' => ['label' => 'En cours', 'class' => 'badge--success'],
    'past_due' => ['label' => 'En attente', 'class' => 'badge--warning'],
    'cancelled' => ['label' => 'Expiré', 'class' => 'badge--danger'],
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
    return '/admin/subscriptions' . ($params !== [] ? '?' . http_build_query($params) : '');
};
?>

<div class="admin-stats">
    <a href="/admin/subscriptions?plan=Commission" class="admin-stat-card" title="Voir les boutiques sur la formule gratuite">
        <img class="admin-stat-card__icon" src="/assets/images/icones/artiste.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['commission'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Formule Commission</div>
    </a>

    <a href="/admin/subscriptions?plan=Essentiel" class="admin-stat-card" title="Voir les boutiques en formule Essentiel">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commissions.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['essentiel'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Formule Essentiel</div>
    </a>

    <a href="/admin/subscriptions?plan=Pro" class="admin-stat-card" title="Voir les boutiques en formule Pro">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commissions.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['pro'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Formule Pro</div>
    </a>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['pending_choice'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">En attente de choix</div>
        <div class="admin-stat-card__trend">Boutiques pas encore ouvertes</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/commissions.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['mrr'] / 100, 2, ',', ' ') ?>€</div>
        <div class="admin-stat-card__label">Revenus mensuels</div>
    </div>
</div>

<div class="admin-table-wrapper">
    <form action="/admin/subscriptions" method="GET" class="admin-table-filters">
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
            <option value="">Souscription : Tous</option>
            <option value="week" <?= $filters['registered'] === 'week' ? 'selected' : '' ?>>Cette semaine</option>
            <option value="month" <?= $filters['registered'] === 'month' ? 'selected' : '' ?>>Ce mois-ci</option>
        </select>

        <select name="plan" onchange="this.form.submit()">
            <option value="" <?= $filters['plan'] === '' ? 'selected' : '' ?>>Plan : Tous</option>
            <option value="Commission" <?= $filters['plan'] === 'Commission' ? 'selected' : '' ?>>Commission (gratuit)</option>
            <option value="Essentiel" <?= $filters['plan'] === 'Essentiel' ? 'selected' : '' ?>>Essentiel</option>
            <option value="Pro" <?= $filters['plan'] === 'Pro' ? 'selected' : '' ?>>Pro</option>
        </select>
    </form>

    <?php if (empty($subscriptions)): ?>
        <p class="admin-panel__empty" style="padding: 1.5rem;">Aucun abonnement ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="admin-table-scroll">
            <table class="admin-table" data-bulk-table>
                <thead>
                    <tr>
                        <th><input type="checkbox" data-select-all aria-label="Tout sélectionner"></th>
                        <th>Abonné</th>
                        <th>Email</th>
                        <th>Plan</th>
                        <th>Statut</th>
                        <th>Début</th>
                        <th>Prochain paiement</th>
                        <th>Montant</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptions as $sub): ?>
                        <?php
                        $isFree = $sub['plan_name'] === 'Commission';
                        $isExpired = !$isFree && $sub['status'] === 'active' && strtotime($sub['current_period_end']) <= time();
                        $statusKey = $isExpired ? 'cancelled' : $sub['status'];
                        $statusInfo = $statusLabels[$statusKey] ?? ['label' => $sub['status'], 'class' => 'badge--neutral'];
                        ?>
                        <tr>
                            <td><input type="checkbox" class="js-row-select" value="<?= (int) $sub['id'] ?>" aria-label="Sélectionner l'abonnement de <?= htmlspecialchars($sub['shop_name']) ?>"></td>
                            <td><?= htmlspecialchars($sub['shop_name']) ?></td>
                            <td><a href="mailto:<?= htmlspecialchars($sub['email']) ?>"><?= htmlspecialchars($sub['email']) ?></a></td>
                            <td><?= htmlspecialchars($sub['plan_name']) ?></td>
                            <td><span class="badge <?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $sub['current_period_start']) ?></td>
                            <td><?= $isFree ? '—' : \App\Core\FrenchDate::format('d MMM y', $sub['current_period_end']) ?></td>
                            <td><?= $isFree ? 'Gratuit' : number_format($sub['plan_price'] / 100, 2, ',', ' ') . '€/mois' ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="/boutiques/<?= htmlspecialchars($sub['shop_slug']) ?>" title="Voir la boutique">
                                        <img src="/assets/images/icones/voir.png" alt="Voir">
                                    </a>
                                    <a href="#" aria-disabled="true" title="Modifier (bientôt disponible)">
                                        <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                    </a>
                                    <a href="#" aria-disabled="true" title="Annulation non disponible depuis l'admin pour le moment">
                                        <img src="/assets/images/icones/supprimer.png" alt="Annuler">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php $entityLabel = 'abonnements'; ?>
    <?php require __DIR__ . '/../components/pagination.php'; ?>
</div>

<script src="/assets/js/admin-table-select.js"></script>
