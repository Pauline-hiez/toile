<?php $pageTitle = htmlspecialchars($shop['name']) . ' — Toile'; ?>

<?php if (!empty($shop['banner'])): ?>
    <img src="/uploads/banners/<?= htmlspecialchars($shop['banner']) ?>" alt="Bannière" style="width: 100%; max-height: 250px; object-fit: cover;">
<?php endif; ?>

<h1><?= htmlspecialchars($shop['name']) ?></h1>

<p>
    <?php if ($ratingStats['count'] > 0): ?>
        ⭐ <?= number_format($ratingStats['average'], 1) ?> (<?= $ratingStats['count'] ?> avis)
    <?php else: ?>
        Pas encore d'avis
    <?php endif; ?>
    —
    <?= $shop['is_open'] ? 'Ouverte aux commandes' : 'Fermée aux commandes' ?>
</p>

<?php if (!empty($shop['bio'])): ?>
    <p><?= nl2br(htmlspecialchars($shop['bio'])) ?></p>
<?php endif; ?>

<?php
$styles = $shop['styles'] ? json_decode($shop['styles'], true) : [];
?>
<?php if (!empty($styles)): ?>
    <p>Styles : <?= htmlspecialchars(implode(', ', $styles)) ?></p>
<?php endif; ?>

<h2>Prestations</h2>
<?php if (empty($services)): ?>
    <p>Aucune prestation disponible pour le moment.</p>
<?php else: ?>
    <ul>
        <?php foreach ($services as $service): ?>
            <li>
                <strong><?= htmlspecialchars($service['title']) ?></strong>
                — à partir de <?= number_format($service['base_price'] / 100, 2) ?> €
                — délai estimé : <?= $service['delivery_days'] ?> jours
                <?php if (!empty($service['description'])): ?>
                    <p><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                <?php endif; ?>
                <a href="/commander/<?= $service['id'] ?>">Commander</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Portfolio</h2>
<?php if (empty($portfolioImages)): ?>
    <p>Aucune image de portfolio pour le moment.</p>
<?php else: ?>
    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
        <?php foreach ($portfolioImages as $image): ?>
            <img
                src="/uploads/portfolio/<?= htmlspecialchars($image['filename']) ?>"
                alt="Portfolio"
                width="200">
        <?php endforeach; ?>
    </div>
<?php endif; ?>
