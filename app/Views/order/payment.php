<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir OrderController::store()/payQuote()).
 *
 * @var array $service Peut être partiel (juste 'title') si issu de payQuote().
 * @var array $shop Peut être partiel (juste 'name') si issu de payQuote().
 * @var int $totalPrice
 * @var string $clientSecret
 * @var string $customerSessionClientSecret
 * @var string $stripePublicKey
 * @var string|null $returnUrl
 */
$pageTitle = 'Paiement — Toile';
?>

<section class="max-w-[560px] mx-auto px-5 py-10">
    <h1 class="font-title text-title text-shine text-[2.2rem] text-center mb-6">Paiement sécurisé</h1>

    <div class="bg-white border border-border rounded-2xl shadow-sm p-5 mb-5 flex items-center gap-4">
        <?php if (!empty($service['image'])): ?>
            <img src="/uploads/services/<?= htmlspecialchars($service['image']) ?>" alt="" class="w-16 h-16 rounded-md object-cover shrink-0">
        <?php else: ?>
            <img src="/assets/images/icones/paiement.png" alt="" class="w-16 h-16 object-contain shrink-0">
        <?php endif; ?>
        <div class="min-w-0">
            <p class="font-semibold text-ink truncate"><?= htmlspecialchars($service['title']) ?></p>
            <?php if (!empty($shop['name'])): ?>
                <p class="text-[0.85rem] text-muted truncate"><?= htmlspecialchars($shop['name']) ?></p>
            <?php endif; ?>
        </div>
        <div class="ml-auto text-right shrink-0">
            <p class="font-cursive text-[1.4rem] font-bold text-primary leading-none"><?= number_format($totalPrice / 100, 2) ?> €</p>
        </div>
    </div>

    <div class="page-alert page-alert--info text-[0.85rem] mb-5">
        Le montant sera uniquement <strong>autorisé</strong> sur ta carte aujourd'hui. Il ne sera débité qu'une fois ta commande acceptée par l'artiste.
    </div>

    <div
        id="payment-form"
        class="bg-white border border-border rounded-2xl shadow-sm p-5"
        data-stripe-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
        data-client-secret="<?= htmlspecialchars($clientSecret) ?>"
        data-customer-session-client-secret="<?= htmlspecialchars($customerSessionClientSecret) ?>"
        data-total-price="<?= number_format($totalPrice / 100, 2, '.', '') ?>"
        <?php if (isset($returnUrl)): ?>data-return-url="<?= htmlspecialchars($returnUrl) ?>"<?php endif; ?>>
        <div id="payment-element"></div>
        <p id="payment-errors" class="text-danger text-[0.85rem] mt-3"></p>
        <button id="submit-btn" class="btn btn--primary w-full mt-4 hidden">
            Autoriser le paiement — <?= number_format($totalPrice / 100, 2) ?> €
        </button>
    </div>

    <p class="text-center mt-5">
        <a href="javascript:history.back()" class="text-primary text-[0.85rem] hover:underline">← Retour</a>
    </p>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/js/payment.js"></script>
