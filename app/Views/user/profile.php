<?php $pageTitle = 'Mon profil — Toile'; ?>

<h1>Mon profil</h1>

<?php if ($success !== null): ?>
    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if (!empty($user['avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" width="100">
<?php endif; ?>

<h2>Informations</h2>
<form method="POST" action="/profile" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

    <div>
        <label for="username">Nom d'utilisateur</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        <?php if (isset($errors['username'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['username']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="avatar">Avatar</label>
        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp">
        <?php if (isset($errors['avatar'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['avatar']) ?></p>
        <?php endif; ?>
    </div>

    <button type="submit">Enregistrer</button>
</form>

<h2>Changer de mot de passe</h2>
<form method="POST" action="/profile/password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

    <div>
        <label for="current_password">Mot de passe actuel</label>
        <input type="password" id="current_password" name="current_password" required>
        <?php if (isset($errors['current_password'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['current_password']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="new_password">Nouveau mot de passe</label>
        <input type="password" id="new_password" name="new_password" required>
    </div>

    <div>
        <label for="new_password_confirm">Confirmer le nouveau mot de passe</label>
        <input type="password" id="new_password_confirm" name="new_password_confirm" required>
        <?php if (isset($errors['new_password'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['new_password']) ?></p>
        <?php endif; ?>
    </div>

    <button type="submit">Changer le mot de passe</button>
</form>
