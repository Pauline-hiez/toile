<?php
/**
 * Modale connexion/inscription réutilisable, incluse une seule fois
 * globalement (voir layouts/default.php). Les boutons du header
 * (data-auth-open="login"|"register") l'ouvrent sur le bon panneau via
 * auth-modal.js ; le lien "Pas encore de compte ?"/"Déjà un compte ?"
 * bascule entre les deux sans fermer la modale.
 *
 * Les formulaires postent vers les mêmes routes /login et /register que
 * les pages dédiées : en cas d'erreur de validation, le navigateur
 * navigue simplement vers la page complète correspondante (qui affiche
 * la même erreur) — dégradation progressive, pas d'AJAX ici, cohérent
 * avec report-modal.php.
 */
?>
<dialog id="authModal" class="auth-modal">
    <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-auth-close aria-label="Fermer">&times;</button>

    <div class="relative">
        <div class="relative" style="
            border-style: solid;
            border-width: clamp(45px, 13vw, 90px) clamp(28px, 7vw, 55px) clamp(22px, 6vw, 42px) clamp(28px, 7vw, 55px);
            border-image-source: url('/assets/images/decor/form.png');
            border-image-slice: 200 260 180 260 fill;
            border-image-repeat: round stretch;
        ">
            <div class="flex flex-col items-center py-2">
                <div class="w-full max-w-[460px] px-3 min-[481px]:px-4 flex flex-col gap-3">

                    <div data-auth-panel="login">
                        <h2 class="font-title text-[1.6rem] min-[481px]:text-[1.9rem] text-title font-semibold text-center leading-none mb-4">Se connecter</h2>

                        <form method="POST" action="/login" class="flex flex-col gap-3">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

                            <div>
                                <label for="modalLoginEmail" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Email</label>
                                <input type="email" id="modalLoginEmail" name="email" required
                                    class="js-spark-input relative w-full border border-title rounded-full px-4 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                            </div>

                            <div>
                                <label for="modalLoginPassword" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Mot de passe</label>
                                <div class="relative">
                                    <input type="password" id="modalLoginPassword" name="password" required
                                        class="js-spark-input relative w-full border border-title rounded-full px-4 pr-10 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                                    <button type="button" class="js-toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-primary" aria-label="Afficher le mot de passe" data-target="modalLoginPassword">
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

                            <div class="flex flex-col min-[400px]:flex-row min-[400px]:items-center justify-between gap-2 -mt-1">
                                <label class="inline-flex items-center gap-2 text-[0.78rem] min-[481px]:text-[0.85rem] text-primary cursor-pointer">
                                    <input type="checkbox" name="remember_me" class="w-4 h-4 accent-primary cursor-pointer">
                                    Se souvenir de moi
                                </label>
                                <a href="/forgot-password" class="text-[0.8rem] min-[481px]:text-[0.88rem] text-primary hover:underline">Mot de passe oublié ?</a>
                            </div>

                            <div class="text-center mt-1">
                                <button type="submit" class="btn btn--primary px-8 min-[481px]:px-12">Se connecter</button>
                            </div>
                        </form>

                        <p class="text-center text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mt-3">
                            Pas encore de compte ? <a href="/register" data-auth-switch="register" class="font-semibold underline">Créer un compte</a>
                        </p>

                        <?php if (!\App\Core\Demo::isEnabled()): ?>
                        <div class="flex items-center gap-3 text-[0.8rem] min-[481px]:text-[0.9rem] text-primary mt-3">
                            <span class="flex-1 border-t border-border"></span>
                            Ou
                            <span class="flex-1 border-t border-border"></span>
                        </div>

                        <p class="text-center text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mt-3">Connectez-vous avec</p>

                        <div class="flex justify-center mt-2">
                            <a href="/auth/google" title="Se connecter avec Google">
                                <img src="/assets/images/reseaux/google-icone.png" alt="Google" class="w-12 h-12 min-[481px]:w-14 min-[481px]:h-14 object-contain">
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div data-auth-panel="register" class="hidden">
                        <h2 class="font-title text-[1.6rem] min-[481px]:text-[1.9rem] text-title font-semibold text-center leading-none mb-4">Créer votre compte</h2>

                        <form method="POST" action="/register" class="flex flex-col gap-3">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

                            <div>
                                <label for="modalRegisterUsername" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Pseudo</label>
                                <input type="text" id="modalRegisterUsername" name="username" required
                                    class="js-spark-input relative w-full border border-title rounded-full px-4 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                            </div>

                            <div>
                                <label for="modalRegisterEmail" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Email</label>
                                <input type="email" id="modalRegisterEmail" name="email" required
                                    class="js-spark-input relative w-full border border-title rounded-full px-4 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                            </div>

                            <div>
                                <label for="modalRegisterPassword" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Mot de passe</label>
                                <div class="relative">
                                    <input type="password" id="modalRegisterPassword" name="password" required
                                        class="js-spark-input relative w-full border border-title rounded-full px-4 pr-10 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                                    <button type="button" class="js-toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-primary" aria-label="Afficher le mot de passe" data-target="modalRegisterPassword">
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
                                <label for="modalRegisterPasswordConfirm" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Confirmation de mot de passe</label>
                                <div class="relative">
                                    <input type="password" id="modalRegisterPasswordConfirm" name="password_confirm" required
                                        class="js-spark-input relative w-full border border-title rounded-full px-4 pr-10 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                                    <button type="button" class="js-toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-primary" aria-label="Afficher le mot de passe" data-target="modalRegisterPasswordConfirm">
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
                                <button type="submit" class="btn btn--primary px-8 min-[481px]:px-12">M'inscrire</button>
                            </div>
                        </form>

                        <p class="text-center text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mt-3">
                            Vous avez déjà un compte ? <a href="/login" data-auth-switch="login" class="font-semibold underline">Connectez-vous</a>
                        </p>

                        <?php if (!\App\Core\Demo::isEnabled()): ?>
                        <div class="flex items-center gap-3 text-[0.8rem] min-[481px]:text-[0.9rem] text-primary mt-3">
                            <span class="flex-1 border-t border-border"></span>
                            Ou
                            <span class="flex-1 border-t border-border"></span>
                        </div>

                        <p class="text-center text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mt-3">Connectez-vous avec</p>

                        <div class="flex justify-center mt-2">
                            <a href="/auth/google" title="S'inscrire avec Google">
                                <img src="/assets/images/reseaux/google-icone.png" alt="Google" class="w-12 h-12 min-[481px]:w-14 min-[481px]:h-14 object-contain">
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <img src="/assets/images/decor/fleur.png" alt="" class="hidden min-[400px]:block absolute bottom-3 right-3 w-[150px] h-auto pointer-events-none select-none opacity-90">
    </div>
</dialog>
