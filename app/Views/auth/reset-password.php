<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AuthController::showResetPassword()/resetPassword()).
 *
 * @var string|null $token
 * @var string|null $error
 */
$pageTitle = 'Nouveau mot de passe — Toile';
?>

<div class="max-w-[1400px] mx-auto px-5 pt-0 pb-4 min-[641px]:px-10 min-[641px]:pt-0 min-[641px]:pb-6">
    <h1 class="font-title text-shine text-[1.7rem] min-[481px]:text-[2.2rem] text-title font-semibold text-center leading-none mb-0">Nouveau mot de passe</h1>

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
                    <?php if ($token !== null): ?>
                        <form method="POST" action="/reset-password" class="flex flex-col gap-3 min-[481px]:gap-5">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                            <div>
                                <label for="password" class="block font-semibold text-[0.8rem] min-[481px]:text-[0.95rem] text-primary mb-1">Nouveau mot de passe</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required
                                        class="js-spark-input relative w-full border border-title rounded-full px-4 min-[481px]:px-5 pr-10 min-[481px]:pr-11 py-[0.3rem] bg-bg font-main text-[0.88rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                                    <button type="button" class="js-toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-primary" aria-label="Afficher le mot de passe" data-target="password">
                                        <svg class="js-icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <svg class="js-icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.7 19.7 0 0 1 5.06-5.94M9.9 4.24A9.1 9.1 0 0 1 12 4c7 0 11 8 11 8a19.6 19.6 0 0 1-3.22 4.36M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>
                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="password_confirm" class="block font-semibold text-[0.8rem] min-[481px]:text-[0.95rem] text-primary mb-1">Confirmer le mot de passe</label>
                                <div class="relative">
                                    <input type="password" id="password_confirm" name="password_confirm" required
                                        class="js-spark-input relative w-full border border-title rounded-full px-4 min-[481px]:px-5 pr-10 min-[481px]:pr-11 py-[0.3rem] bg-bg font-main text-[0.88rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                                    <button type="button" class="js-toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-primary" aria-label="Afficher le mot de passe" data-target="password_confirm">
                                        <svg class="js-icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <svg class="js-icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.7 19.7 0 0 1 5.06-5.94M9.9 4.24A9.1 9.1 0 0 1 12 4c7 0 11 8 11 8a19.6 19.6 0 0 1-3.22 4.36M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>
                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="text-center mt-1">
                                <button type="submit" class="btn btn--primary px-6 min-[481px]:px-12">Changer mon mot de passe</button>
                            </div>
                        </form>
                    <?php endif; ?>

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
<script src="/assets/js/password-toggle.js"></script>
