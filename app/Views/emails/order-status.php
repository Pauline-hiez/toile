<?php
ob_start();
?>
<div style="text-align:center; margin-bottom:20px;">
    <img src="cid:email-illustration" width="200" alt="" style="width:200px; max-width:60%; height:auto; display:inline-block;">
</div>

<h2 style="font-family:Georgia, serif; font-size:22px; color:#a89a82; text-align:center; margin:0 0 16px;">
    Commande #<?= (int) $orderId ?>
</h2>

<p style="text-align:center; margin:0 0 8px;">Bonjour <?= htmlspecialchars($username) ?>,</p>
<p style="text-align:center; margin:0 0 4px;">
    Le statut de votre commande <strong><?= htmlspecialchars($orderTitle) ?></strong> a été mis à jour :
</p>

<p style="text-align:center; font-size:18px; font-weight:bold; color:#7a9e7e; margin:8px 0 24px;">
    <?= htmlspecialchars($statusLabel) ?>
</p>

<div style="text-align:center;">
    <a href="<?= htmlspecialchars($_ENV['APP_URL'] ?? 'http://toile.test') ?>/commandes/<?= (int) $orderId ?>" style="display:inline-block; padding:12px 32px; background:#7a9e7e; color:#ffffff; text-decoration:none; border-radius:999px; font-weight:bold; font-size:14px;">
        Voir ma commande
    </a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
