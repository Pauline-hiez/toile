<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir UserController::showProfile()/updateProfile()/updatePassword()).
 *
 * @var array $user
 * @var array<string, string> $errors
 * @var string|null $success
 * @var bool $isArtist
 * @var array|null $subscription Abonnement actif de la boutique (si $isArtist).
 */
$pageTitle = 'Mon profil — Toile';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<?php if ($success !== null): ?>
    <div class="bg-success-bg border border-success/25 text-success rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($isArtist): ?>
    <div class="grid grid-cols-1 min-[481px]:grid-cols-2 gap-4 mb-8">
        <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
            <div class="text-[0.85rem] text-muted font-medium mb-2">Inscrit depuis le</div>
            <div class="font-cursive text-[1.9rem] font-semibold text-ink"><?= \App\Core\FrenchDate::format('dd/MM/y', $user['created_at']) ?></div>
        </div>

        <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
            <div class="text-[0.85rem] text-muted font-medium mb-2">Abonnement</div>
            <div class="font-cursive text-[1.9rem] font-semibold text-ink mb-2"><?= $subscription !== null ? htmlspecialchars($subscription['plan_name']) : 'Aucun' ?></div>
            <a href="/my-subscription" class="text-[0.8rem] text-primary font-medium no-underline hover:underline">Gérer mon abonnement →</a>
        </div>
    </div>
<?php endif; ?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm mb-6 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-base font-semibold text-ink mb-1">Moyens de paiement</h2>
        <p class="text-[0.85rem] text-muted">Gère les cartes enregistrées pour tes commandes.</p>
    </div>
    <a href="/profile/payment-methods" class="btn btn--outline whitespace-nowrap">💳 Gérer mes moyens de paiement</a>
</div>

<div class="grid grid-cols-1 min-[901px]:grid-cols-2 gap-6 mb-6 items-start">
<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <h2 class="text-base font-semibold mb-5 text-ink">Informations</h2>

    <form method="POST" action="/profile" enctype="multipart/form-data" class="flex flex-col gap-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div>
            <label for="username" class="block font-semibold text-[0.9rem] mb-2">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['username'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['username']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block font-semibold text-[0.9rem] mb-2">Avatar</label>
            <div class="flex items-center gap-4">
                <img id="avatarPreviewImg"
                    src="<?= !empty($user['avatar']) ? '/uploads/avatars/' . htmlspecialchars($user['avatar']) : '/assets/images/icones/new-user.png' ?>"
                    alt="Avatar"
                    class="w-36 aspect-[463/431] object-cover <?= !empty($user['avatar']) ? 'avatar-shape' : 'rounded-full border border-border' ?>">

                <label class="btn btn--primary cursor-pointer">
                    Choisir un fichier
                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden">
                </label>
            </div>
            <?php if (isset($errors['avatar'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['avatar']) ?></p>
            <?php endif; ?>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <h2 class="text-base font-semibold mb-5 text-ink">Mot de passe</h2>

    <form method="POST" action="/profile/password" class="flex flex-col gap-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div>
            <label for="current_password" class="block font-semibold text-[0.9rem] mb-2">Mot de passe actuel</label>
            <input type="password" id="current_password" name="current_password" required
                class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['current_password'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['current_password']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="new_password" class="block font-semibold text-[0.9rem] mb-2">Nouveau mot de passe</label>
            <input type="password" id="new_password" name="new_password" required
                class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
        </div>

        <div>
            <label for="new_password_confirm" class="block font-semibold text-[0.9rem] mb-2">Confirmer le mot de passe</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm" required
                class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['new_password'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['new_password']) ?></p>
            <?php endif; ?>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>
</div>

<div id="avatarCropModal" class="hidden fixed inset-0 z-[200] bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-md p-5 max-w-[440px] w-full shadow-sm">
        <h3 class="text-base font-semibold text-ink mb-1">Recadre ton avatar</h3>
        <p class="text-[0.8rem] text-muted mb-4">Déplace et zoome ta photo pour qu'elle s'adapte bien à la découpe (aperçu en filigrane).</p>

        <div id="avatarCropWrapper" class="relative w-full aspect-[463/431] bg-bg overflow-hidden mb-4">
            <img id="avatarCropImage" src="" alt="" class="block max-w-full">
            <img src="/assets/images/decor/crop-avatar.png" alt="" class="absolute inset-0 w-full h-full pointer-events-none opacity-90 z-10">
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" id="avatarCropCancel" class="btn btn--outline">Annuler</button>
            <button type="button" id="avatarCropConfirm" class="btn btn--primary">Valider le cadrage</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var fileInput = document.getElementById('avatarInput');
        var modal = document.getElementById('avatarCropModal');
        var cropImage = document.getElementById('avatarCropImage');
        var confirmBtn = document.getElementById('avatarCropConfirm');
        var cancelBtn = document.getElementById('avatarCropCancel');
        var previewImg = document.getElementById('avatarPreviewImg');
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
                    aspectRatio: 463 / 431,
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

            cropper.getCroppedCanvas({ width: 926, height: 862 }).toBlob(function (blob) {
                var croppedFile = new File([blob], 'avatar.png', { type: 'image/png' });
                var dt = new DataTransfer();
                dt.items.add(croppedFile);
                fileInput.files = dt.files;

                previewImg.src = URL.createObjectURL(blob);
                previewImg.classList.remove('rounded-full', 'border', 'border-border');
                previewImg.classList.add('avatar-shape');

                closeModal();
            }, 'image/png');
        });
    })();
</script>
