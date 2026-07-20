<?php
ob_start();
?>
<h2>Félicitations, tu es maintenant artiste sur Toile ! 🎉</h2>

<p>Bonjour <?= htmlspecialchars($username) ?>,</p>

<p>Ta demande pour devenir artiste sur Toile a été <strong>acceptée</strong>. Choisis ta formule d'abonnement pour commencer à créer ta boutique et proposer tes prestations.</p>

<a href="<?= $_ENV['APP_URL'] ?>/my-subscription" class="btn">
    Choisir mon abonnement
</a>

<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
