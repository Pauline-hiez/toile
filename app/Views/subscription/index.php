<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir SubscriptionController::index()).
 *
 * @var array $plans
 * @var array|null $currentSubscription
 * @var array|null $shop
 */
$pageTitle = 'Choisir mon abonnement — Toile';
$hasChosenPlan = $shop !== null && (bool) $shop['plan_selected'];

$planAssets = [
    'Commission' => ['logo' => 'ab-commission.png', 'desc' => "Idéal pour découvrir et réaliser tes premiers projets."],
    'Essentiel' => ['logo' => 'ab-essentiel.png', 'desc' => "Pour réduire ta commission et gagner en visibilité."],
    'Pro' => ['logo' => 'ab-pro.png', 'desc' => "Pour les artistes qui veulent aller plus loin et développer leur activité."],
];

$renderFeature = function (string $icon, string $label) {
    return '<div class="flex items-center gap-2 text-[0.85rem] text-ink">'
        . '<img src="/assets/images/icones/' . $icon . '" alt="" class="w-6 h-6 object-contain shrink-0">'
        . '<span class="whitespace-nowrap">' . htmlspecialchars($label) . '</span>'
        . '</div>';
};
?>

<section class="max-w-[1100px] mx-auto px-5 py-5 min-[641px]:px-10">
    <h1 class="font-title text-title text-[2rem] min-[641px]:text-[2.4rem] text-center mb-4">Choisir mon abonnement</h1>

    <?php if ($shop === null): ?>
        <div class="page-alert page-alert--info max-w-[560px] mx-auto text-center mb-5">
            Choisis ta formule pour continuer : tu pourras créer ta boutique juste après.
        </div>
    <?php elseif (!$hasChosenPlan): ?>
        <div class="page-alert page-alert--warning max-w-[560px] mx-auto text-center mb-5">
            Ta boutique n'est pas encore ouverte. Choisis une formule ci-dessous (gratuite ou payante) pour l'activer.
        </div>
    <?php endif; ?>

    <?php if ($currentSubscription !== null): ?>
        <div class="bg-white border border-border rounded-md p-5 shadow-sm max-w-[420px] mx-auto mb-6 text-center">
            <h2 class="font-cursive text-[1.2rem] font-semibold text-ink mb-1">Abonnement actif : <?= htmlspecialchars($currentSubscription['plan_name']) ?></h2>
            <p class="text-[0.85rem] text-muted mb-4">Valide jusqu'au <?= \App\Core\FrenchDate::format('d MMMM y', $currentSubscription['current_period_end']) ?></p>

            <form method="POST" action="/my-subscription/cancel">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <button type="submit" class="text-danger text-[0.85rem] font-semibold hover:underline" onclick="return confirm('Annuler ton abonnement ? Tu repasseras en formule Commission.');">
                    Annuler l'abonnement
                </button>
            </form>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 min-[768px]:grid-cols-3 gap-5">
        <?php foreach ($plans as $plan): ?>
            <?php
            $assets = $planAssets[$plan['name']] ?? ['logo' => 'ab-commission.png', 'desc' => ''];
            $isCurrent = $currentSubscription !== null && $currentSubscription['plan_id'] == $plan['id'];
            $isUnlimited = fn($v) => (int) $v >= 9999;
            ?>
            <div class="bg-white border border-border rounded-2xl shadow-sm p-5 flex flex-col items-center text-center">
                <img src="/assets/images/decor/<?= $assets['logo'] ?>" alt="" class="w-20 h-20 object-contain mb-2">
                <h2 class="font-cursive text-[1.3rem] font-semibold text-ink mb-1"><?= htmlspecialchars($plan['name']) ?></h2>
                <p class="text-[0.8rem] text-muted mb-3 max-w-[220px]"><?= htmlspecialchars($assets['desc']) ?></p>

                <hr class="w-full border-t border-border mb-2">
                <img src="/assets/images/decor/palette.png" alt="" class="w-7 h-7 object-contain mb-2">

                <div class="font-cursive text-[1.8rem] font-bold text-primary leading-none mb-1">
                    <?= number_format($plan['price'] / 100, 2, ',', ' ') ?> €
                </div>
                <p class="text-[0.75rem] text-muted mb-3">par mois</p>

                <div class="flex flex-col gap-1.5 w-full items-start mb-4 px-2">
                    <?= $renderFeature('commissions.png', rtrim(rtrim(number_format((float) $plan['commission_rate'], 2), '0'), '.') . '% de commission') ?>
                    <?= $renderFeature('prestations.png', 'Prestations : ' . ($isUnlimited($plan['max_services']) ? 'illimité' : $plan['max_services'] . ' max.')) ?>
                    <?= $renderFeature('portfolio.png', 'Portfolio : ' . ($isUnlimited($plan['max_portfolio_images']) ? 'illimité' : $plan['max_portfolio_images'] . ' photos max.')) ?>
                    <?= $renderFeature('parametres.png', 'Options : ' . ($isUnlimited($plan['max_options_per_service']) ? 'illimité' : $plan['max_options_per_service'] . ' max.')) ?>
                    <?= $renderFeature('acheter-ticket.png', 'Tirage au sort accessible') ?>
                </div>

                <div class="mt-auto w-full">
                    <?php if ($isCurrent): ?>
                        <span class="<?= \App\Core\Badge::classes('success') ?>">Plan actuel</span>
                    <?php elseif ($plan['name'] === 'Commission'): ?>
                        <?php if (!$hasChosenPlan): ?>
                            <form method="POST" action="/my-subscription/confirm-free">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <button type="submit" class="btn btn--primary w-full">Choisir Commission</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted text-[0.8rem]">Formule gratuite par défaut</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="POST" action="/my-subscription/subscribe">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                            <button type="submit" class="btn btn--primary w-full">
                                <?= $currentSubscription !== null ? 'Changer pour ' . htmlspecialchars($plan['name']) : 'Choisir ' . htmlspecialchars($plan['name']) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($shop !== null): ?>
        <p class="text-center mt-5">
            <a href="/my-shop" class="text-primary text-[0.85rem] hover:underline">← Retour à ma boutique</a>
        </p>
    <?php endif; ?>
</section>
