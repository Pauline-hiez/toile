<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ShopController::manage()/save()).
 *
 * @var array|null $shop
 * @var array<string, string> $errors
 * @var string|null $success
 * @var array{average: float, count: int}|null $ratingStats Null si $shop est null.
 * @var int|null $favoriteCount Null si $shop est null.
 * @var array<int, string> $availableStyles Styles fixes + validés par l'admin (voir Shop::getAllStyles()).
 * @var array<int, string> $availableTypes Types fixes + validés par l'admin (voir Shop::getAllTypes()).
 * @var array $categoryRequests Demandes de style/type de cette boutique, toutes statuts confondus.
 * @var string $tab 'infos'|'stats'|'raffle'
 * @var int $days Période des graphiques de l'onglet Statistiques (14|30|90).
 * @var array{labels: array, values: array}|null $revenueChart Revenu net (après commission) par jour — null si $shop est null.
 * @var array{labels: array, values: array}|null $ordersChart Commandes par jour — null si $shop est null.
 * @var array{total_orders: int, net_revenue: int}|null $lifetimeStats Chiffres à vie — null si $shop est null.
 * @var array|null $raffleHistory Tickets de tirage au sort de la boutique (page courante) — null si $shop est null.
 * @var int|null $raffleHistoryTotal
 * @var int $rafflePage
 * @var int $rafflePerPage
 * @var array<int, int|string> $rafflePageNumbers
 */
$pageTitle = 'Ma boutique — Toile';

// Ratio du cadre bannière — pilote à la fois le crop interactif
// (Cropper.js) et l'affichage (classe .shop-banner-frame).
$bannerShapeRatio = '579 / 160';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<?php if ($success !== null): ?>
    <div class="bg-success-bg border border-success/25 text-success rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($shop !== null): ?>
    <nav class="flex items-center gap-2 mt-16 mb-6">
        <a href="/my-shop?tab=infos" class="inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium no-underline transition-colors <?= $tab === 'infos' ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">Ma boutique</a>
        <a href="/my-shop?tab=stats" class="inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium no-underline transition-colors <?= $tab === 'stats' ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">Statistiques</a>
        <a href="/my-shop?tab=raffle" class="inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium no-underline transition-colors <?= $tab === 'raffle' ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">Tirage au sort</a>
        <a href="/my-shop?tab=invoices" class="inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium no-underline transition-colors <?= $tab === 'invoices' ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">Factures</a>
    </nav>
<?php endif; ?>

