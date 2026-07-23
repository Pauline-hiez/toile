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

// Sous le layout artiste, le conteneur (largeur + padding) et le titre de
// page sont déjà fournis par le layout — on ne garde ici que le centrage.
$wrapClass = $isArtist
    ? 'max-w-[700px] mx-auto relative'
    : 'max-w-[700px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10 relative';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<div class="<?= $wrapClass ?>">
    <?php if (!$isArtist): ?>
        <img src="/assets/images/decor/tache7.png" alt="" style="width: 240px; top: 2%; right: -78px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
        <img src="/assets/images/decor/plante4.png" alt="" style="width: 145px; top: 5%; right: -42px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">
        <img src="/assets/images/decor/tache7.png" alt="" style="width: 220px; bottom: 4%; left: -74px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10 -scale-x-100">
        <img src="/assets/images/decor/plante9.png" alt="" style="width: 135px; bottom: 7%; left: -38px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">

        <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Mon profil</h1>
        <p class="text-center text-muted text-[0.9rem] mb-10">Gère tes informations personnelles et ta sécurité.</p>
    <?php endif; ?>

    <?php if ($success !== null): ?>
        <div class="bg-success-bg border border-success/25 text-success rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($isArtist): ?>
        <div class="grid grid-cols-1 min-[481px]:grid-cols-2 gap-4 mb-6">
            <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm">
                <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= \App\Core\FrenchDate::format('dd/MM/y', $user['created_at']) ?></div>
                <div class="font-cursive text-[0.9rem] text-success">Inscrit depuis le</div>
            </div>

            <div class="bg-white border border-border rounded-2xl p-3 text-center shadow-sm">
                <div class="font-cursive text-[1.7rem] font-bold text-success leading-none mb-1"><?= $subscription !== null ? htmlspecialchars($subscription['plan_name']) : 'Aucun' ?></div>
                <div class="font-cursive text-[0.9rem] text-success mb-2">Abonnement</div>
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

    <form method="POST" action="/profile" enctype="multipart/form-data" class="flex flex-col gap-6 mb-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div class="bg-white border border-border rounded-md p-6 shadow-sm">
            <h2 class="text-base font-semibold mb-5 text-ink">Informations</h2>

            <div class="flex flex-col gap-5">
                <div>
                    <label for="username" class="block font-semibold text-[0.9rem] mb-2">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                        class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                    <?php if (isset($errors['username'])): ?>
                        <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['username']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($user['provider'] === 'credentials'): ?>
                    <div>
                        <label for="email" class="block font-semibold text-[0.9rem] mb-2">Adresse email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                        <?php if (isset($errors['email'])): ?>
                            <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block font-semibold text-[0.9rem] mb-2">Avatar</label>
                    <div class="flex items-center gap-4">
                        <img id="avatarPreviewImg"
                            src="<?= !empty($user['avatar']) ? '/uploads/avatars/' . htmlspecialchars($user['avatar']) : '/uploads/avatars/default.png' ?>"
                            alt="Avatar"
                            class="w-20 aspect-square object-cover rounded-full border border-border">

                        <label class="btn btn--outline cursor-pointer">
                            Choisir un fichier
                            <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </label>
                    </div>
                    <?php if (isset($errors['avatar'])): ?>
                        <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['avatar']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white border border-border rounded-md p-6 shadow-sm">
            <h2 class="text-base font-semibold mb-1 text-ink">Bio &amp; adresse</h2>
            <p class="text-[0.8rem] text-muted mb-5">La bio est visible sur ton profil public ; l'adresse sert à préremplir tes commandes.</p>

            <div class="flex flex-col gap-5">
                <div>
                    <label for="bio" class="block font-semibold text-[0.9rem] mb-2">Bio</label>
                    <textarea id="bio" name="bio" rows="3"
                        placeholder="Une courte présentation, visible sur ton profil public..."
                        class="w-full border border-border rounded-2xl px-4 py-3 font-main text-[0.9rem] outline-none resize-none focus:border-primary"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-[0.9rem] mb-2">Adresse</label>
                    <div class="flex flex-col gap-2">
                        <input type="text" name="address_line1" value="<?= htmlspecialchars($user['address_line1'] ?? '') ?>" placeholder="Adresse"
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                        <input type="text" name="address_line2" value="<?= htmlspecialchars($user['address_line2'] ?? '') ?>" placeholder="Complément d'adresse (optionnel)"
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" placeholder="Code postal"
                                class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                            <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="Ville"
                                class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                        </div>
                        <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>" placeholder="Pays"
                            class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
        </div>
    </form>

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

<dialog id="avatarCropModal" class="auth-modal">
    <div class="bg-white rounded-md p-5 shadow-sm">
        <h3 class="text-base font-semibold text-ink mb-1">Recadre ton avatar</h3>
        <p class="text-[0.8rem] text-muted mb-4">Déplace et zoome ton image pour bien centrer le sujet — c'est ce cadrage qui sera utilisé.</p>

        <div class="relative w-full h-[420px] bg-bg overflow-hidden mb-4">
            <img id="avatarCropImage" src="" alt="" class="block max-w-full">
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" id="avatarCropCancel" class="btn btn--outline">Annuler</button>
            <button type="button" id="avatarCropConfirm" class="btn btn--primary">Valider le cadrage</button>
        </div>
    </div>
</dialog>

<script src="/assets/js/image-crop.js"></script>
<script>
    // Avatar carré (aspectRatio 1). Le recadrage met à jour l'aperçu
    // (#avatarPreviewImg) et remplace le fichier envoyé par la version
    // recadrée — même système que les bannières / visuels de style.
    setupImageCrop({
        fileInputId: 'avatarInput',
        modalId: 'avatarCropModal',
        imageId: 'avatarCropImage',
        confirmId: 'avatarCropConfirm',
        cancelId: 'avatarCropCancel',
        previewId: 'avatarPreviewImg',
        aspectRatio: 1,
        outputWidth: 400,
        outputHeight: 400,
    });
</script>
