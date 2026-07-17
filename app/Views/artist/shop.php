<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ShopController::manage()/save()).
 *
 * @var array|null $shop
 * @var array<string, string> $errors
 * @var string|null $success
 * @var array{average: float, count: int}|null $ratingStats Null si $shop est null.
 * @var int|null $favoriteCount Null si $shop est null.
 */
$pageTitle = 'Ma boutique — Toile';

// Ratio de la forme découpée (public/assets/images/decor/crop-banniere.png,
// 579×226 après recadrage à sa bounding box) — pilote à la fois le crop
// interactif (Cropper.js) et l'affichage (classe .shop-banner-shape).
$bannerShapeRatio = '579 / 226';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<?php if ($success !== null): ?>
    <div class="bg-success-bg border border-success/25 text-success rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($shop !== null): ?>
    <div class="grid grid-cols-2 min-[721px]:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1">
                <?= $ratingStats['count'] > 0 ? number_format($ratingStats['average'], 1) . ' ⭐' : '—' ?>
            </div>
            <div class="font-cursive text-[0.9rem] text-success"><?= $ratingStats['count'] > 0 ? $ratingStats['count'] . ' avis' : "Pas encore d'avis" ?></div>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= $favoriteCount ?></div>
            <div class="font-cursive text-[0.9rem] text-success">Favoris</div>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <?php if ($shop['plan_selected']): ?>
                <div class="font-cursive text-[1.7rem] font-bold <?= $shop['is_open'] ? 'text-success' : 'text-muted' ?> leading-none mb-1"><?= $shop['is_open'] ? 'Ouverte' : 'Fermée' ?></div>
                <div class="font-cursive text-[0.9rem] text-success mb-2">Statut</div>
                <form method="POST" action="/my-shop/toggle">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <button type="submit" class="text-[0.8rem] text-primary font-medium bg-transparent border-0 cursor-pointer p-0 hover:underline">
                        <?= $shop['is_open'] ? 'Fermer la boutique →' : 'Ouvrir la boutique →' ?>
                    </button>
                </form>
            <?php else: ?>
                <div class="font-cursive text-[1.7rem] font-bold text-muted leading-none mb-1">—</div>
                <div class="font-cursive text-[0.9rem] text-success mb-2">Statut</div>
                <a href="/my-subscription" class="text-[0.8rem] text-primary font-medium no-underline hover:underline">Choisir ma formule →</a>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm max-w-[220px]">
            <div class="font-cursive text-[1.7rem] font-bold <?= $shop['accepts_quotes'] ? 'text-success' : 'text-muted' ?> leading-none mb-1"><?= $shop['accepts_quotes'] ? 'Activés' : 'Désactivés' ?></div>
            <div class="font-cursive text-[0.9rem] text-success mb-2">Devis</div>
            <form method="POST" action="/my-shop/toggle-quotes">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <button type="submit" class="text-[0.8rem] text-primary font-medium bg-transparent border-0 cursor-pointer p-0 hover:underline">
                    <?= $shop['accepts_quotes'] ? 'Désactiver les devis →' : 'Activer les devis →' ?>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($shop !== null && !$shop['plan_selected']): ?>
    <div class="bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-4 mb-6 text-[0.9rem]">
        <strong>Ta boutique n'est pas encore ouverte.</strong>
        Choisis ta formule d'abonnement (gratuite ou payante) pour l'activer.
        <a href="/my-subscription" class="font-semibold underline">Choisir ma formule →</a>
    </div>
<?php endif; ?>

<?php if ($shop === null): ?>
    <p class="text-[0.85rem] text-muted mb-5">Configure ta boutique pour qu'elle apparaisse sur Toile.</p>
<?php endif; ?>

