<h1>Mot de passe oublié</h1>

<?php if ($success !== null): ?>
    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($error !== null): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/forgot-password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

    <div>
        <label for="email">Ton adresse email</label>
        <input type="email" id="email" name="email" required>
    </div>

    <button type="submit">Envoyer le lien</button>
</form>

<p><a href="/login">← Retour à la connexion</a></p>