<?php
ob_start();
?>
<h2>Information de paiement</h2>

<p>Bonjour <?= htmlspecialchars($username) ?>,</p>

<p><?= htmlspecialchars($message) ?></p>

<p>
    <strong>Commande :</strong> <?= htmlspecialchars($orderTitle) ?><br>
    <strong>Montant :</strong> <?= number_format($amount / 100, 2) ?> €
</p>

<a href="<?= $_ENV['APP_URL'] ?>/commandes/<?= $orderId ?>" class="btn">
    Voir ma commande
</a>

<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