<form method="POST" action="/my-shop" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

    <div class="grid grid-cols-1 min-[901px]:grid-cols-2 gap-6 mb-6 items-start">
        <div class="bg-white border border-border rounded-md p-6 shadow-sm">
            <h2 class="text-base font-semibold text-ink mb-5">Bannière</h2>

            <img id="bannerPreviewImg"
                src="<?= !empty($shop['banner']) ? '/uploads/banners/' . htmlspecialchars($shop['banner']) : '' ?>"
                alt="Bannière"
                class="w-full aspect-[<?= $bannerShapeRatio ?>] object-cover shop-banner-shape mb-3 <?= empty($shop['banner']) ? 'hidden' : '' ?>">

            <label class="btn btn--primary cursor-pointer inline-block">
                <span id="bannerUploadLabel"><?= !empty($shop['banner']) ? 'Modifier ma bannière' : 'Ajouter une bannière' ?></span>
                <input type="file" id="bannerInput" name="banner" accept="image/jpeg,image/png,image/webp" class="hidden">
            </label>
            <p class="text-[0.8rem] text-muted mt-1">Positionne ton image pour qu'elle s'adapte bien à la découpe.</p>
            <?php if (isset($errors['banner'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['banner']) ?></p>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-border rounded-md p-6 shadow-sm">
            <h2 class="text-base font-semibold text-ink mb-5">Informations</h2>

            <div class="flex flex-col gap-5">
                <?php if ($shop !== null): ?>
                    <p class="text-[0.85rem] text-muted -mt-2">URL publique : <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="text-primary hover:underline">/boutiques/<?= htmlspecialchars($shop['slug']) ?></a></p>
                <?php endif; ?>

                <div>
                    <label for="name" class="block font-semibold text-[0.9rem] mb-2">Nom de la boutique</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($shop['name'] ?? '') ?>" required
                        class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                    <?php if (isset($errors['name'])): ?>
                        <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="bio" class="block font-semibold text-[0.9rem] mb-2">Description</label>
                    <textarea id="bio" name="bio" rows="4"
                        class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary resize-y"><?= htmlspecialchars($shop['bio'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-border rounded-md p-6 shadow-sm mb-6">
        <h2 class="text-base font-semibold text-ink mb-2">Réseaux sociaux</h2>
        <p class="text-[0.8rem] text-muted mb-5">Affichés sur ta page boutique publique, sous le bouton "Ajouter aux favoris". Laisse vide les réseaux que tu n'utilises pas.</p>

        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-5">
            <div>
                <label for="social_instagram" class="block font-semibold text-[0.9rem] mb-2">Instagram</label>
                <input type="url" id="social_instagram" name="social_instagram" value="<?= htmlspecialchars($shop['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/tonpseudo"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="social_facebook" class="block font-semibold text-[0.9rem] mb-2">Facebook</label>
                <input type="url" id="social_facebook" name="social_facebook" value="<?= htmlspecialchars($shop['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/tapage"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="social_pinterest" class="block font-semibold text-[0.9rem] mb-2">Pinterest</label>
                <input type="url" id="social_pinterest" name="social_pinterest" value="<?= htmlspecialchars($shop['social_pinterest'] ?? '') ?>" placeholder="https://pinterest.com/tonpseudo"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="social_tiktok" class="block font-semibold text-[0.9rem] mb-2">TikTok</label>
                <input type="url" id="social_tiktok" name="social_tiktok" value="<?= htmlspecialchars($shop['social_tiktok'] ?? '') ?>" placeholder="https://tiktok.com/@tonpseudo"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            </div>
        </div>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn--primary">Enregistrer</button>
    </div>
</form>

<div id="bannerCropModal" class="hidden fixed inset-0 z-[200] bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-md p-5 max-w-[640px] w-full shadow-sm">
        <h3 class="text-base font-semibold text-ink mb-1">Recadre ta bannière</h3>
        <p class="text-[0.8rem] text-muted mb-4">Déplace et zoome ton image pour qu'elle s'adapte bien à la découpe (aperçu en filigrane).</p>

        <div id="bannerCropWrapper" class="relative w-full aspect-[<?= $bannerShapeRatio ?>] bg-bg overflow-hidden mb-4">
            <img id="bannerCropImage" src="" alt="" class="block max-w-full">
            <img src="/assets/images/decor/crop-banniere.png" alt="" class="absolute inset-0 w-full h-full pointer-events-none opacity-90 z-10">
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" id="bannerCropCancel" class="btn btn--outline">Annuler</button>
            <button type="button" id="bannerCropConfirm" class="btn btn--primary">Valider le cadrage</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var fileInput = document.getElementById('bannerInput');
        var modal = document.getElementById('bannerCropModal');
        var cropImage = document.getElementById('bannerCropImage');
        var confirmBtn = document.getElementById('bannerCropConfirm');
        var cancelBtn = document.getElementById('bannerCropCancel');
        var previewImg = document.getElementById('bannerPreviewImg');
        var cropper = null;
        var objectUrl = null;

        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            cropImage.src = objectUrl;
            modal.classList.remove('hidden');

            cropImage.onload = function () {
                if (cropper) cropper.destroy();
                cropper = new Cropper(cropImage, {
                    aspectRatio: 579 / 226,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                    background: false,
                });
            };
        });

        function closeModal() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            modal.classList.add('hidden');
        }

        cancelBtn.addEventListener('click', function () {
            fileInput.value = '';
            closeModal();
        });

        confirmBtn.addEventListener('click', function () {
            if (!cropper) return;

            cropper.getCroppedCanvas({ width: 1158, height: 452 }).toBlob(function (blob) {
                var croppedFile = new File([blob], 'banner.png', { type: 'image/png' });
                var dt = new DataTransfer();
                dt.items.add(croppedFile);
                fileInput.files = dt.files;

                previewImg.src = URL.createObjectURL(blob);
                previewImg.classList.remove('hidden');

                closeModal();
            }, 'image/png');
        });
    })();
</script>
