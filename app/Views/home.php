<?php $pageTitle = 'Toile'; ?>

<h1>Bienvenue sur Toile 🎨</h1>
<p>La marketplace de commissions artistiques.</p>

<?php if (isset($_SESSION['user_id'])): ?>
    <p>Tu es connecté (id : <?= htmlspecialchars($_SESSION['user_id']) ?>).</p>
<?php endif; ?>

<?php if (!empty($featuredShops)): ?>
    <h2>🏠 Boutiques de la semaine</h2>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <?php foreach ($featuredShops as $featured): ?>
            <div style="border: 2px solid gold; border-radius: 8px; padding: 1rem; background: #fffbea; min-width: 200px;">
                <a href="/boutiques/<?= htmlspecialchars($featured['shop_slug']) ?>">
                    <strong><?= htmlspecialchars($featured['shop_name']) ?></strong>
                </a>
                <?php if (!empty($featured['shop_bio'])): ?>
                    <p style="font-size: 0.85rem;"><?= htmlspecialchars(mb_substr($featured['shop_bio'], 0, 100)) ?>...</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>