<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir SubscriptionController::subscribe()).
 *
 * @var array $plan
 * @var string $clientSecret
 * @var string $customerSessionClientSecret
 * @var string $stripePublicKey
 */
$pageTitle = 'Paiement abonnement — Toile';

$planLogos = [
    'Commission' => 'ab-commission.png',
    'Essentiel' => 'ab-essentiel.png',
    'Pro' => 'ab-pro.png',
];
$planLogo = $planLogos[$plan['name']] ?? 'ab-commission.png';
?>

<section class="max-w-[560px] mx-auto px-5 py-10">
    <h1 class="font-title text-title text-shine text-[2.2rem] text-center mb-6">Paiement de l'abonnement</h1>

    <div class="bg-white border border-border rounded-2xl shadow-sm p-5 mb-5 flex items-center gap-4">
        <img src="/assets/images/decor/<?= $planLogo ?>" alt="" class="w-16 h-16 object-contain shrink-0">
        <div class="min-w-0">
            <p class="font-semibold text-ink"><?= htmlspecialchars($plan['name']) ?></p>
            <p class="text-[0.85rem] text-muted">Abonnement mensuel</p>
        </div>
        <div class="ml-auto text-right shrink-0">
            <p class="font-cursive text-[1.4rem] font-bold text-primary leading-none"><?= number_format($plan['price'] / 100, 2) ?> €</p>
            <p class="text-[0.75rem] text-muted">par mois</p>
        </div>
    </div>

    <div
        id="subscription-payment-form"
        class="bg-white border border-border rounded-2xl shadow-sm p-5"
        data-stripe-public-key="<?= htmlspecialchars($stripePublicKey) ?>"
        data-client-secret="<?= htmlspecialchars($clientSecret) ?>"
        data-customer-session-client-secret="<?= htmlspecialchars($customerSessionClientSecret) ?>">
        <div id="payment-element"></div>
        <p id="payment-errors" class="text-danger text-[0.85rem] mt-3"></p>
        <button id="submit-btn" class="btn btn--primary w-full mt-4 hidden">
            Confirmer l'abonnement — <?= number_format($plan['price'] / 100, 2) ?> €/mois
        </button>
    </div>

    <p class="text-center mt-5">
        <a href="/my-subscription" class="text-primary text-[0.85rem] hover:underline">← Retour aux abonnements</a>
    </p>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script src="/assets/js/subscription-payment.js"></script>
