<?php

ob_start();
?>
<h2>Nouveau message de contact</h2>

<p><strong>Nom :</strong> <?= htmlspecialchars($name) ?></p>
<p><strong>Email :</strong> <?= htmlspecialchars($email) ?></p>
<p><strong>Sujet :</strong> <?= htmlspecialchars($subject) ?></p>

<p><strong>Message :</strong></p>
<p><?= nl2br(htmlspecialchars($message)) ?></p>
<?php
$content = ob_get_clean();
require __DIR__ . '/base.php';
