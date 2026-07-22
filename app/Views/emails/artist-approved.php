<?php
ob_start();
?>
<div style="text-align:center; margin-bottom:20px;">
    <img src="cid:email-illustration" width="200" alt="" style="width:200px; max-width:60%; height:auto; display:inline-block;">
</div>

<h2 style="font-family:Georgia, serif; font-size:22px; color:#a89a82; text-align:center; margin:0 0 16px;">
    Vous êtes maintenant artiste sur Toile !
</h2>

<p style="text-align:center; margin:0 0 8px;">Bonjour <?= htmlspecialchars($username) ?>,</p>
<p style="text-align:center; margin:0 0 20px;">
    Votre demande pour devenir artiste a été acceptée. Choisissez votre formule d'abonnement pour commencer à créer votre boutique et proposer vos prestations.
</p>

<div style="text-align:center; margin:24px 0;">
    <a href="<?= htmlspecialchars($_ENV['APP_URL'] ?? 'http://toile.test') ?>/my-subscription" style="display:inline-block; padding:12px 32px; background:#7a9e7e; color:#ffffff; text-decoration:none; border-radius:999px; font-weight:bold; font-size:14px;">
        Choisir mon abonnement
    </a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
