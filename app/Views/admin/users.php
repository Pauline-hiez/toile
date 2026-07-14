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
    'user' => ['label' => 'Utilisateur', 'class' => \App\Core\Badge::classes('neutral')],
    'artist' => ['label' => 'Artiste', 'class' => \App\Core\Badge::classes('success')],
    'admin' => ['label' => 'Admin', 'class' => \App\Core\Badge::classes('info')],
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

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/users.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Utilisateurs</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/users.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['active'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Utilisateurs actifs</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/users.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['new_this_week'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Nouveaux inscrits</div>
        <div class="text-[0.75rem] text-success">↗ <?= $stats['new_vs_prev_week'] >= 0 ? '+' : '' ?><?= $stats['new_vs_prev_week'] ?> vs semaine dernière</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/artiste.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['artists'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Artistes</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_artists_this_week'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/avertissements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['suspended'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Suspendus</div>
    </div>
</div>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <form action="/admin/users" method="GET" class="p-5 border-b border-border flex items-center gap-3 flex-wrap [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-4 [&_select]:py-[0.4rem] [&_select]:text-[0.85rem] [&_select]:outline-none [&_select]:bg-bg [&_select]:font-main">
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
        <p class="text-muted text-[0.85rem] text-center p-6">Aucun utilisateur ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2] [&_input]:w-4 [&_input]:h-4 [&_input]:accent-primary [&_input]:cursor-pointer" data-bulk-table data-bulk-form="usersBulkForm">
                <thead>
                    <tr>
                        <th>
                            <span class="inline-flex items-center gap-2">
                                <input type="checkbox" data-select-all aria-label="Tout sélectionner">
                                <button type="submit" form="usersBulkForm" class="inline-flex items-center bg-transparent border-0 cursor-pointer p-1 rounded-sm text-muted transition-colors hover:text-primary hover:bg-primary-light [&_svg]:w-4 [&_svg]:h-4" data-bulk-trigger hidden title="Suspendre la sélection">
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
                        $roleInfo = $roleLabels[$user['role']] ?? ['label' => $user['role'], 'class' => \App\Core\Badge::classes('neutral')];
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
                                        <img class="w-9 h-9 rounded-full object-cover border border-border" src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="">
                                    <?php else: ?>
                                        <img class="w-9 h-9 rounded-full object-cover border border-border" src="/assets/images/icones/new-user.png" alt="">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($user['username']) ?>
                                </div>
                            </td>
                            <td><a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a></td>
                            <td><span class="<?= $roleInfo['class'] ?>"><?= htmlspecialchars($roleInfo['label']) ?></span></td>
                            <td>
                                <?php if ($user['is_banned']): ?>
                                    <span class="<?= \App\Core\Badge::classes('danger') ?>">Suspendu</span>
                                <?php else: ?>
                                    <span class="<?= \App\Core\Badge::classes('success') ?>">Actif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $user['created_at']) ?></td>
                            <td>—</td>
                            <td>
                                <div class="flex items-center gap-2 [&_a]:bg-transparent [&_a]:border-0 [&_a]:cursor-pointer [&_a]:p-1 [&_a]:rounded-sm [&_a]:text-muted [&_a]:transition-colors [&_a]:flex [&_a]:items-center [&_a]:no-underline [&_a:hover]:text-primary [&_a:hover]:bg-primary-light [&_button]:bg-transparent [&_button]:border-0 [&_button]:cursor-pointer [&_button]:p-1 [&_button]:rounded-sm [&_button]:text-muted [&_button]:transition-colors [&_button]:flex [&_button]:items-center [&_button:hover]:text-primary [&_button:hover]:bg-primary-light [&_button.danger:hover]:text-danger [&_button.danger:hover]:bg-danger-bg [&_svg]:w-4 [&_svg]:h-4 [&_img]:w-4 [&_img]:h-4 [&_img]:object-contain">
                                    <?php if ($shopSlug): ?>
                                        <a href="/boutiques/<?= htmlspecialchars($shopSlug) ?>" title="Voir la boutique">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php else: ?>
                                        <a href="#" aria-disabled="true" class="opacity-35 cursor-not-allowed pointer-events-none" title="Aucune boutique">
                                            <img src="/assets/images/icones/voir.png" alt="Voir">
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!$isSelf): ?>
                                        <details class="relative inline-flex">
                                            <summary class="list-none cursor-pointer p-1 rounded-sm text-muted flex items-center transition-colors hover:text-primary hover:bg-primary-light [&::-webkit-details-marker]:hidden" title="Modifier le rôle">
                                                <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                            </summary>
                                            <div class="absolute top-full right-0 mt-[0.4rem] bg-white border border-border rounded-md shadow-sm p-3 min-w-[170px] z-30 [&_label]:block [&_label]:text-[0.75rem] [&_label]:text-muted [&_label]:mb-[0.35rem] [&_select]:w-full [&_select]:border [&_select]:border-border [&_select]:rounded-sm [&_select]:px-2 [&_select]:py-[0.35rem] [&_select]:text-[0.85rem] [&_select]:font-main [&_select]:bg-bg">
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
                                        <a href="#" aria-disabled="true" class="opacity-35 cursor-not-allowed pointer-events-none" title="Modifier">
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
                                        <a href="#" aria-disabled="true" class="opacity-35 cursor-not-allowed pointer-events-none" title="Suspendre">
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
