<?php
ob_start();
?>
<h2>Réponse à ta demande artiste</h2>

<p>Bonjour <?= htmlspecialchars($username) ?>,</p>

<p>Après examen, ta demande pour devenir artiste sur Toile n'a malheureusement pas été retenue pour le moment.</p>

<p>Tu peux soumettre une nouvelle demande ultérieurement depuis ton espace personnel.</p>

<a href="<?= $_ENV['APP_URL'] ?>/become-artist" class="btn">
    Soumettre une nouvelle demande
</a>

<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
