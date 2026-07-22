<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AuthController::showForgotPassword()/forgotPassword()).
 *
 * @var string|null $success
 * @var string|null $error
 */
$pageTitle = 'Mot de passe oublié — Toile';
?>

<div class="max-w-[1400px] mx-auto px-5 pt-0 pb-4 min-[641px]:px-10 min-[641px]:pt-0 min-[641px]:pb-6">
    <h1 class="font-title text-shine text-[1.7rem] min-[481px]:text-[2.2rem] text-title font-semibold text-center leading-none mb-0">Mot de passe oublié</h1>

    <?php if ($success !== null): ?>
        <p class="max-w-[820px] mx-auto text-success text-[0.85rem] text-center mt-4">
            <?= htmlspecialchars($success) ?>
        </p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p class="max-w-[820px] mx-auto text-danger text-[0.85rem] text-center mt-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="relative max-w-[820px] mx-auto">
        <div class="relative" style="
            border-style: solid;
            border-width: clamp(40px, 13vw, 85px) clamp(30px, 8vw, 55px) clamp(24px, 6vw, 40px) clamp(30px, 8vw, 55px);
            border-image-source: url('/assets/images/decor/form.png');
            border-image-slice: 200 260 180 260 fill;
            border-image-repeat: round stretch;
        ">
            <div class="flex flex-col items-center py-2">
                <div class="w-full max-w-[440px] px-3 min-[481px]:px-4 flex flex-col gap-3 min-[481px]:gap-5">
                    <p class="text-center text-[0.78rem] min-[481px]:text-[0.9rem] text-primary">
                        Indique ton adresse email : on t'envoie un lien pour réinitialiser ton mot de passe.
                    </p>

                    <form method="POST" action="/forgot-password" class="flex flex-col gap-3 min-[481px]:gap-5">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

                        <div>
                            <label for="email" class="block font-semibold text-[0.8rem] min-[481px]:text-[0.95rem] text-primary mb-1">Email</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" required
                                    class="js-spark-input relative w-full border border-title rounded-full px-4 min-[481px]:px-5 py-[0.3rem] bg-bg font-main text-[0.88rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                            </div>
                        </div>

                        <div class="text-center mt-1">
                            <button type="submit" class="btn btn--primary px-6 min-[481px]:px-12">Envoyer le lien</button>
                        </div>
                    </form>

                    <p class="text-center text-[0.78rem] min-[481px]:text-[0.9rem] text-primary">
                        <a href="/login" class="font-semibold underline">← Retour à la connexion</a>
                    </p>
                </div>
            </div>
        </div>

        <img src="/assets/images/decor/fleur.png" alt="" class="hidden min-[560px]:block absolute -bottom-3 -right-4 w-[270px] h-auto pointer-events-none select-none">
    </div>
</div>

<script src="/assets/js/input-sparks.js"></script>
