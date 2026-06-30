<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
</head>

<body>
    <h1>Hello Toile 🎨</h1>
    <p>Le squelette MVC fonctionne.</p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Tu es connecté (id utilisateur : <?= htmlspecialchars($_SESSION['user_id']) ?>).</p>
        <a href="/logout">Se déconnecter</a>
    <?php else: ?>
        <a href="/login">Se connecter</a>
        |
        <a href="/register">S'inscrire</a>
    <?php endif; ?>
</body>

</html>