<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::users()).
 *
 * @var array $users
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, role: string, status: string, registered: string} $filters
 * @var array<int, string> $shopSlugsByUserId
 * @var array $stats
 * @var array<int, int|string> $pageNumbers
 */

$roleLabels = [
    'user' => ['label' => 'Utilisateur', 'class' => 'badge--neutral'],
    'artist' => ['label' => 'Artiste', 'class' => 'badge--success'],
    'admin' => ['label' => 'Admin', 'class' => 'badge--info'],
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
    return '/admin/users' . ($params !== [] ? '?' . http_build_query($params) : '');
};
?>

<div class="admin-stats">
    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/users.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Utilisateurs</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/users.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['active'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Utilisateurs actifs</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/users.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['new_this_week'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Nouveaux inscrits</div>
        <div class="admin-stat-card__trend">↗ <?= $stats['new_vs_prev_week'] >= 0 ? '+' : '' ?><?= $stats['new_vs_prev_week'] ?> vs semaine dernière</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/artiste.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['artists'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Artistes</div>
        <div class="admin-stat-card__trend">↗ +<?= $stats['new_artists_this_week'] ?> cette semaine</div>
    </div>

    <div class="admin-stat-card">
        <img class="admin-stat-card__icon" src="/assets/images/icones/avertissements.png" alt="">
        <div class="admin-stat-card__value"><?= number_format($stats['suspended'], 0, ',', ' ') ?></div>
        <div class="admin-stat-card__label">Suspendus</div>
    </div>
</div>

<div class="admin-table-wrapper">
    <form action="/admin/users" method="GET" class="admin-table-filters">
        <?php
        $searchStandalone = false;
        $searchValue = $filters['q'];
        ?>
        <?php require __DIR__ . '/../components/search-bar.php'; ?>

        <select name="role" onchange="this.form.submit()">
            <option value="">Rôle : Tous</option>
            <option value="user" <?= $filters['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
            <option value="artist" <?= $filters['role'] === 'artist' ? 'selected' : '' ?>>Artiste</option>
            <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>

        <select name="status" onchange="this.form.submit()">
            <option value="">Statuts : Tous</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Actif</option>
            <option value="banned" <?= $filters['status'] === 'banned' ? 'selected' : '' ?>>Suspendu</option>
        </select>

        <select name="registered" onchange="this.form.submit()">
            <option value="">Inscription : Tous</option>
            <option value="week" <?= $filters['registered'] === 'week' ? 'selected' : '' ?>>Cette semaine</option>
            <option value="month" <?= $filters['registered'] === 'month' ? 'selected' : '' ?>>Ce mois-ci</option>
        </select>
    </form>

    <form method="POST" action="/admin/users/bulk-ban" id="usersBulkForm" data-bulk-form data-confirm="Suspendre les %d utilisateur(s) sélectionné(s) ?" hidden>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
    </form>

    <?php if (empty($users)): ?>
        <p class="admin-panel__empty" style="padding: 1.5rem;">Aucun utilisateur ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="admin-table-scroll">
            <table class="admin-table" data-bulk-table data-bulk-form="usersBulkForm">
                <thead>
                    <tr>
                        <th>
                            <span class="admin-th-select">
                                <input type="checkbox" data-select-all aria-label="Tout sélectionner">
                                <button type="submit" form="usersBulkForm" class="admin-bulk-trigger" data-bulk-trigger hidden title="Suspendre la sélection">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <line x1="5.5" y1="5.5" x2="18.5" y2="18.5"></line>
                                    </svg>
                                </button>
                            </span>
                        </th>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Inscription</th>
                        <th>Dernière connexion</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $roleInfo = $roleLabels[$user['role']] ?? ['label' => $user['role'], 'class' => 'badge--neutral'];
                        $isSelf = $user['id'] === $_SESSION['user_id'];
                        $shopSlug = $shopSlugsByUserId[$user['id']] ?? null;
                        ?>
                        <tr>
                            <td>
                                <?php if (!$isSelf && $user['role'] !== 'admin'): ?>
                                    <input type="checkbox" class="js-row-select" value="<?= $user['id'] ?>" aria-label="Sélectionner <?= htmlspecialchars($user['username']) ?>">
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <?php if (!empty($user['avatar'])): ?>
                                        <img class="admin-table-avatar" src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <img class="admin-table-avatar" src="/assets/images/icones/new-user.png" alt="">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($user['username']) ?>
                                </div>
                            </td>
                            <td><a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a></td>
                            <td><span class="badge <?= $roleInfo['class'] ?>"><?= htmlspecialchars($roleInfo['label']) ?></span></td>
                            <td>
                                <?php if ($user['is_banned']): ?>
                                    <span class="badge badge--danger">Suspendu</span>
                                <?php else: ?>
                                    <span class="badge badge--success">Actif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td>—</td>
                            <td>
                                <div class="table-actions">
                                    <?php if ($shopSlug): ?>
                                        <a href="/boutiques/<?= htmlspecialchars($shopSlug) ?>" title="Voir la boutique">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php else: ?>
                                        <a href="#" aria-disabled="true" title="Aucune boutique">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!$isSelf): ?>
                                        <details class="action-popover">
                                            <summary title="Modifier le rôle">
                                                <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                            </summary>
                                            <div class="action-popover__panel">
                                                <form method="POST" action="/admin/users/<?= $user['id'] ?>/role">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                                    <label for="role-<?= $user['id'] ?>">Changer le rôle</label>
                                                    <select id="role-<?= $user['id'] ?>" name="role" onchange="this.form.submit()">
                                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                                        <option value="artist" <?= $user['role'] === 'artist' ? 'selected' : '' ?>>Artiste</option>
                                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </details>
                                    <?php else: ?>
                                        <a href="#" aria-disabled="true" title="Modifier">
                                            <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!$isSelf && $user['role'] !== 'admin'): ?>
                                        <?php if ($user['is_banned']): ?>
                                            <form method="POST" action="/admin/users/<?= $user['id'] ?>/unban">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                                <button type="submit" title="Réactiver">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="/admin/users/<?= $user['id'] ?>/ban">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                                <button type="submit" class="danger" title="Suspendre" onclick="return confirm('Suspendre <?= htmlspecialchars($user['username']) ?> ?');">
                                                    <img src="/assets/images/icones/supprimer.png" alt="Suspendre">
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="#" aria-disabled="true" title="Suspendre">
                                            <img src="/assets/images/icones/supprimer.png" alt="Suspendre">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php $entityLabel = 'utilisateurs'; ?>
    <?php require __DIR__ . '/../components/pagination.php'; ?>
</div>

<script src="/assets/js/admin-table-select.js"></script>