<?php if ($tab === 'invoices' && $shop !== null): ?>
    <p class="text-[0.85rem] text-muted mb-4">
        Historique des paiements de ton abonnement — la facture de chaque commande est disponible depuis son suivi de commande.
    </p>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <?php if (empty($subscriptionInvoices)): ?>
            <p class="text-muted text-[0.85rem] text-center p-6">Aucune facture d'abonnement pour le moment.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[0.875rem] max-[560px]:min-w-[480px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2]">
                    <thead>
                        <tr>
                            <th>Formule</th>
                            <th>Période</th>
                            <th>Payée le</th>
                            <th>Montant</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptionInvoices as $invoice): ?>
                            <tr>
                                <td><?= htmlspecialchars($invoice['plan_name']) ?></td>
                                <td><?= \App\Core\FrenchDate::format('d MMM y', $invoice['period_start']) ?> — <?= \App\Core\FrenchDate::format('d MMM y', $invoice['period_end']) ?></td>
                                <td><?= \App\Core\FrenchDate::format('d MMM y', $invoice['paid_at']) ?></td>
                                <td><?= number_format($invoice['amount'] / 100, 2, ',', ' ') ?> €</td>
                                <td><a href="/factures/abonnement/<?= (int) $invoice['id'] ?>" class="text-primary font-medium hover:underline">Télécharger (PDF)</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($tab === 'stats' && $shop !== null): ?>
    <?php
    $periods = [14 => '14 jours', 30 => '30 jours', 90 => '90 jours'];
    $revenueTotal = array_sum($revenueChart['values']);
    $ordersTotal = array_sum($ordersChart['values']);
    ?>

    <div class="grid grid-cols-2 min-[721px]:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= number_format($lifetimeStats['net_revenue'] / 100, 2, ',', ' ') ?> €</div>
            <div class="font-cursive text-[0.9rem] text-success">Revenu total</div>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= $lifetimeStats['total_orders'] ?></div>
            <div class="font-cursive text-[0.9rem] text-success">Total commandes</div>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1">
                <?= $ratingStats['count'] > 0 ? number_format($ratingStats['average'], 1) . ' ⭐' : '—' ?>
            </div>
            <div class="font-cursive text-[0.9rem] text-success"><?= $ratingStats['count'] > 0 ? $ratingStats['count'] . ' avis' : "Pas encore d'avis" ?></div>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= $favoriteCount ?></div>
            <div class="font-cursive text-[0.9rem] text-success">Favoris</div>
        </div>
    </div>

    <div class="flex items-center gap-2 mb-6">
        <?php foreach ($periods as $value => $label): ?>
            <a href="/my-shop?tab=stats&days=<?= $value ?>"
                class="inline-flex items-center rounded-full border px-4 py-1 text-[0.85rem] font-medium no-underline transition-colors <?= $days === $value ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <p class="text-[0.8rem] text-muted mb-3">Totaux sur les <?= $days ?> derniers jours : <?= number_format($revenueTotal, 2, ',', ' ') ?> € · <?= $ordersTotal ?> commande(s)</p>

    <div class="grid grid-cols-1 min-[1024px]:grid-cols-2 gap-6 mb-6">
        <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
            <h2 class="text-base font-semibold mb-5 text-ink">Revenu net</h2>
            <div class="relative min-h-[220px]"
                data-admin-chart
                data-labels="<?= htmlspecialchars(json_encode($revenueChart['labels']), ENT_QUOTES) ?>"
                data-values="<?= htmlspecialchars(json_encode($revenueChart['values']), ENT_QUOTES) ?>"
                data-suffix=" €"
                role="img"
                aria-label="Revenu net sur les <?= $days ?> derniers jours"
            ></div>
        </div>

        <div class="bg-white border border-border rounded-md p-6 shadow-sm flex flex-col">
            <h2 class="text-base font-semibold mb-5 text-ink">Commandes</h2>
            <div class="relative min-h-[220px]"
                data-admin-chart
                data-labels="<?= htmlspecialchars(json_encode($ordersChart['labels']), ENT_QUOTES) ?>"
                data-values="<?= htmlspecialchars(json_encode($ordersChart['values']), ENT_QUOTES) ?>"
                data-suffix=" commande(s)"
                role="img"
                aria-label="Commandes sur les <?= $days ?> derniers jours"
            ></div>
        </div>
    </div>

    <script src="/assets/js/admin-chart.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/admin-chart.js') ?>"></script>
<?php endif; ?>

