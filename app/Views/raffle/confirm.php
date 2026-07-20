<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir RaffleController::confirm()).
 *
 * @var string $type 'boutiques'|'homepage'
 */
$pageTitle = 'Inscription confirmée — Toile';
?>

<section class="max-w-[480px] mx-auto px-5 py-16 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-success-bg text-success mb-5">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="m8.5 12.5 2.5 2.5 5-5"></path>
        </svg>
    </div>

    <h1 class="font-title text-title text-[2rem] mb-3">Inscription confirmée !</h1>

    <p class="text-muted text-[0.9rem] leading-[1.6] mb-6">
        Ton ticket a bien été payé pour le tirage
        <strong class="text-ink"><?= $type === 'homepage' ? 'Page d\'accueil' : 'Vitrine boutiques' ?></strong>.
        <br>
        <?php if ($type === 'homepage'): ?>
            Le tirage a lieu chaque lundi. Tu seras notifié·e du résultat.
        <?php else: ?>
            Le tirage a lieu le 1er de chaque mois. Tu seras notifié·e du résultat.
        <?php endif; ?>
    </p>

    <a href="/raffle" class="btn btn--primary">← Retour aux tirages</a>
</section>
