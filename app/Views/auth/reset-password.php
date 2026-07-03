<h1>Nouveau mot de passe</h1>

<?php if ($error !== null): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($token !== null): ?>
    <form method="POST" action="/reset-password">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div>
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>

        <button type="submit">Changer mon mot de passe</button>
    </form>
<?php endif; ?>

<p><a href="/login">← Retour à la connexion</a></p>