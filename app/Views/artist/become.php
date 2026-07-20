<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ArtistController::showRequest()/submitRequest()).
 *
 * @var array $user
 * @var array<string, string> $errors
 * @var array<string, string> $old
 * @var string|null $success
 */
$pageTitle = 'Devenir artiste — Toile';

$steps = [
    ['icon' => 'envoyer-demande.png', 'label' => 'Envoyez une demande'],
    ['icon' => 'abonnements.png', 'label' => 'Choisissez votre abonnement'],
    ['icon' => 'boutique.png', 'label' => 'Création de votre boutique'],
];
?>

<section class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:pt-2 min-[641px]:pb-10">
    <div class="grid grid-cols-1 min-[1100px]:grid-cols-[1.4fr_1fr] items-start gap-10 mb-10">
        <div class="min-w-0">
            <h1 class="font-title text-[2.1rem] min-[641px]:text-[3.4rem] font-semibold text-title leading-none mb-4">Devenir Artiste</h1>
            <p class="font-main font-medium text-[0.95rem] text-footer leading-[1.5] mb-8 max-w-[480px]">
                Envie de libérer votre créativité et de la mettre au service des autres ?
                Devenez Artiste !
            </p>

            <div class="flex flex-col gap-6">
                <?php foreach ($steps as $step): ?>
                    <div class="flex items-center gap-5 min-w-0">
                        <span class="flex items-center justify-center w-[72px] h-[72px] rounded-full bg-[radial-gradient(circle,var(--color-title-bg)_0%,transparent_72%)] shrink-0">
                            <img src="/assets/images/icones/<?= $step['icon'] ?>" alt="" class="w-[60px] h-[60px] object-contain">
                        </span>
                        <span class="font-cursive text-[1.15rem] text-ink min-w-0"><?= htmlspecialchars($step['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="order-first min-[1100px]:order-none max-w-[360px] min-[1100px]:max-w-none mx-auto min-[1100px]:mx-0 min-w-0">
            <img src="/assets/images/decor/encrier.png" alt="Encrier et plume" class="w-full max-w-[440px] min-w-0 h-auto block mx-auto">
        </div>
    </div>

    <div class="flex justify-center text-center">
        <?php if (isset($success)): ?>
            <div class="page-alert page-alert--success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($user['role'] === 'artist'): ?>
            <div class="page-alert page-alert--info">
                Tu es déjà artiste sur la plateforme.
                <a href="/my-shop">Gérer ma boutique →</a>
            </div>
        <?php elseif ($user['artist_request_status'] === 'pending'): ?>
            <div class="page-alert page-alert--info">
                Ta demande est en cours d'examen. Tu seras notifié(e) dès qu'elle sera traitée.
            </div>
        <?php else: ?>
            <?php if ($user['artist_request_status'] === 'rejected'): ?>
                <div class="page-alert page-alert--warning">
                    Ta précédente demande n'a pas été retenue. Tu peux soumettre une nouvelle demande ci-dessous.
                </div>
            <?php endif; ?>

            <div class="flex flex-col items-center gap-3">
                <button type="button" data-become-artist-open class="btn btn--primary px-10 py-3 text-base">Envoyer ma demande</button>
                <p class="text-muted text-[0.85rem] leading-[1.5] max-w-[400px] text-center">
                    Un administrateur examinera ta demande prochainement.
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../components/become-artist-modal.php'; ?>
<script src="/assets/js/become-artist-modal.js"></script>
