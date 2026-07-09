<h1>Mes moyens de paiement</h1>

<?php if (empty($savedCards)): ?>
    <p>Tu n'as aucune carte enregistrée.</p>
    <p>Ta carte sera automatiquement enregistrée lors de ton prochain paiement si tu coches "Mémoriser cette carte".</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($savedCards as $card): ?>
            <li style="border: 1px solid #ccc; border-radius: 6px; padding: 1rem; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                <span>
                    💳 <strong><?= htmlspecialchars(ucfirst($card->card->brand)) ?></strong>
                    •••• <?= htmlspecialchars($card->card->last4) ?>
                    — expire <?= htmlspecialchars($card->card->exp_month) ?>/<?= htmlspecialchars($card->card->exp_year) ?>
                </span>
                <form method="POST" action="/profile/payment-methods/delete">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <input type="hidden" name="payment_method_id" value="<?= htmlspecialchars($card->id) ?>">
                    <button
                        type="submit"
                        onclick="return confirm('Supprimer cette carte ?');"
                        style="color: #e53e3e;">
                        Supprimer
                    </button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="/profile">← Retour au profil</a></p>