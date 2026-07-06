<h1>Mon abonnement</h1>

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
    <p>Tu es actuellement sur la <strong>formule Commission</strong> (<?= $_ENV['DEFAULT_COMMISSION_RATE'] ?? 10 ?>% prélevé sur chaque commande).</p>
    <p>Passe à un abonnement pour supprimer la commission :</p>
<?php endif; ?>

<div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
    <?php foreach ($plans as $plan): ?>
        <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 220px;">
            <h2><?= htmlspecialchars($plan['name']) ?></h2>
            <p style="font-size: 1.5rem; font-weight: bold;">
                <?= number_format($plan['price'] / 100, 2) ?> €<span style="font-size: 1rem; font-weight: normal;">/mois</span>
            </p>
            <p>✅ 0% de commission</p>

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