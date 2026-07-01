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
