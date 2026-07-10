<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::shops()).
 *
 * @var array $shops
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, status: string, registered: string} $filters
 * @var array $stats
 * @var array<int, int|string> $pageNumbers
 */

$totalPages = max(1, (int) ceil($total / $perPage));
$rangeStart = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$rangeEnd = min($total, $page * $perPage);

$queryWithout = function (array $overrides = []) use ($filters) {
    $params = array_merge(
        array_diff_key($filters, array_flip(['page', 'per_page'])),
        $overrides
    );
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '/admin/shops' . ($params !== [] ? '?' . http_build_query($params) : '');
};
?>

<div class="admin-stats">
    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/artiste.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Boutiques créées</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/artiste.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['pending'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Boutiques fermées</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/artiste.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['active'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Boutiques ouvertes</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['suspended'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Boutiques suspendues</div>
    </div>
</div>

<div class="admin-table-wrapper">
    <form action="/admin/shops" method="GET" class="admin-table-filters">
        <?php
        $searchStandalone = false;
        $searchValue = $filters['q'];
        ?>
        <?php require __DIR__ . '/../components/search-bar.php'; ?>

        <select name="status" onchange="this.form.submit()">
            <option value="">Statuts : Tous</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Ouverte</option>
            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Fermée</option>
            <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspendue</option>
        </select>

        <select name="registered" onchange="this.form.submit()">
            <option value="">Création : Tous</option>
            <option value="week" <?= $filters['registered'] === 'week' ? 'selected' : '' ?>>Cette semaine</option>
            <option value="month" <?= $filters['registered'] === 'month' ? 'selected' : '' ?>>Ce mois-ci</option>
        </select>
    </form>

    <?php if (empty($shops)): ?>
        <p class="admin-panel__empty" style="padding: 1.5rem;">Aucune boutique ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="admin-card-grid">
            <?php foreach ($shops as $shop): ?>
                <?php
                $styles = json_decode($shop['styles'] ?? '[]', true) ?: [];
                $rating = $shop['avg_rating'] !== null ? number_format((float) $shop['avg_rating'], 1) : null;
                $planLabel = $shop['plan_name'] ?? ($shop['monetization_type'] === 'commission' ? 'Commission' : 'Sans abonnement');
                ?>
                <div class="admin-shop-card">
                    <div class="admin-shop-card__cover">
                        <?php if (!empty($shop['banner'])): ?>
                            <img class="admin-shop-card__banner" src="/uploads/banners/<?= htmlspecialchars($shop['banner']) ?>" alt="">
                        <?php endif; ?>

                        <span class="admin-shop-card__status">
                            <?php if ($shop['is_banned']): ?>
                                <span class="badge badge--danger">Suspendue</span>
                            <?php elseif ($shop['is_open']): ?>
                                <span class="badge badge--success">Ouverte</span>
                            <?php else: ?>
                                <span class="badge badge--warning">Fermée</span>
                            <?php endif; ?>
                        </span>

                        <?php if (!empty($shop['avatar'])): ?>
                            <img class="admin-shop-card__avatar" src="/uploads/avatars/<?= htmlspecialchars($shop['avatar']) ?>" alt="">
                        <?php else: ?>
                            <img class="admin-shop-card__avatar" src="/assets/images/icones/new-user.png" alt="">
                        <?php endif; ?>
                    </div>

                    <div class="admin-shop-card__body">
                        <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="admin-shop-card__name"><?= htmlspecialchars($shop['name']) ?></a>
                        <div class="admin-shop-card__plan">Par <?= htmlspecialchars($shop['username']) ?> · Abonnement <?= htmlspecialchars($planLabel) ?></div>

                        <?php if (!empty($styles)): ?>
                            <div class="admin-shop-card__tags">
                                <?php foreach ($styles as $style): ?>
                                    <span class="badge badge--neutral"><?= htmlspecialchars(ucfirst($style)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="admin-shop-card__stats">
                            <span title="Commandes">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                    <path d="M3 6h18"></path>
                                </svg>
                                <?= (int) $shop['order_count'] ?> commande<?= $shop['order_count'] > 1 ? 's' : '' ?>
                            </span>

                            <?php if ($rating !== null): ?>
                                <span title="Note moyenne">
                                    <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>
                                    </svg>
                                    <?= $rating ?> (<?= (int) $shop['review_count'] ?>)
                                </span>
                            <?php endif; ?>

                            <span title="Favoris">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
                                </svg>
                                <?= (int) $shop['favorite_count'] ?>
                            </span>
                        </div>

                        <div class="admin-shop-card__actions">
                            <div class="table-actions">
                                <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" title="Voir la boutique">
                                    <img src="/assets/images/icones/voir.png" alt="Voir">
                                </a>

                                <details class="action-popover">
                                    <summary title="<?= $shop['is_open'] ? 'Désactiver' : 'Activer' ?> la boutique">
                                        <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                    </summary>
                                    <div class="action-popover__panel">
                                        <form method="POST" action="/admin/shops/<?= $shop['id'] ?>/toggle">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                            <label>Statut de la boutique</label>
                                            <button type="submit" class="btn btn--outline" style="width: 100%;">
                                                <?= $shop['is_open'] ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </form>
                                    </div>
                                </details>

                                <form method="POST" action="/admin/shops/<?= $shop['id'] ?>/delete">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                    <button
                                        type="submit"
                                        class="danger"
                                        title="Supprimer"
                                        onclick="return confirm('Supprimer la boutique <?= htmlspecialchars($shop['name']) ?> et tout son contenu ?');">
                                        <img src="/assets/images/icones/supprimer.png" alt="Supprimer">
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php $entityLabel = 'boutiques'; ?>
    <?php require __DIR__ . '/../components/pagination.php'; ?>
</div>
