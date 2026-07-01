<?php $pageTitle = 'Connexion — Toile'; ?>

<h1>Se connecter</h1>

<?php if ($error !== null): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/login">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>

    <div>
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>
    </div>

    <button type="submit">Se connecter</button>
</form>

<p><a href="/register">Créer un compte</a></p>
