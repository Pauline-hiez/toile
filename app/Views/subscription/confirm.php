<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir SubscriptionController::confirm()).
 *
 * @var array $plan
 */
$pageTitle = 'Abonnement activé — Toile';
$commissionRate = rtrim(rtrim(number_format((float) $plan['commission_rate'], 2), '0'), '.');
?>

<section class="max-w-[480px] mx-auto px-5 py-16 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success-bg text-success mb-5">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="m8.5 12.5 2.5 2.5 5-5"></path>
        </svg>
    </div>

    <h1 class="font-title text-title text-[2rem] mb-3">Abonnement activé !</h1>

    <p class="text-muted text-[0.9rem] leading-[1.6] mb-6">
        Ton abonnement <strong class="text-ink"><?= htmlspecialchars($plan['name']) ?></strong> est maintenant actif.
        Tu bénéficies de <strong class="text-ink"><?= $commissionRate ?>% de commission</strong> sur toutes tes commandes.
    </p>

    <a href="/my-subscription" class="btn btn--primary">Voir mon abonnement</a>
</section>
