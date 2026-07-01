<?php $pageTitle = ($service ? 'Modifier' : 'Créer') . ' une prestation — Toile'; ?>

<h1><?= $service ? 'Modifier' : 'Créer' ?> une prestation</h1>

<form method="POST" action="/my-services/save">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

    <?php if ($service): ?>
        <input type="hidden" name="id" value="<?= $service['id'] ?>">
    <?php endif; ?>

    <div>
        <label for="title">Titre</label>
        <input
            type="text"
            id="title"
            name="title"
            value="<?= htmlspecialchars($service['title'] ?? '') ?>"
            required>
        <?php if (isset($errors['title'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['title']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
    </div>

    <div>
        <label for="base_price">Prix de base (€)</label>
        <input
            type="number"
            id="base_price"
            name="base_price"
            step="0.01"
            min="0"
            value="<?= isset($service['base_price']) ? number_format($service['base_price'] / 100, 2, '.', '') : '' ?>"
            required>
        <?php if (isset($errors['base_price'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['base_price']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="delivery_days">Délai de livraison (jours)</label>
        <input
            type="number"
            id="delivery_days"
            name="delivery_days"
            min="1"
            value="<?= htmlspecialchars($service['delivery_days'] ?? '') ?>"
            required>
        <?php if (isset($errors['delivery_days'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['delivery_days']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label>
            <input
                type="checkbox"
                name="is_active"
                <?= !isset($service) || ($service['is_active'] ?? false) ? 'checked' : '' ?>>
            Prestation active (visible publiquement)
        </label>
    </div>

    <h2>Options de prix (facultatif)</h2>
    <p>Laisse le libellé vide pour ignorer une ligne.</p>

    <?php
    $rows = $options;
    $rows[] = ['label' => '', 'extra_price' => ''];
    $rows[] = ['label' => '', 'extra_price' => ''];
    ?>

    <?php foreach ($rows as $option): ?>
        <div>
            <input
                type="text"
                name="option_label[]"
                placeholder="Ex : Couleur"
                value="<?= htmlspecialchars($option['label']) ?>">
            <input
                type="number"
                name="option_price[]"
                step="0.01"
                min="0"
                placeholder="Supplément en €"
                value="<?= $option['extra_price'] !== '' ? number_format($option['extra_price'] / 100, 2, '.', '') : '' ?>">
        </div>
    <?php endforeach; ?>

    <button type="submit">Enregistrer</button>
</form>

<p><a href="/my-services">Retour à mes prestations</a></p>
