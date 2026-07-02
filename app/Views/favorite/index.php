<h1>Mes favoris</h1>

<?php if (empty($shops)): ?>
    <p>Tu n'as pas encore de boutique en favori.</p>
    <p><a href="/boutiques">Découvrir les artistes</a></p>
<?php else: ?>
    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
        <?php foreach ($shops as $shop): ?>
            <div style="border: 1px solid #ccc; padding: 1rem; width: 250px;">
                <h3>
                    <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>">
                        <?= htmlspecialchars($shop['name']) ?>
                    </a>
                </h3>
                <?php if (!empty($shop['bio'])): ?>
                    <p><?= htmlspecialchars(mb_substr($shop['bio'], 0, 80)) ?>...</p>
                <?php endif; ?>
                <form method="POST" action="/favoris/toggle/<?= $shop['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <button type="submit">❤️ Retirer des favoris</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p><a href="/">Retour à l'accueil</a></p>