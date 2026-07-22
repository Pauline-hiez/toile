<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir PayoutController::index()).
 *
 * @var array|null $shop
 */
$pageTitle = 'Mes paiements — Toile';

$isConnected = $shop !== null && !empty($shop['stripe_account_id']);
$payoutsEnabled = $isConnected && !empty($shop['stripe_payouts_enabled']);
?>

<div class="max-w-[560px] mx-auto">
    <?php if ($shop === null): ?>
        <div class="page-alert page-alert--info text-center">
            Tu dois avoir une boutique avant de pouvoir connecter un compte bancaire.
            <a href="/my-shop">Créer ma boutique →</a>
        </div>
    <?php elseif ($payoutsEnabled): ?>
        <div class="bg-white border border-border rounded-md p-6 shadow-sm text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success-bg text-success mb-4">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="m8.5 12.5 2.5 2.5 5-5"></path>
                </svg>
            </div>
            <h2 class="font-cursive text-[1.2rem] font-semibold text-ink mb-2">Compte bancaire connecté</h2>
            <p class="text-muted text-[0.9rem] leading-[1.6] mb-5">
                Ta part est désormais reversée automatiquement sur ce compte à chaque commande acceptée et payée.
            </p>
            <form method="POST" action="/my-payouts/connect">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <button type="submit" class="btn btn--outline">Gérer mon compte Stripe</button>
            </form>
        </div>
    <?php elseif ($isConnected): ?>
        <div class="bg-white border border-border rounded-md p-6 shadow-sm text-center">
            <h2 class="font-cursive text-[1.2rem] font-semibold text-ink mb-2">Inscription en cours</h2>
            <p class="text-muted text-[0.9rem] leading-[1.6] mb-5">
                Ton inscription Stripe n'est pas encore complète — termine-la pour pouvoir recevoir tes reversements.
            </p>
            <form method="POST" action="/my-payouts/connect">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <button type="submit" class="btn btn--primary">Continuer la configuration</button>
            </form>
        </div>
    <?php else: ?>
        <div class="bg-white border border-border rounded-md p-6 shadow-sm text-center">
            <h2 class="font-cursive text-[1.2rem] font-semibold text-ink mb-2">Connecte ton compte bancaire</h2>
            <p class="text-muted text-[0.9rem] leading-[1.6] mb-5">
                Pour recevoir directement ta part de chaque commande, connecte un compte bancaire via Stripe.
                Tu seras redirigé·e vers un formulaire sécurisé hébergé par Stripe.
            </p>
            <form method="POST" action="/my-payouts/connect">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <button type="submit" class="btn btn--primary">Connecter mon compte bancaire</button>
            </form>
        </div>
    <?php endif; ?>
</div>
