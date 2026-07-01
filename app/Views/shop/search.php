<?php $pageTitle = 'Découvrir les artistes — Toile'; ?>

<h1>Découvrir les artistes</h1>

<form method="GET" action="/boutiques">
    <div>
        <label for="q">Rechercher</label>
        <input type="text" id="q" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Nom de la boutique...">
    </div>

    <div>
        <label for="style">Style</label>
        <select id="style" name="style">
            <option value="">Tous les styles</option>
            <?php foreach ($availableStyles as $style): ?>
                <option value="<?= htmlspecialchars($style) ?>" <?= $filters['style'] === $style ? 'selected' : '' ?>>
                    <?= htmlspecialchars($style) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="min_price">Prix min (€)</label>
        <input type="number" id="min_price" name="min_price" min="0" value="<?= htmlspecialchars($filters['min_price']) ?>">
    </div>

    <div>
        <label for="max_price">Prix max (€)</label>
        <input type="number" id="max_price" name="max_price" min="0" value="<?= htmlspecialchars($filters['max_price']) ?>">
    </div>

    <div>
        <label for="sort">Trier par</label>
        <select id="sort" name="sort">
            <option value="rating" <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Meilleure note</option>
            <option value="price" <?= $filters['sort'] === 'price' ? 'selected' : '' ?>>Prix croissant</option>
        </select>
    </div>

    <button type="submit">Rechercher</button>
</form>

<?php if (empty($shops)): ?>
    <p>Aucune boutique ne correspond à ta recherche.</p>
<?php else: ?>
    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1.5rem;">
        <?php foreach ($shops as $shop): ?>
            <div style="border: 1px solid #ccc; padding: 1rem; width: 250px;">
                <h3><a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>"><?= htmlspecialchars($shop['name']) ?></a></h3>

                <p>
                    <?php if ($shop['avg_rating'] !== null): ?>
                        ⭐ <?= number_format($shop['avg_rating'], 1) ?>
                    <?php else: ?>
                        Pas encore d'avis
                    <?php endif; ?>
                </p>

                <p>
                    <?php if ($shop['min_price'] !== null): ?>
                        À partir de <?= number_format($shop['min_price'] / 100, 2) ?> €
                    <?php else: ?>
                        Aucune prestation disponible
                    <?php endif; ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
