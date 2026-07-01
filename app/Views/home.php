<?php $pageTitle = 'Toile'; ?>

<h1>Bienvenue sur Toile 🎨</h1>
<p>La marketplace de commissions artistiques.</p>

<?php if (isset($_SESSION['user_id'])): ?>
    <p>Tu es connecté (id : <?= htmlspecialchars($_SESSION['user_id']) ?>).</p>
<?php endif; ?>
