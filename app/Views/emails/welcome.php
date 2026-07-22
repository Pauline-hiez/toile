<?php

ob_start();
?>
<div style="text-align:center; margin-bottom:20px;">
    <img src="cid:email-illustration" width="220" alt="" style="width:220px; max-width:70%; height:auto; display:inline-block;">
</div>

<h2 style="font-family:Georgia, serif; font-size:24px; color:#a89a82; text-align:center; margin:0 0 16px;">Bienvenue sur Toile !</h2>

<p style="text-align:center; margin:0 0 20px;">
    Bonjour <?= htmlspecialchars($username) ?>, nous sommes ravis de vous accueillir dans notre communauté d'artistes et de passionnés d'art.
</p>

<div style="text-align:center; margin:24px 0;">
    <a href="<?= htmlspecialchars($_ENV['APP_URL'] ?? 'http://toile.test') ?>/boutiques" style="display:inline-block; padding:12px 32px; background:#7a9e7e; color:#ffffff; text-decoration:none; border-radius:999px; font-weight:bold; font-size:14px;">
        Découvrir les artistes
    </a>
</div>

<p style="text-align:center; font-style:italic; color:#9a9483; margin:0;">L'art commence ici.</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
