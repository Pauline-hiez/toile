<?php
ob_start();
?>
<h2>Le tarif de ton abonnement évolue</h2>

<p>Bonjour <?= htmlspecialchars($username) ?>,</p>

<p>Le prix de la formule <strong><?= htmlspecialchars($planName) ?></strong> évolue :</p>

<p style="font-size: 1.2rem; font-weight: bold; color: #6f42c1;">
    <?= number_format($oldPrice / 100, 2) ?> € → <?= number_format($newPrice / 100, 2) ?> € / mois
</p>

<p>
    Ce nouveau tarif s'appliquera à partir de ton prochain renouvellement, le <?= htmlspecialchars($renewalDate) ?>.
    Tu peux changer de formule ou repasser sur l'offre gratuite à tout moment avant cette date.
</p>

<a href="<?= $_ENV['APP_URL'] ?>/my-subscription" class="btn">
    Gérer mon abonnement
</a>

<p><strong>L'équipe Toile</strong></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
