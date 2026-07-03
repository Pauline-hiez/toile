<?php

ob_start();
?>
<h2>Bienvenue sur Toile, <?= htmlspecialchars($username) ?> ! 🎨</h2>

<p>Ton compte a bien été créé. Tu peux dès maintenant :</p>

<ul>
    <li>Découvrir les boutiques des artistes</li>
    <li>Passer ta première commande</li>
    <li>Demander à devenir artiste toi-même</li>
</ul>

<a href="<?= $_ENV['APP_URL'] ?? 'http://toile.test' ?>" class="btn">
    Accéder à Toile
</a>

<p>À bientôt !</p>
<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
