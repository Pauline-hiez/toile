<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mes prestations — Toile</title>
</head>

<body>
    <h1>Mes prestations</h1>

    <p><a href="/my-services/create">+ Ajouter une prestation</a></p>

    <?php if (empty($services)): ?>
        <p>Tu n'as pas encore de prestation. Crée ta première prestation !</p>
    <?php else: ?>
        <ul>
            <?php foreach ($services as $service): ?>
                <li>
                    <strong><?= htmlspecialchars($service['title']) ?></strong>
                    — <?= number_format($service['base_price'] / 100, 2) ?> €
                    — <?= $service['is_active'] ? 'Active' : 'Inactive' ?>

                    <a href="/my-services/<?= $service['id'] ?>/edit">Modifier</a>

                    <form method="POST" action="/my-services/<?= $service['id'] ?>/delete" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                        <button type="submit" onclick="return confirm('Supprimer cette prestation ?');">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="/my-shop">Retour à ma boutique</a></p>
</body>

</html>