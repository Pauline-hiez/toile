<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir RaffleController::enter()).
 *
 * @var string $type 'boutiques'|'homepage'
 * @var int $price
 * @var string $clientSecret
 * @var string $customerSessionClientSecret
 * @var string $stripePublicKey
 */
$pageTitle = 'Paiement du ticket — Toile';
$typeLabel = $type === 'homepage' ? 'Page d\'accueil' : 'Vitrine boutiques';
$ticketImage = $type === 'homepage' ? 'ticket-b.png' : 'ticket-a.png';
?>

<section class="max-w-[560px] mx-auto px-5 py-10">
    <h1 class="font-title text-title text-[2.2rem] text-center mb-6">Paiement du ticket</h1>

    <div class="bg-white border border-border rounded-2xl shadow-sm p-5 mb-5 flex items-center gap-4">
        <img src="/assets/images/decor/<?= $ticketImage ?>" alt="" class="w-16 h-auto object-contain shrink-0">
        <div class="min-w-0">
            <p class="font-semibold text-ink">Tirage au sort</p>
            <p class="text-[0.85rem] text-muted"><?= htmlspecialchars($typeLabel) ?></p>
        </div>
        <div class="ml-auto text-right shrink-0">
            <p class="font-cursive text-[1.4rem] font-bold text-primary leading-none"><?= number_format($price / 100, 2) ?> €</p>
        </div>
    </div>

    <div class="page-alert page-alert--info text-[0.85rem] mb-5">
        Ce montant est prélevé immédiatement, que ta boutique soit sélectionnée ou non.
    </div>

    <div
        id="payment-form"
        class="bg-white border border-border rounded-2xl shadow-sm p-5"
        data-stripe-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
        data-client-secret="<?= htmlspecialchars($clientSecret) ?>"
        data-customer-session-client-secret="<?= htmlspecialchars($customerSessionClientSecret) ?>"
        data-total-price="<?= number_format($price / 100, 2, '.', '') ?>"
        data-return-url="<?= htmlspecialchars(($_ENV['APP_URL'] ?? 'http://toile.test') . '/raffle/confirm') ?>">
        <div id="payment-element"></div>
        <p id="payment-errors" class="text-danger text-[0.85rem] mt-3"></p>
        <button id="submit-btn" class="btn btn--primary w-full mt-4 hidden">
            Payer <?= number_format($price / 100, 2) ?> €
        </button>
    </div>

    <p class="text-center mt-5">
        <a href="/raffle" class="text-primary text-[0.85rem] hover:underline">← Retour aux tirages</a>
    </p>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/js/payment.js"></script>
