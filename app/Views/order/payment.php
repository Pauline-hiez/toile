<h1>Paiement sécurisé</h1>

<p>
    <strong>Prestation :</strong> <?= htmlspecialchars($service['title']) ?><br>
    <strong>Boutique :</strong> <?= htmlspecialchars($shop['name']) ?><br>
    <strong>Total :</strong> <?= number_format($totalPrice / 100, 2) ?> €
</p>

<p>
    ℹ️ Le montant sera uniquement <strong>autorisé</strong> sur ta carte aujourd'hui.
    Il ne sera débité qu'une fois ta commande acceptée par l'artiste.
</p>

<div
    id="payment-form"
    style="max-width: 500px;"
    data-stripe-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
    data-client-secret="<?= htmlspecialchars($clientSecret) ?>"
    data-total-price="<?= number_format($totalPrice / 100, 2, '.', '') ?>">
    <div id="payment-element"></div>
    <div id="payment-errors" style="color: red; margin-top: 0.5rem;"></div>
    <button id="submit-btn" style="margin-top: 1rem; display: none;">
        Autoriser le paiement — <?= number_format($totalPrice / 100, 2) ?> €
    </button>
</div>

<p><a href="javascript:history.back()">← Retour</a></p>

<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/js/payment.js"></script>