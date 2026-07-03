<?php
ob_start();
?>
<h2>Mise à jour de ta commande #<?= $orderId ?></h2>

<p>Bonjour <?= htmlspecialchars($username) ?>,</p>

<p>Le statut de ta commande <strong>"<?= htmlspecialchars($orderTitle) ?>"</strong> a été mis à jour :</p>

<p style="font-size: 1.2rem; font-weight: bold; color: #6f42c1;">
    <?= htmlspecialchars($statusLabel) ?>
</p>

<a href="<?= $_ENV['APP_URL'] ?>/commandes/<?= $orderId ?>" class="btn">
    Voir ma commande
</a>

<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
