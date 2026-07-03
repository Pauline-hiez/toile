<?php
ob_start();
?>
<h2>Félicitations, tu es maintenant artiste sur Toile ! 🎉</h2>

<p>Bonjour <?= htmlspecialchars($username) ?>,</p>

<p>Ta demande pour devenir artiste sur Toile a été <strong>acceptée</strong>. Tu peux dès maintenant créer ta boutique et proposer tes prestations.</p>

<a href="<?= $_ENV['APP_URL'] ?>/my-shop" class="btn">
    Créer ma boutique
</a>

<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
