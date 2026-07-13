<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir SubscriptionController::index()).
 *
 * @var array $plans
 * @var array|null $currentSubscription
 * @var array|null $shop
 */
$hasChosenPlan = $shop !== null && (bool) $shop['plan_selected'];
?>
<h1>Mon abonnement</h1>

<?php if ($shop !== null && !$hasChosenPlan): ?>
    <div style="border: 2px solid #92400e; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; max-width: 500px;">
        <p><strong>Ta boutique n'est pas encore ouverte.</strong></p>
        <p>Choisis une formule ci-dessous (gratuite ou payante) pour l'activer et commencer à recevoir des commandes.</p>
    </div>
<?php endif; ?>

<?php if ($currentSubscription !== null): ?>
    <div style="border: 2px solid #6f42c1; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; max-width: 400px;">
        <h2>Abonnement actif : <?= htmlspecialchars($currentSubscription['plan_name']) ?></h2>
        <p>Valide jusqu'au : <?= htmlspecialchars($currentSubscription['current_period_end']) ?></p>
        <p>Commission : <strong>0%</strong></p>

        <form method="POST" action="/my-subscription/cancel">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
            <button
                type="submit"
                onclick="return confirm('Annuler ton abonnement ? Tu repasseras en formule Commission.');"
                style="color: #e53e3e;">
                Annuler l'abonnement
            </button>
        </form>
    </div>
<?php else: ?>
    <?php $freeCommission = $_ENV['DEFAULT_COMMISSION_RATE'] ?? 10; ?>
    <p>
        <?= $hasChosenPlan ? 'Tu es actuellement sur la' : 'Sans abonnement, tu serais sur la' ?>
        <strong>formule Commission</strong>
        (<?= $freeCommission ?>% prélevé sur chaque commande — 3 prestations, 5 photos portfolio, 2 options par prestation).
    </p>

    <?php if (!$hasChosenPlan): ?>
        <form method="POST" action="/my-subscription/confirm-free" style="margin: 1rem 0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
            <button type="submit">Continuer avec la formule Commission (gratuite)</button>
        </form>
    <?php endif; ?>

    <p>Passe à un abonnement pour réduire la commission et débloquer plus de contenu :</p>
<?php endif; ?>

<div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
    <?php foreach ($plans as $plan): ?>
        <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 220px;">
            <h2><?= htmlspecialchars($plan['name']) ?></h2>
            <p style="font-size: 1.5rem; font-weight: bold;">
                <?= number_format($plan['price'] / 100, 2) ?> €<span style="font-size: 1rem; font-weight: normal;">/mois</span>
            </p>
            <p>✅ <?= rtrim(rtrim(number_format((float) $plan['commission_rate'], 2), '0'), '.') ?>% de commission</p>
            <p><?= (int) $plan['max_services'] >= 9999 ? 'Prestations illimitées' : (int) $plan['max_services'] . ' prestations maximum' ?></p>
            <p><?= (int) $plan['max_portfolio_images'] >= 9999 ? 'Photos portfolio illimitées' : (int) $plan['max_portfolio_images'] . ' photos portfolio maximum' ?></p>
            <p><?= (int) $plan['max_options_per_service'] >= 9999 ? 'Options par prestation illimitées' : (int) $plan['max_options_per_service'] . ' options par prestation maximum' ?></p>
            <?php if ((int) $plan['max_services'] >= 9999): ?>
                <p>📊 Statistiques de boutique (vues, commandes reçues, revenus du mois)</p>
            <?php endif; ?>

            <?php if ($currentSubscription === null || $currentSubscription['plan_id'] != $plan['id']): ?>
                <form method="POST" action="/my-subscription/subscribe">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <button type="submit">
                        <?= $currentSubscription !== null ? 'Changer de plan' : 'Souscrire' ?>
                    </button>
                </form>
            <?php else: ?>
                <p><em>Plan actuel</em></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<p><a href="/my-shop">← Retour à ma boutique</a></p>
