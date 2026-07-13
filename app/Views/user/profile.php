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
            <div class="font-cursive text-[1.9rem] font-semibold text-ink"><?= $subscription !== null ? htmlspecialchars($subscription['plan_name']) : 'Aucun' ?></div>
        </div>
    </div>
<?php endif; ?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm mb-6">
    <h2 class="text-base font-semibold mb-5 text-ink">Informations</h2>

    <a href="/profile/payment-methods" class="inline-flex items-center gap-2 text-[0.85rem] text-primary font-medium no-underline hover:underline mb-5">💳 Gérer mes moyens de paiement</a>

    <form method="POST" action="/profile" enctype="multipart/form-data" class="flex flex-col gap-5 max-w-[480px]">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div>
            <label for="username" class="block font-semibold text-[0.9rem] mb-2">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['username'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['username']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block font-semibold text-[0.9rem] mb-2">Avatar</label>
            <div class="flex items-center gap-4">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="w-16 h-16 rounded-full object-cover border border-border">
                <?php else: ?>
                    <img src="/assets/images/icones/new-user.png" alt="Avatar" class="w-16 h-16 rounded-full object-cover border border-border">
                <?php endif; ?>

                <label class="btn btn--primary cursor-pointer">
                    Choisir un fichier
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden">
                </label>
            </div>
            <?php if (isset($errors['avatar'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['avatar']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <form method="POST" action="/profile/password" class="flex flex-col gap-5 max-w-[480px]">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div>
            <label for="current_password" class="block font-semibold text-[0.9rem] mb-2">Mot de passe actuel</label>
            <input type="password" id="current_password" name="current_password" required
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['current_password'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['current_password']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="new_password" class="block font-semibold text-[0.9rem] mb-2">Nouveau mot de passe</label>
            <input type="password" id="new_password" name="new_password" required
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
        </div>

        <div>
            <label for="new_password_confirm" class="block font-semibold text-[0.9rem] mb-2">Confirmer le mot de passe</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm" required
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['new_password'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['new_password']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>
