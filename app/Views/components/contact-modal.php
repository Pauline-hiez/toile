<?php
/**
 * Modale de contact réutilisable, incluse une seule fois globalement (voir
 * layouts/default.php). Le lien du footer (data-contact-open) l'ouvre via
 * contact-modal.js ; le href réel (/contact) reste en place pour la
 * dégradation sans JS (voir contact.php). Même traitement visuel que
 * auth-modal.php (cadre décoratif form.png + fleur.png).
 *
 * @var array|null $currentUser Injecté par le scope de layouts/default.php (peut être absent si non connecté).
 */
$contactStatus = $_GET['contact'] ?? '';
?>
<dialog id="contactModal" class="auth-modal"<?= $contactStatus !== '' ? ' data-contact-reopen' : '' ?>>
    <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-contact-close aria-label="Fermer">&times;</button>

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

                    <h2 class="font-title text-[1.6rem] min-[481px]:text-[1.9rem] text-title font-semibold text-center leading-none mb-4">Nous contacter</h2>

                    <?php if ($contactStatus === 'sent'): ?>
                        <div class="page-alert page-alert--success">Ton message a bien été envoyé, merci !</div>
                    <?php elseif ($contactStatus === 'error'): ?>
                        <div class="page-alert page-alert--warning">Merci de remplir tous les champs avec une adresse email valide.</div>
                    <?php endif; ?>

                    <form method="POST" action="/contact" class="flex flex-col gap-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'] ?? '/', '?')) ?>">

                        <div>
                            <label for="modalContactName" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Nom</label>
                            <input type="text" id="modalContactName" name="name" required value="<?= htmlspecialchars($currentUser['username'] ?? '') ?>"
                                class="js-spark-input relative w-full border border-title rounded-full px-4 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                        </div>

                        <div>
                            <label for="modalContactEmail" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Email</label>
                            <input type="email" id="modalContactEmail" name="email" required value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
                                class="js-spark-input relative w-full border border-title rounded-full px-4 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                        </div>

                        <div>
                            <label for="modalContactSubject" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Sujet</label>
                            <input type="text" id="modalContactSubject" name="subject"
                                class="js-spark-input relative w-full border border-title rounded-full px-4 py-[0.3rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary">
                        </div>

                        <div>
                            <label for="modalContactMessage" class="block font-semibold text-[0.85rem] min-[481px]:text-[0.95rem] text-primary mb-1">Message</label>
                            <textarea id="modalContactMessage" name="message" rows="4" required
                                class="w-full border border-title rounded-md px-4 py-[0.5rem] bg-bg font-main text-[0.92rem] min-[481px]:text-[1rem] outline-none transition-colors duration-300 focus:border-primary resize-y"></textarea>
                        </div>

                        <div class="text-center mt-1">
                            <button type="submit" class="btn btn--primary px-8 min-[481px]:px-12">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <img src="/assets/images/decor/fleur.png" alt="" class="hidden min-[400px]:block absolute bottom-3 right-3 w-[150px] h-auto pointer-events-none select-none opacity-90">
    </div>
</dialog>
