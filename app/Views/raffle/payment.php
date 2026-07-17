<h1>Paiement du ticket</h1>

<p>
    <strong>Tirage :</strong>
    <?= $type === 'homepage' ? 'Page d\'accueil' : 'Vitrine boutiques' ?><br>
    <strong>Montant à régler :</strong> <?= number_format($price / 100, 2) ?> €<br>
    <em>Ce montant est prélevé immédiatement, que ta boutique soit sélectionnée ou non.</em>
</p>

<div
    id="payment-form"
    style="max-width: 500px;"
    data-stripe-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
    data-client-secret="<?= htmlspecialchars($clientSecret) ?>"
    data-customer-session-client-secret="<?= htmlspecialchars($customerSessionClientSecret) ?>"
    data-total-price="<?= number_format($price / 100, 2, '.', '') ?>"
    data-return-url="<?= htmlspecialchars(($_ENV['APP_URL'] ?? 'http://toile.test') . '/raffle/confirm') ?>">
    <div id="payment-element"></div>
    <div id="payment-errors" style="color: red; margin-top: 0.5rem;"></div>
    <button id="submit-btn" style="margin-top: 1rem; display: none;">
        Payer <?= number_format($price / 100, 2) ?> €
    </button>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/js/payment.js"></script>