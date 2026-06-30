<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Inscription — Toile</title>
</head>

<body>
    <h1>Créer un compte</h1>

    <form method="POST" action="/register">
        <div>
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                required>
            <?php if (isset($errors['email'])): ?>
                <p style="color: red;"><?= htmlspecialchars($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="username">Nom d'utilisateur</label>
            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                required>
            <?php if (isset($errors['username'])): ?>
                <p style="color: red;"><?= htmlspecialchars($errors['username']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
            <?php if (isset($errors['password'])): ?>
                <p style="color: red;"><?= htmlspecialchars($errors['password']) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit">S'inscrire</button>
    </form>
</body>

</html>