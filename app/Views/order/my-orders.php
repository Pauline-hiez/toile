<?php $pageTitle = 'Mes commandes — Toile'; ?>

<h1>Mes commandes</h1>

<?php if (empty($orders)): ?>
    <p>Tu n'as pas encore passé de commande.</p>
<?php else: ?>
    <ul>
        <?php foreach ($orders as $order): ?>
            <li>
                <a href="/commandes/<?= $order['id'] ?>">
                    <strong><?= htmlspecialchars($order['title']) ?></strong>
                </a>
                — <?= htmlspecialchars($order['shop_name']) ?>
                — <?= htmlspecialchars($order['status']) ?>
                — <?= number_format($order['total_price'] / 100, 2) ?> €
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
