<h1>Autorisation de paiement</h1>

<p>
    <strong>Montant autorisé :</strong> <?= number_format($rafflePrice / 100, 2) ?> €<br>
    <em>Ce montant ne sera débité que si ta boutique est sélectionnée au tirage.</em>
</p>

<div
    id="payment-form"
    style="max-width: 500px;"
    data-stripe-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
    data-client-secret="<?= htmlspecialchars($clientSecret) ?>"
    data-total-price="<?= number_format($rafflePrice / 100, 2, '.', '') ?>"
    data-return-url="<?= htmlspecialchars(($_ENV['APP_URL'] ?? 'http://toile.test') . '/raffle/confirm') ?>">
    <div id="payment-element"></div>
    <div id="payment-errors" style="color: red; margin-top: 0.5rem;"></div>
    <button id="submit-btn" style="margin-top: 1rem; display: none;">
        Autoriser <?= number_format($rafflePrice / 100, 2) ?> €
    </button>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/js/payment.js"></script>