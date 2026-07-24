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
 * @var array $plans Paliers disponibles (voir SubscriptionPlan::findAll()), pour le formulaire de changement de palier.
 */

$statusLabels = [
    'active' => ['label' => 'En cours', 'class' => \App\Core\Badge::classes('success')],
    'past_due' => ['label' => 'En attente', 'class' => \App\Core\Badge::classes('warning')],
    'cancelled' => ['label' => 'Expiré', 'class' => \App\Core\Badge::classes('danger')],
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

<div class="grid grid-cols-2 min-[481px]:grid-cols-[repeat(auto-fit,minmax(140px,1fr))] min-[721px]:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-4 mb-8">
    <a href="/admin/subscriptions?plan=Commission" class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm no-underline text-inherit max-w-[220px] transition-colors hover:border-primary" title="Voir les boutiques sur la formule gratuite">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/abonnements.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= number_format($stats['commission'], 0, ',', ' ') ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Formule Commission</div>
    </a>

    <a href="/admin/subscriptions?plan=Essentiel" class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm no-underline text-inherit max-w-[220px] transition-colors hover:border-primary" title="Voir les boutiques en formule Essentiel">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/abonnements.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= number_format($stats['essentiel'], 0, ',', ' ') ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Formule Essentiel</div>
    </a>

    <a href="/admin/subscriptions?plan=Pro" class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm no-underline text-inherit max-w-[220px] transition-colors hover:border-primary" title="Voir les boutiques en formule Pro">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/choisir-abonnement.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= number_format($stats['pro'], 0, ',', ' ') ?></div>
        <div class="font-cursive text-[0.9rem] text-success">Formule Pro</div>
    </a>

    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm no-underline text-inherit max-w-[220px] transition-colors hover:border-primary">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/abonnement-attente.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= number_format($stats['pending_choice'], 0, ',', ' ') ?></div>
        <div class="font-cursive text-[0.9rem] text-success">En attente de choix</div>
        <div class="text-[0.75rem] text-success">Boutiques pas encore ouvertes</div>
    </div>

    <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm no-underline text-inherit max-w-[220px] transition-colors hover:border-primary">
        <img class="w-20 h-20 object-contain mx-auto" src="/assets/images/icones/commissions.png" alt="">
        <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= number_format($stats['mrr'] / 100, 2, ',', ' ') ?>€</div>
        <div class="font-cursive text-[0.9rem] text-success">Revenus mensuels</div>
    </div>
</div>

<div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
    <form action="/admin/subscriptions" method="GET" class="p-5 border-b border-border flex items-center gap-3 flex-wrap [&_select]:border [&_select]:border-border [&_select]:rounded-full [&_select]:px-4 [&_select]:py-[0.4rem] [&_select]:text-[0.85rem] [&_select]:outline-none [&_select]:bg-bg [&_select]:font-main">
        <?php
        $searchStandalone = false;
        $searchValue = $filters['q'];
        $searchAutocompleteUrl = '/admin/subscriptions/autocomplete';
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
        <p class="text-muted text-[0.85rem] text-center p-6">Aucun abonnement ne correspond à ces filtres.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="data-table max-[720px]:min-w-[640px] [&_input]:w-4 [&_input]:h-4 [&_input]:accent-primary [&_input]:cursor-pointer" data-bulk-table>
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
                        $statusInfo = $statusLabels[$statusKey] ?? ['label' => $sub['status'], 'class' => \App\Core\Badge::classes('neutral')];
                        ?>
                        <tr>
                            <td><input type="checkbox" class="js-row-select" value="<?= (int) $sub['id'] ?>" aria-label="Sélectionner l'abonnement de <?= htmlspecialchars($sub['shop_name']) ?>"></td>
                            <td><?= htmlspecialchars($sub['shop_name']) ?></td>
                            <td><a href="mailto:<?= htmlspecialchars($sub['email']) ?>"><?= htmlspecialchars($sub['email']) ?></a></td>
                            <td><?= htmlspecialchars($sub['plan_name']) ?></td>
                            <td><span class="<?= $statusInfo['class'] ?>"><?= htmlspecialchars($statusInfo['label']) ?></span></td>
                            <td><?= \App\Core\FrenchDate::format('d MMM y', $sub['current_period_start']) ?></td>
                            <td><?= $isFree ? '—' : \App\Core\FrenchDate::format('d MMM y', $sub['current_period_end']) ?></td>
                            <td><?= $isFree ? 'Gratuit' : number_format($sub['plan_price'] / 100, 2, ',', ' ') . '€/mois' ?></td>
                            <td>
                                <div class="flex items-center gap-2 [&_a]:bg-transparent [&_a]:border-0 [&_a]:cursor-pointer [&_a]:p-1 [&_a]:rounded-sm [&_a]:text-muted [&_a]:transition-colors [&_a]:flex [&_a]:items-center [&_a]:no-underline [&_a:hover]:text-primary [&_a:hover]:bg-primary-light [&_button]:bg-transparent [&_button]:border-0 [&_button]:cursor-pointer [&_button]:p-1 [&_button]:rounded-sm [&_button]:text-muted [&_button]:transition-colors [&_button]:flex [&_button]:items-center [&_button:hover]:text-primary [&_button:hover]:bg-primary-light [&_button.danger:hover]:text-danger [&_button.danger:hover]:bg-danger-bg [&_svg]:w-4 [&_svg]:h-4 [&_img]:w-4 [&_img]:h-4 [&_img]:object-contain">
                                    <a href="/boutiques/<?= htmlspecialchars($sub['shop_slug']) ?>" title="Voir la boutique">
                                        <img src="/assets/images/icones/voir.png" alt="Voir">
                                    </a>
                                    <a href="/admin/subscriptions/<?= (int) $sub['id'] ?>/invoices" title="Factures">
                                        <img src="/assets/images/icones/paiement.png" alt="Factures">
                                    </a>
                                    <button type="button"
                                        data-plan-modal-open
                                        data-subscription-id="<?= (int) $sub['id'] ?>"
                                        data-shop-name="<?= htmlspecialchars($sub['shop_name']) ?>"
                                        data-current-plan-id="<?= (int) $sub['plan_id'] ?>"
                                        title="Modifier le palier">
                                        <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                    </button>
                                    <?php if ($isFree): ?>
                                        <a href="#" aria-disabled="true" class="opacity-35 cursor-not-allowed pointer-events-none" title="Déjà sur le palier gratuit">
                                            <img src="/assets/images/icones/supprimer.png" alt="Annuler">
                                        </a>
                                    <?php else: ?>
                                        <form method="POST" action="/admin/subscriptions/<?= (int) $sub['id'] ?>/cancel">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                            <button type="submit" class="danger"
                                                onclick="return confirm('Annuler l\'abonnement de <?= htmlspecialchars(addslashes($sub['shop_name'])) ?> et repasser sur le palier gratuit Commission ?');"
                                                title="Annuler l'abonnement">
                                                <img src="/assets/images/icones/supprimer.png" alt="Annuler">
                                            </button>
                                        </form>
                                    <?php endif; ?>
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

<dialog id="planChangeModal" class="auth-modal">
    <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-plan-modal-close aria-label="Fermer">&times;</button>

    <div class="relative bg-bg rounded-2xl border border-border shadow-lg px-6 py-8 min-[481px]:px-10 min-[481px]:py-10 max-w-[420px]">
        <h2 class="font-title text-[1.6rem] min-[481px]:text-[1.9rem] text-title font-semibold text-center leading-none mb-2">Changer de palier</h2>
        <p class="text-[0.85rem] text-muted text-center mb-5">
            Bascule <strong id="planModalShopName"></strong> vers un autre palier, sans passer par un paiement Stripe — utile pour une correction ou un geste commercial.
        </p>

        <form id="planChangeForm" method="POST" class="flex flex-col gap-4 max-w-[300px] mx-auto">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <div>
                <label for="planModalSelect" class="block font-semibold text-[0.9rem] mb-2">Palier</label>
                <select id="planModalSelect" name="plan_id" class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= (int) $plan['id'] ?>"><?= htmlspecialchars($plan['name']) ?> — <?= $plan['price'] > 0 ? number_format($plan['price'] / 100, 2, ',', ' ') . '€/mois' : 'Gratuit' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="text-center mt-1">
                <button type="submit" class="btn btn--primary px-10">Valider</button>
            </div>
        </form>
    </div>
</dialog>

<script src="/assets/js/admin-table-select.js"></script>
<script>
    (function () {
        var dialog = document.getElementById('planChangeModal');
        var form = document.getElementById('planChangeForm');
        var shopNameLabel = document.getElementById('planModalShopName');
        var select = document.getElementById('planModalSelect');

        document.querySelectorAll('[data-plan-modal-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                form.action = '/admin/subscriptions/' + trigger.dataset.subscriptionId + '/plan';
                shopNameLabel.textContent = trigger.dataset.shopName;
                select.value = trigger.dataset.currentPlanId;
                dialog.showModal();
            });
        });

        dialog.querySelector('[data-plan-modal-close]').addEventListener('click', function () {
            dialog.close();
        });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                dialog.close();
            }
        });
    })();
</script>
