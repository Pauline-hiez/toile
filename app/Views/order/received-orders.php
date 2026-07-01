<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Commandes reçues — Toile</title>
</head>

<body>
    <h1>Commandes reçues</h1>

    <?php if (empty($orders)): ?>
        <p>Tu n'as pas encore reçu de commande.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($orders as $order): ?>
                <li>
                    <a href="/commandes/<?= $order['id'] ?>">
                        <strong><?= htmlspecialchars($order['title']) ?></strong>
                    </a>
                    — par <?= htmlspecialchars($order['client_name']) ?>
                    — <?= htmlspecialchars($order['status']) ?>
                    — <?= number_format($order['total_price'] / 100, 2) ?> €
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="/">Retour à l'accueil</a></p>
</body>

</html>