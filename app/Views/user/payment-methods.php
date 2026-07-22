<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir UserController::paymentMethods()).
 *
 * @var array $savedCards Cartes Stripe enregistrées (PaymentMethod du SDK Stripe).
 * @var bool $isArtist Si vrai, le layout artiste affiche déjà le titre de page.
 */
$pageTitle = 'Mes moyens de paiement — Toile';
?>

<div class="max-w-[700px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <?php if (!$isArtist): ?>
        <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Mes moyens de paiement</h1>
        <p class="text-center text-muted text-[0.9rem] mb-10">Gère les cartes enregistrées pour tes commandes.</p>
    <?php endif; ?>

    <div class="bg-white border border-border rounded-md shadow-sm p-6">
        <?php if (empty($savedCards)): ?>
            <p class="text-muted text-[0.85rem] text-center py-2">Tu n'as aucune carte enregistrée.</p>
            <p class="text-muted text-[0.8rem] text-center">Ta carte sera automatiquement enregistrée lors de ton prochain paiement si tu coches « Mémoriser cette carte ».</p>
        <?php else: ?>
            <div class="flex flex-col gap-3">
                <?php foreach ($savedCards as $card): ?>
                    <div class="flex items-center justify-between gap-4 flex-wrap border border-border rounded-md p-4">
                        <span class="text-[0.9rem] text-ink">
                            💳 <strong><?= htmlspecialchars(ucfirst($card->card->brand)) ?></strong>
                            •••• <?= htmlspecialchars($card->card->last4) ?>
                            — expire <?= htmlspecialchars($card->card->exp_month) ?>/<?= htmlspecialchars($card->card->exp_year) ?>
                        </span>
                        <form method="POST" action="/profile/payment-methods/delete">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <input type="hidden" name="payment_method_id" value="<?= htmlspecialchars($card->id) ?>">
                            <button type="submit" class="btn btn--outline" onclick="return confirm('Supprimer cette carte ?');">Supprimer</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <p class="text-center mt-6">
        <a href="/profile" class="text-primary text-[0.9rem] font-medium no-underline hover:underline">← Retour au profil</a>
    </p>
</div>
