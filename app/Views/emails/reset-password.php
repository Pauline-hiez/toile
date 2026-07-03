<?php
ob_start();
?>
<h2>Réinitialisation de ton mot de passe</h2>

<p>Bonjour <?= htmlspecialchars($username) ?>,</p>

<p>Tu as demandé à réinitialiser ton mot de passe sur Toile. Clique sur le bouton ci-dessous pour en choisir un nouveau :</p>

<a href="<?= htmlspecialchars($resetLink) ?>" class="btn">
    Réinitialiser mon mot de passe
</a>

<p>Ce lien est valable <strong>1 heure</strong>. Après ce délai, tu devras refaire une demande.</p>

<p>Si tu n'es pas à l'origine de cette demande, ignore simplement cet email — ton mot de passe ne changera pas.</p>

<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
