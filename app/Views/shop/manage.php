<?php $pageTitle = 'Ma boutique — Toile'; ?>

<h1>Ma boutique</h1>

<?php if ($success !== null): ?>
    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php
$currentStyles = $shop !== null && $shop['styles']
    ? json_decode($shop['styles'], true)
    : [];

$availableStyles = ['anime', 'réaliste', 'chibi', 'pixel art', 'concept art'];
?>

<?php if ($shop !== null): ?>
    <p>URL publique de ta boutique : <code>/boutiques/<?= htmlspecialchars($shop['slug']) ?></code></p>

    <p>
        Statut actuel :
        <strong><?= $shop['is_open'] ? 'Ouverte aux commandes' : 'Fermée aux commandes' ?></strong>
    </p>

    <form method="POST" action="/my-shop/toggle" style="display: inline;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
        <button type="submit" class="btn <?= $shop['is_open'] ? 'btn--outline' : 'btn--primary' ?>">
            <?= $shop['is_open'] ? 'Fermer la boutique' : 'Ouvrir la boutique' ?>
        </button>
    </form>

    <p style="color: #6b7280; font-size: 0.85rem;">
        Ferme temporairement ta boutique si tu ne peux pas honorer de nouvelles commandes (vacances, surcharge...).
        Elle reste visible dans la recherche mais les clients ne pourront plus commander tant qu'elle n'est pas rouverte.
    </p>
<?php endif; ?>

<form method="POST" action="/my-shop">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

    <div>
        <label for="name">Nom de la boutique</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= htmlspecialchars($shop['name'] ?? '') ?>"
            required>
        <?php if (isset($errors['name'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['name']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="bio">Description</label>
        <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($shop['bio'] ?? '') ?></textarea>
    </div>

    <div>
        <p>Styles artistiques</p>
        <?php foreach ($availableStyles as $style): ?>
            <label>
                <input
                    type="checkbox"
                    name="styles[]"
                    value="<?= htmlspecialchars($style) ?>"
                    <?= in_array($style, $currentStyles, true) ? 'checked' : '' ?>>
                <?= htmlspecialchars($style) ?>
            </label>
        <?php endforeach; ?>
    </div>

    <div>
        <label>
            <input
                type="checkbox"
                name="is_open"
                <?= ($shop['is_open'] ?? false) ? 'checked' : '' ?>>
            Boutique ouverte aux commandes
        </label>
    </div>

    <button type="submit">Enregistrer</button>
</form>
