<h1>Paiement de l'abonnement</h1>

<p>
    <strong>Plan :</strong> <?= htmlspecialchars($plan['name']) ?><br>
    <strong>Montant :</strong> <?= number_format($plan['price'] / 100, 2) ?> €/mois
</p>

<div
    id="subscription-payment-form"
    style="max-width: 500px;"
    data-stripe-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
    data-client-secret="<?= htmlspecialchars($clientSecret) ?>">
    <div id="payment-element"></div>
    <div id="payment-errors" style="color: red; margin-top: 0.5rem;"></div>
    <button id="submit-btn" style="margin-top: 1rem; display: none;">
        Confirmer l'abonnement — <?= number_format($plan['price'] / 100, 2) ?> €/mois
    </button>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/js/subscription-payment.js"></script>