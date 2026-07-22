<?php
/**
 * Modale de proposition d'un nouveau style/type, incluse uniquement dans
 * artist/shop.php (nécessite $errors, $categoryRequests en scope). Le
 * bouton "+" (data-category-request-open) l'ouvre via
 * category-request-modal.js — même convention que become-artist-modal.php.
 *
 * @var array<string, string> $errors Injecté par le scope de artist/shop.php.
 * @var array $categoryRequests
 */
?>
<dialog id="categoryRequestModal" class="auth-modal"<?= isset($errors['category_request']) ? ' data-category-request-reopen' : '' ?>>
    <button type="button" class="absolute top-3 right-4 text-title text-3xl leading-none z-20" data-category-request-close aria-label="Fermer">&times;</button>

    <div class="relative bg-bg rounded-2xl border border-border shadow-lg px-6 py-8 min-[481px]:px-10 min-[481px]:py-10 max-h-[85vh] overflow-y-auto">
        <h2 class="font-title text-[1.6rem] min-[481px]:text-[1.9rem] text-title font-semibold text-center leading-none mb-2">Proposer une catégorie</h2>
        <p class="text-[0.85rem] text-muted text-center mb-5">Tu ne trouves pas ton style ou ta spécialité&nbsp;? Propose-le·a à l'admin. Un visuel est requis pour un nouveau style.</p>

        <form method="POST" action="/my-shop/category-requests" enctype="multipart/form-data" class="flex flex-col gap-4 max-w-[380px] mx-auto text-left">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <div>
                <label for="categoryRequestType" class="block font-semibold text-[0.9rem] mb-2">Catégorie</label>
                <select id="categoryRequestType" name="category_type" data-category-request-type
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
                    <option value="style">Nouveau style</option>
                    <option value="type">Nouveau type / spécialité</option>
                </select>
            </div>

            <div>
                <label for="categoryRequestName" class="block font-semibold text-[0.9rem] mb-2">Nom proposé</label>
                <input type="text" id="categoryRequestName" name="name" required
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
            </div>

            <div data-category-request-image>
                <label for="categoryRequestImage" class="block font-semibold text-[0.9rem] mb-2">Visuel</label>
                <input type="file" id="categoryRequestImage" name="image" accept="image/png,image/jpeg,image/webp" class="w-full text-[0.85rem]">
                <img id="categoryRequestImagePreview" src="" alt="" class="hidden w-24 h-24 object-cover rounded-md border border-border mt-2">
            </div>

            <?php if (isset($errors['category_request'])): ?>
                <p class="text-danger text-[0.8rem]"><?= htmlspecialchars($errors['category_request']) ?></p>
            <?php endif; ?>

            <div class="text-center mt-1">
                <button type="submit" class="btn btn--primary px-10">Envoyer la proposition</button>
            </div>
        </form>

        <?php if (!empty($categoryRequests)): ?>
            <div class="max-w-[380px] mx-auto mt-6 pt-5 border-t border-border">
                <p class="text-[0.82rem] font-semibold text-ink mb-2">Tes propositions</p>
                <?php
                $categoryRequestStatusBadge = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ];
                $categoryRequestStatusLabel = [
                    'pending' => 'En attente',
                    'approved' => 'Acceptée',
                    'rejected' => 'Refusée',
                ];
                ?>
                <div class="flex flex-col gap-2">
                    <?php foreach ($categoryRequests as $categoryRequest): ?>
                        <div class="flex items-center justify-between gap-3 text-[0.85rem]">
                            <span class="text-ink"><?= htmlspecialchars(ucfirst($categoryRequest['name'])) ?> <span class="text-muted">(<?= $categoryRequest['category_type'] === 'style' ? 'style' : 'type' ?>)</span></span>
                            <span class="<?= \App\Core\Badge::classes($categoryRequestStatusBadge[$categoryRequest['status']]) ?>"><?= $categoryRequestStatusLabel[$categoryRequest['status']] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</dialog>

<dialog id="categoryRequestCropModal" class="auth-modal">
    <div class="bg-white rounded-md p-5 shadow-sm">
        <h3 class="text-base font-semibold text-ink mb-1">Recadre ton visuel</h3>
        <p class="text-[0.8rem] text-muted mb-4">Déplace et zoome ton image pour bien centrer le sujet — c'est ce cadrage qui sera utilisé sur la tuile.</p>

        <div class="relative w-full h-[420px] bg-bg overflow-hidden mb-4">
            <img id="categoryRequestCropImage" src="" alt="" class="block max-w-full">
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" id="categoryRequestCropCancel" class="btn btn--outline">Annuler</button>
            <button type="button" id="categoryRequestCropConfirm" class="btn btn--primary">Valider le cadrage</button>
        </div>
    </div>
</dialog>

<script src="/assets/js/image-crop.js"></script>
<script>
    setupImageCrop({
        fileInputId: 'categoryRequestImage',
        modalId: 'categoryRequestCropModal',
        imageId: 'categoryRequestCropImage',
        confirmId: 'categoryRequestCropConfirm',
        cancelId: 'categoryRequestCropCancel',
        previewId: 'categoryRequestImagePreview',
        aspectRatio: 4 / 3,
        outputWidth: 800,
        outputHeight: 600,
    });
</script>
