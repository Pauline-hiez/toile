<?php $pageTitle = 'Toile'; ?>

<h1>Bienvenue sur Toile 🎨</h1>
<p>La marketplace de commissions artistiques.</p>

<?php if (isset($_SESSION['user_id'])): ?>
    <p>Tu es connecté (id : <?= htmlspecialchars($_SESSION['user_id']) ?>).</p>
<?php endif; ?>

<?php if (!empty($featuredShop)): ?>
    <div style="border: 2px solid gold; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; background: #fffbea;">
        <p style="font-size: 0.85rem; color: #b7791f;">⭐ Boutique mise en avant aujourd'hui</p>
        <h2>
            <a href="/boutiques/<?= htmlspecialchars($featuredShop['shop_slug']) ?>">
                <?= htmlspecialchars($featuredShop['shop_name']) ?>
            </a>
        </h2>
        <?php if (!empty($featuredShop['shop_bio'])): ?>
            <p><?= htmlspecialchars(mb_substr($featuredShop['shop_bio'], 0, 150)) ?>...</p>
        <?php endif; ?>
    </div>
<?php endif; ?>