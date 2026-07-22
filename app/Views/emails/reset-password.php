<?php
ob_start();
?>
<div style="text-align:center; margin-bottom:20px;">
    <img src="cid:email-illustration" width="180" alt="" style="width:180px; max-width:55%; height:auto; display:inline-block;">
</div>

<h2 style="font-family:Georgia, serif; font-size:22px; color:#a89a82; text-align:center; margin:0 0 16px;">Réinitialisez votre mot de passe</h2>

<p style="text-align:center; margin:0 0 8px;">Bonjour <?= htmlspecialchars($username) ?>,</p>
<p style="text-align:center; margin:0 0 20px;">
    Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.
</p>

<div style="text-align:center; margin:24px 0;">
    <a href="<?= htmlspecialchars($resetLink) ?>" style="display:inline-block; padding:12px 32px; background:#7a9e7e; color:#ffffff; text-decoration:none; border-radius:999px; font-weight:bold; font-size:14px;">
        Réinitialiser mon mot de passe
    </a>
</div>

<p style="text-align:center; font-size:13px; color:#9a9483; margin:0 0 4px;">Le lien expirera dans 1 heure.</p>
<p style="text-align:center; font-size:13px; color:#9a9483; margin:0;">
    Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
