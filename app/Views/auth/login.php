<?php $pageTitle = 'Connexion — Toile'; ?>

<?php if (isset($_GET['banned'])): ?>
    <p style="color: red;">
        Ton compte a été suspendu. Contacte l'administration pour plus d'informations.
    </p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p style="color: red;">
        La connexion avec Google a échoué. Réessaie ou utilise ton email/mot de passe.
    </p>
<?php endif; ?>

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
    <hr>
    <a href="/auth/google">
        <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png" alt="Se connecter avec Google">
    </a>
</form>

<p><a href="/forgot-password">Mot de passe oublié ?</a></p>

<?php if (isset($_GET['reset'])): ?>
    <p style="color: green;">Mot de passe modifié avec succès. Tu peux te connecter.</p>
<?php endif; ?>
<p><a href="/register">Créer un compte</a></p>