<?php if ($tab === 'raffle' && $shop !== null): ?>
    <?php
    $raffleStatusLabels = [
        'entered' => ['label' => 'En attente du tirage', 'class' => \App\Core\Badge::classes('info')],
        'selected' => ['label' => 'Sélectionné·e !', 'class' => \App\Core\Badge::classes('success')],
        'not_selected' => ['label' => 'Non sélectionné·e', 'class' => \App\Core\Badge::classes('neutral')],
        'cancelled' => ['label' => 'Annulé', 'class' => \App\Core\Badge::classes('danger')],
    ];
    $raffleTypeLabels = ['boutiques' => 'Vitrine boutiques', 'homepage' => "Page d'accueil"];

    $queryWithout = function (array $overrides = []) {
        $params = ['tab' => 'raffle'];
        if (isset($overrides['page'])) {
            $params['raffle_page'] = $overrides['page'];
        }
        return '/my-shop?' . http_build_query($params);
    };
    $total = $raffleHistoryTotal;
    $page = $rafflePage;
    $perPage = $rafflePerPage;
    $totalPages = $raffleTotalPages;
    $pageNumbers = $rafflePageNumbers;
    $rangeStart = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
    $rangeEnd = min($total, $page * $perPage);
    $entityLabel = 'ticket(s)';
    ?>

    <p class="text-[0.85rem] text-muted mb-4">
        Historique de tes participations aux tirages au sort.
        <a href="/raffle" class="text-primary font-medium hover:underline">Participer à un tirage →</a>
    </p>

    <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
        <?php if (empty($raffleHistory)): ?>
            <p class="text-muted text-[0.85rem] text-center p-6">Tu n'as encore participé à aucun tirage.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[0.875rem] max-[560px]:min-w-[480px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2]">
                    <thead>
                        <tr>
                            <th>Tirage</th>
                            <th>Période</th>
                            <th>Acheté le</th>
                            <th>Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($raffleHistory as $ticket): ?>
                            <?php $info = $raffleStatusLabels[$ticket['status']] ?? ['label' => $ticket['status'], 'class' => \App\Core\Badge::classes('neutral')]; ?>
                            <tr>
                                <td><?= htmlspecialchars($raffleTypeLabels[$ticket['type']] ?? $ticket['type']) ?></td>
                                <td><?= htmlspecialchars($ticket['period']) ?></td>
                                <td><?= \App\Core\FrenchDate::format('d MMM y', $ticket['created_at']) ?></td>
                                <td><?= $ticket['amount_paid'] !== null ? number_format($ticket['amount_paid'] / 100, 2, ',', ' ') . ' €' : '—' ?></td>
                                <td><span class="<?= $info['class'] ?>"><?= htmlspecialchars($info['label']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($total > 0): ?>
            <div class="p-4">
                <?php require __DIR__ . '/../components/pagination.php'; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($tab === 'infos'): ?>

<?php if ($shop !== null): ?>
    <div class="grid grid-cols-2 min-[721px]:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1">
                <?= $ratingStats['count'] > 0 ? number_format($ratingStats['average'], 1) . ' ⭐' : '—' ?>
            </div>
            <div class="font-cursive text-[0.9rem] text-success"><?= $ratingStats['count'] > 0 ? $ratingStats['count'] . ' avis' : "Pas encore d'avis" ?></div>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= $favoriteCount ?></div>
            <div class="font-cursive text-[0.9rem] text-success">Favoris</div>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <?php if ($shop['plan_selected']): ?>
                <div class="font-cursive text-[1.7rem] font-bold <?= $shop['is_open'] ? 'text-success' : 'text-muted' ?> leading-none mb-1"><?= $shop['is_open'] ? 'Ouverte' : 'Fermée' ?></div>
                <div class="font-cursive text-[0.9rem] text-success mb-2">Statut</div>
                <form method="POST" action="/my-shop/toggle">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <button type="submit" class="text-[0.8rem] text-primary font-medium bg-transparent border-0 cursor-pointer p-0 hover:underline">
                        <?= $shop['is_open'] ? 'Fermer la boutique →' : 'Ouvrir la boutique →' ?>
                    </button>
                </form>
            <?php else: ?>
                <div class="font-cursive text-[1.7rem] font-bold text-muted leading-none mb-1">—</div>
                <div class="font-cursive text-[0.9rem] text-success mb-2">Statut</div>
                <a href="/my-subscription" class="text-[0.8rem] text-primary font-medium no-underline hover:underline">Choisir ma formule →</a>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold <?= $shop['accepts_quotes'] ? 'text-success' : 'text-muted' ?> leading-none mb-1"><?= $shop['accepts_quotes'] ? 'Activés' : 'Désactivés' ?></div>
            <div class="font-cursive text-[0.9rem] text-success mb-2">Devis</div>
            <form method="POST" action="/my-shop/toggle-quotes">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <button type="submit" class="text-[0.8rem] text-primary font-medium bg-transparent border-0 cursor-pointer p-0 hover:underline">
                    <?= $shop['accepts_quotes'] ? 'Désactiver les devis →' : 'Activer les devis →' ?>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($shop !== null && !$shop['plan_selected']): ?>
    <div class="bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-4 mb-6 text-[0.9rem]">
        <strong>Ta boutique n'est pas encore ouverte.</strong>
        Choisis ta formule d'abonnement (gratuite ou payante) pour l'activer.
        <a href="/my-subscription" class="font-semibold underline">Choisir ma formule →</a>
    </div>
<?php endif; ?>

<?php if ($shop !== null && empty($shop['stripe_payouts_enabled'])): ?>
    <div class="bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-4 mb-6 text-[0.9rem]">
        <strong>Ton compte bancaire n'est pas encore connecté.</strong>
        Connecte-le pour recevoir directement ta part de chaque commande.
        <a href="/my-payouts" class="font-semibold underline">Connecter mon compte →</a>
    </div>
<?php endif; ?>

<?php if ($shop === null): ?>
    <p class="text-[0.85rem] text-muted mb-5">Configure ta boutique pour qu'elle apparaisse sur Toile.</p>
<?php endif; ?>

<form method="POST" action="/my-shop" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

    <div class="grid grid-cols-1 min-[901px]:grid-cols-2 gap-6 mb-6 items-start">
        <div class="bg-white border border-border rounded-md p-6 shadow-sm">
            <h2 class="text-base font-semibold text-ink mb-5">Bannière</h2>

            <img id="bannerPreviewImg"
                src="<?= !empty($shop['banner']) ? '/uploads/banners/' . htmlspecialchars($shop['banner']) : '' ?>"
                alt="Bannière"
                class="w-full aspect-[<?= $bannerShapeRatio ?>] object-cover shop-banner-frame mb-3 <?= empty($shop['banner']) ? 'hidden' : '' ?>">

            <label class="btn btn--primary cursor-pointer inline-block">
                <span id="bannerUploadLabel"><?= !empty($shop['banner']) ? 'Modifier ma bannière' : 'Ajouter une bannière' ?></span>
                <input type="file" id="bannerInput" name="banner" accept="image/jpeg,image/png,image/webp" class="hidden">
            </label>
            <p class="text-[0.8rem] text-muted mt-1">Positionne ton image pour qu'elle s'adapte bien au cadre.</p>
            <?php if (isset($errors['banner'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['banner']) ?></p>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-border rounded-md p-6 shadow-sm">
            <h2 class="text-base font-semibold text-ink mb-5">Informations</h2>

            <div class="flex flex-col gap-5">
                <?php if ($shop !== null): ?>
                    <p class="text-[0.85rem] text-muted -mt-2">URL publique : <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="text-primary hover:underline">/boutiques/<?= htmlspecialchars($shop['slug']) ?></a></p>
                <?php endif; ?>

                <div>
                    <label for="name" class="block font-semibold text-[0.9rem] mb-2">Nom de la boutique</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($shop['name'] ?? '') ?>" required
                        class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                    <?php if (isset($errors['name'])): ?>
                        <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="bio" class="block font-semibold text-[0.9rem] mb-2">Description</label>
                    <textarea id="bio" name="bio" rows="4"
                        class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary resize-y"><?= htmlspecialchars($shop['bio'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-border rounded-md p-6 shadow-sm mb-6">
        <div class="flex items-center gap-2 mb-5">
            <h2 class="text-base font-semibold text-ink">Style & spécialité</h2>
            <?php if ($shop !== null): ?>
                <button type="button" data-category-request-open title="Proposer un nouveau style ou type"
                    class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-base leading-none border-0 cursor-pointer hover:bg-primary/90 transition-colors">+</button>
            <?php endif; ?>
        </div>

        <?php
        $selectedStyles = !empty($shop['styles']) ? json_decode($shop['styles'], true) : [];
        $selectedTypes = !empty($shop['types']) ? json_decode($shop['types'], true) : [];
        ?>

        <div class="mb-5">
            <p class="text-[0.82rem] font-semibold text-ink mb-2">Styles artistiques</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($availableStyles as $style): ?>
                    <label class="inline-flex items-center gap-2 bg-white border border-border rounded-full px-4 py-[0.4rem] text-[0.82rem] text-ink cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                        <input type="checkbox" name="styles[]" value="<?= htmlspecialchars($style) ?>" <?= in_array($style, $selectedStyles, true) ? 'checked' : '' ?> class="accent-primary">
                        <?= htmlspecialchars(ucfirst($style)) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <p class="text-[0.82rem] font-semibold text-ink mb-2">Type / spécialité</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($availableTypes as $type): ?>
                    <label class="inline-flex items-center gap-2 bg-white border border-border rounded-full px-4 py-[0.4rem] text-[0.82rem] text-ink cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                        <input type="checkbox" name="types[]" value="<?= htmlspecialchars($type) ?>" <?= in_array($type, $selectedTypes, true) ? 'checked' : '' ?> class="accent-primary">
                        <?= htmlspecialchars(ucfirst($type)) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="bg-white border border-border rounded-md p-6 shadow-sm mb-6">
        <h2 class="text-base font-semibold text-ink mb-2">Réseaux sociaux</h2>
        <p class="text-[0.8rem] text-muted mb-5">Affichés sur ta page boutique publique, sous le bouton "Ajouter aux favoris". Laisse vide les réseaux que tu n'utilises pas.</p>

        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-5">
            <div>
                <label for="social_instagram" class="block font-semibold text-[0.9rem] mb-2">Instagram</label>
                <input type="url" id="social_instagram" name="social_instagram" value="<?= htmlspecialchars($shop['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/tonpseudo"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="social_facebook" class="block font-semibold text-[0.9rem] mb-2">Facebook</label>
                <input type="url" id="social_facebook" name="social_facebook" value="<?= htmlspecialchars($shop['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/tapage"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="social_pinterest" class="block font-semibold text-[0.9rem] mb-2">Pinterest</label>
                <input type="url" id="social_pinterest" name="social_pinterest" value="<?= htmlspecialchars($shop['social_pinterest'] ?? '') ?>" placeholder="https://pinterest.com/tonpseudo"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="social_tiktok" class="block font-semibold text-[0.9rem] mb-2">TikTok</label>
                <input type="url" id="social_tiktok" name="social_tiktok" value="<?= htmlspecialchars($shop['social_tiktok'] ?? '') ?>" placeholder="https://tiktok.com/@tonpseudo"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>
        </div>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn--primary">Enregistrer</button>
    </div>
</form>

<?php if ($shop !== null): ?>
    <?php require __DIR__ . '/../components/category-request-modal.php'; ?>
    <script src="/assets/js/category-request-modal.js"></script>
<?php endif; ?>

<div id="bannerCropModal" class="hidden fixed inset-0 z-[200] bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-md p-5 max-w-[640px] w-full shadow-sm">
        <h3 class="text-base font-semibold text-ink mb-1">Recadre ta bannière</h3>
        <p class="text-[0.8rem] text-muted mb-4">Déplace et zoome ton image pour qu'elle s'adapte bien au cadre.</p>

        <div id="bannerCropWrapper" class="relative w-full h-[420px] bg-bg overflow-hidden mb-4">
            <img id="bannerCropImage" src="" alt="" class="block max-w-full">
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" id="bannerCropCancel" class="btn btn--outline">Annuler</button>
            <button type="button" id="bannerCropConfirm" class="btn btn--primary">Valider le cadrage</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var fileInput = document.getElementById('bannerInput');
        var modal = document.getElementById('bannerCropModal');
        var cropImage = document.getElementById('bannerCropImage');
        var confirmBtn = document.getElementById('bannerCropConfirm');
        var cancelBtn = document.getElementById('bannerCropCancel');
        var previewImg = document.getElementById('bannerPreviewImg');
        var cropper = null;
        var objectUrl = null;

        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            cropImage.src = objectUrl;
            modal.classList.remove('hidden');

            cropImage.onload = function () {
                if (cropper) cropper.destroy();
                cropper = new Cropper(cropImage, {
                    aspectRatio: 579 / 160,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                    background: false,
                });
            };
        });

        function closeModal() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            modal.classList.add('hidden');
        }

        cancelBtn.addEventListener('click', function () {
            fileInput.value = '';
            closeModal();
        });

        confirmBtn.addEventListener('click', function () {
            if (!cropper) return;

            cropper.getCroppedCanvas({ width: 1158, height: 320 }).toBlob(function (blob) {
                var croppedFile = new File([blob], 'banner.png', { type: 'image/png' });
                var dt = new DataTransfer();
                dt.items.add(croppedFile);
                fileInput.files = dt.files;

                previewImg.src = URL.createObjectURL(blob);
                previewImg.classList.remove('hidden');

                closeModal();
            }, 'image/png');
        });
    })();
</script>
<?php endif; ?>
