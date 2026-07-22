<?php
/**
 * Modale du formulaire de candidature artiste, incluse uniquement dans
 * artist/become.php (nécessite $user, $errors, $old en scope). Le bouton
 * "Envoyer ma demande" (data-become-artist-open) l'ouvre via
 * become-artist-modal.js — même convention que auth-modal.php / quote-modal.php.
 *
 * @var array $user Injecté par le scope de artist/become.php.
 * @var array<string, string> $errors
 * @var array<string, string> $old
 */
?>
<dialog id="becomeArtistModal" class="auth-modal"<?= !empty($errors) ? ' data-become-artist-reopen' : '' ?>>
    <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-become-artist-close aria-label="Fermer">&times;</button>

    <div class="relative bg-bg rounded-2xl border border-border shadow-lg px-6 py-8 min-[481px]:px-10 min-[481px]:py-10 max-h-[85vh] overflow-y-auto">
        <h2 class="font-title text-[1.6rem] min-[481px]:text-[1.9rem] text-title font-semibold text-center leading-none mb-5">Devenir Artiste</h2>

        <form method="POST" action="/become-artist" class="flex flex-col gap-5 max-w-[440px] mx-auto text-left">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <div>
                <label for="artist_display_name" class="block font-semibold text-[0.9rem] mb-2">Nom d'artiste</label>
                <input type="text" id="artist_display_name" name="artist_display_name" value="<?= htmlspecialchars($old['artist_display_name'] ?? '') ?>" required
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                <?php if (isset($errors['artist_display_name'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['artist_display_name']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="requested_shop_name" class="block font-semibold text-[0.9rem] mb-2">Nom de la boutique</label>
                <input type="text" id="requested_shop_name" name="requested_shop_name" value="<?= htmlspecialchars($old['requested_shop_name'] ?? '') ?>" required
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                <?php if (isset($errors['requested_shop_name'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['requested_shop_name']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="artist_contact_email" class="block font-semibold text-[0.9rem] mb-2">Adresse email</label>
                <input type="email" id="artist_contact_email" name="artist_contact_email" value="<?= htmlspecialchars($old['artist_contact_email'] ?? $user['email']) ?>" required
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                <?php if (isset($errors['artist_contact_email'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['artist_contact_email']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="artist_presentation" class="block font-semibold text-[0.9rem] mb-2">Présentation de l'artiste</label>
                <textarea id="artist_presentation" name="artist_presentation" rows="4" required
                    class="w-full border border-border rounded-md px-4 py-[0.6rem] bg-white font-main outline-none focus:border-primary resize-y"><?= htmlspecialchars($old['artist_presentation'] ?? '') ?></textarea>
                <?php if (isset($errors['artist_presentation'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['artist_presentation']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="artist_motivation" class="block font-semibold text-[0.9rem] mb-2">Pourquoi vouloir ouvrir une boutique ?</label>
                <textarea id="artist_motivation" name="artist_motivation" rows="4" required
                    class="w-full border border-border rounded-md px-4 py-[0.6rem] bg-white font-main outline-none focus:border-primary resize-y"><?= htmlspecialchars($old['artist_motivation'] ?? '') ?></textarea>
                <?php if (isset($errors['artist_motivation'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['artist_motivation']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="inline-flex items-start gap-2 text-[0.85rem] text-ink cursor-pointer">
                    <input type="checkbox" name="terms" value="1" class="mt-1 w-4 h-4 accent-primary cursor-pointer">
                    <span>J'accepte le <a href="/reglement-interieur" target="_blank" class="text-primary underline">règlement intérieur du site</a>.</span>
                </label>
                <?php if (isset($errors['terms'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['terms']) ?></p>
                <?php endif; ?>
            </div>

            <div class="text-center mt-1">
                <button type="submit" class="btn btn--primary px-10">Envoyer ma demande</button>
            </div>
        </form>
    </div>
</dialog>
