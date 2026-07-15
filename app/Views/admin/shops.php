<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::shops()).
 *
 * @var array $shops
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var array{q: string, status: string, registered: string} $filters status: 'active'|'pending'|'closed'|'suspended'
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

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/artiste.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['total'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Boutiques créées</div>
        <div class="text-[0.75rem] text-success">↗ +<?= $stats['new_this_week'] ?> cette semaine</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/artiste.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['pending'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">En attente de choix</div>
        <div class="text-[0.75rem] text-success">Formule d'abonnement pas encore choisie</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/artiste.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['active'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Boutiques ouvertes</div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 flex flex-col gap-[0.4rem] shadow-sm no-underline text-inherit transition-[border-color,box-shadow] duration-150 hover:border-primary hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)]">
        <img class="w-[52px] h-[52px] object-contain" src="/assets/images/icones/avertissements.png" alt="">
        <div class="text-[1.75rem] font-bold text-primary leading-none"><?= number_format($stats['suspended'], 0, ',', ' ') ?></div>
        <div class="text-[0.8rem] text-muted font-medium">Boutiques suspendues</div>
    </div>
</div>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <form action="/admin/shops" method="GET" class="p-5 border-b border-border flex items-center gap-3 flex-wrap [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-4 [&_select]:py-[0.4rem] [&_select]:text-[0.85rem] [&_select]:outline-none [&_select]:bg-bg [&_select]:font-main">
        <?php
        $searchStandalone = false;
        $searchValue = $filters['q'];
        ?>
        <?php require __DIR__ . '/../components/search-bar.php'; ?>

        <select name="status" onchange="this.form.submit()">
            <option value="">Statuts : Tous</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Ouverte</option>
            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>En attente de choix</option>
            <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Fermée temporairement</option>
            <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspendue</option>
        </select>

        <select name="registered" onchange="this.form.submit()">
            <option value="">Création : Tous</option>
            <option value="week" <?= $filters['registered'] === 'week' ? 'selected' : '' ?>>Cette semaine</option>
            <option value="month" <?= $filters['registered'] === 'month' ? 'selected' : '' ?>>Ce mois-ci</option>
        </select>
    </form>

    <?php if (empty($shops)): ?>
        <p class="text-muted text-[0.85rem] text-center p-6">Aucune boutique ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-5 p-5">
            <?php foreach ($shops as $shop): ?>
                <?php
                $styles = json_decode($shop['styles'] ?? '[]', true) ?: [];
                $rating = $shop['avg_rating'] !== null ? number_format((float) $shop['avg_rating'], 1) : null;
                $planLabel = $shop['plan_selected'] ? ($shop['plan_name'] ?? 'Commission') : null;
                ?>
                <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm flex flex-col">
                    <div class="relative h-[90px] bg-primary-light">
                        <?php if (!empty($shop['banner'])): ?>
                            <img class="w-full h-full object-cover block" src="/uploads/banners/<?= htmlspecialchars($shop['banner']) ?>" alt="">
                        <?php endif; ?>

                        <span class="absolute top-2 left-2">
                            <?php if ($shop['is_banned']): ?>
                                <span class="<?= \App\Core\Badge::classes('danger') ?>">Suspendue</span>
                            <?php elseif ($shop['is_open']): ?>
                                <span class="<?= \App\Core\Badge::classes('success') ?>">Ouverte</span>
                            <?php elseif (!$shop['plan_selected']): ?>
                                <span class="<?= \App\Core\Badge::classes('warning') ?>">En attente de choix</span>
                            <?php else: ?>
                                <span class="<?= \App\Core\Badge::classes('warning') ?>">Fermée</span>
                            <?php endif; ?>
                        </span>

                        <?php if (!empty($shop['avatar'])): ?>
                            <img class="absolute left-[0.9rem] bottom-[-22px] w-[52px] h-[52px] rounded-full border-[3px] border-white object-cover bg-bg" src="/uploads/avatars/<?= htmlspecialchars($shop['avatar']) ?>" alt="">
                        <?php else: ?>
                            <img class="absolute left-[0.9rem] bottom-[-22px] w-[52px] h-[52px] rounded-full border-[3px] border-white object-cover bg-bg" src="/assets/images/icones/new-user.png" alt="">
                        <?php endif; ?>
                    </div>

                    <div class="pt-7 px-[0.9rem] pb-[0.9rem] flex-1 flex flex-col">
                        <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="font-semibold text-[0.95rem] text-ink no-underline block"><?= htmlspecialchars($shop['name']) ?></a>
                        <div class="text-[0.75rem] text-muted mb-[0.65rem]">
                            Par <?= htmlspecialchars($shop['username']) ?>
                            · <?= $planLabel !== null ? 'Abonnement ' . htmlspecialchars($planLabel) : 'Aucune formule choisie' ?>
                        </div>

                        <?php if (!empty($styles)): ?>
                            <div class="flex flex-wrap gap-[0.35rem] mb-3">
                                <?php foreach ($styles as $style): ?>
                                    <span class="<?= \App\Core\Badge::classes('neutral') ?>"><?= htmlspecialchars(ucfirst($style)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-[0.9rem] text-[0.8rem] text-muted mb-3">
                            <span title="Commandes" class="inline-flex items-center gap-[0.3rem]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-[14px] h-[14px]">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                    <path d="M3 6h18"></path>
                                </svg>
                                <?= (int) $shop['order_count'] ?> commande<?= $shop['order_count'] > 1 ? 's' : '' ?>
                            </span>

                            <?php if ($rating !== null): ?>
                                <span title="Note moyenne" class="inline-flex items-center gap-[0.3rem]">
                                    <svg viewBox="0 0 24 24" fill="currentColor" stroke="none" class="w-[14px] h-[14px]">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>
                                    </svg>
                                    <?= $rating ?> (<?= (int) $shop['review_count'] ?>)
                                </span>
                            <?php endif; ?>

                            <span title="Favoris" class="inline-flex items-center gap-[0.3rem]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-[14px] h-[14px]">
                                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
                                </svg>
                                <?= (int) $shop['favorite_count'] ?>
                            </span>
                        </div>

                        <div class="mt-auto pt-[0.65rem] border-t border-border">
                            <div class="flex items-center gap-2 [&_a]:bg-transparent [&_a]:border-0 [&_a]:cursor-pointer [&_a]:p-1 [&_a]:rounded-sm [&_a]:text-muted [&_a]:transition-colors [&_a]:flex [&_a]:items-center [&_a]:no-underline [&_a:hover]:text-primary [&_a:hover]:bg-primary-light [&_button]:bg-transparent [&_button]:border-0 [&_button]:cursor-pointer [&_button]:p-1 [&_button]:rounded-sm [&_button]:text-muted [&_button]:transition-colors [&_button]:flex [&_button]:items-center [&_button:hover]:text-primary [&_button:hover]:bg-primary-light [&_button.danger:hover]:text-danger [&_button.danger:hover]:bg-danger-bg [&_svg]:w-4 [&_svg]:h-4 [&_img]:w-4 [&_img]:h-4 [&_img]:object-contain">
                                <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" title="Voir la boutique">
                                    <img src="/assets/images/icones/voir.png" alt="Voir">
                                </a>

                                <details class="relative inline-flex">
                                    <summary class="list-none cursor-pointer p-1 rounded-sm text-muted flex items-center transition-colors hover:text-primary hover:bg-primary-light [&::-webkit-details-marker]:hidden" title="<?= $shop['is_open'] ? 'Désactiver' : 'Activer' ?> la boutique">
                                        <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                    </summary>
                                    <div class="absolute top-full right-0 mt-[0.4rem] bg-white border border-border rounded-md shadow-sm p-3 min-w-[170px] z-30 [&_label]:block [&_label]:text-[0.75rem] [&_label]:text-muted [&_label]:mb-[0.35rem] [&_select]:w-full [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-2 [&_select]:py-[0.35rem] [&_select]:text-[0.85rem] [&_select]:font-main [&_select]:bg-bg">
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
