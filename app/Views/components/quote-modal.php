<?php
/**
 * Modale de demande de devis générale (sans prestation précise), incluse
 * uniquement dans shop/show.php (nécessite $shop en scope). Le bouton
 * "Demander un devis" (data-quote-open) l'ouvre via quote-modal.js — même
 * convention que auth-modal.php / report-modal.php.
 *
 * @var array $shop Injecté par le scope de shop/show.php.
 */
?>
<dialog id="quoteModal" class="auth-modal"<?= isset($_GET['quote_error']) ? ' data-quote-reopen' : '' ?>>
    <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-quote-close aria-label="Fermer">&times;</button>

    <div class="relative bg-bg rounded-2xl border border-border shadow-lg px-6 py-8 min-[481px]:px-10 min-[481px]:py-10">
        <h2 class="font-title text-[1.6rem] min-[481px]:text-[1.9rem] text-title font-semibold text-center leading-none mb-4">Demande de devis</h2>

        <p class="text-[0.85rem] min-[481px]:text-[0.9rem] text-ink text-center leading-[1.5] mb-5 max-w-[400px] mx-auto">
            Vous avez une idée mais vous avez besoin d'être guidé(e) ? Parlez-en à <strong><?= htmlspecialchars($shop['name']) ?></strong>, qui vous aidera à la réaliser ! Soyez le plus précis possible.
        </p>

        <form method="POST" action="/boutiques/<?= htmlspecialchars($shop['slug']) ?>/devis" enctype="multipart/form-data" class="flex flex-col gap-3 max-w-[400px] mx-auto">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <textarea
                name="description"
                rows="6"
                required
                minlength="10"
                placeholder="Décris ton projet en détail..."
                class="w-full border border-border rounded-2xl px-4 py-3 bg-white font-main text-[0.9rem] outline-none resize-none focus:border-primary"></textarea>

            <label class="inline-flex items-center gap-2 text-[0.8rem] text-muted cursor-pointer">
                <input type="checkbox" data-quote-reference-toggle class="w-4 h-4 accent-primary cursor-pointer">
                Joindre un fichier de référence (optionnel)
            </label>

            <div data-quote-reference-field class="hidden">
                <input type="file" name="reference" accept="image/jpeg,image/png,image/webp" class="text-[0.82rem] text-muted">
            </div>

            <div class="text-center mt-2">
                <button type="submit" class="btn btn--primary px-10">Envoyer ma demande</button>
            </div>
        </form>

        <img src="/assets/images/decor/aquarelle.png" alt="" class="hidden min-[560px]:block absolute -bottom-8 -right-10 w-[150px] h-auto pointer-events-none select-none -z-10">
    </div>
</dialog>
