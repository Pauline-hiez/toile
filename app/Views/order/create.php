<?php $pageTitle = 'Commander : ' . htmlspecialchars($service['title']) . ' — Toile'; ?>

<h1>Commander : <?= htmlspecialchars($service['title']) ?></h1>
<p>Boutique : <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>"><?= htmlspecialchars($shop['name']) ?></a></p>

<form method="POST" action="/commander" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">

    <div>
        <label for="title">Titre de ton projet</label>
        <input type="text" id="title" name="title" placeholder="Ex : Portrait de mon personnage" required>
        <?php if (isset($errors['title'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['title']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="description">Décris ton idée en détail</label>
        <textarea id="description" name="description" rows="6" required></textarea>
        <?php if (isset($errors['description'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['description']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($basesGrouped)): ?>
        <h2>Précise ta demande</h2>
        <?php if (isset($errors['service_base'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['service_base']) ?></p>
        <?php endif; ?>
        <?php foreach ($basesGrouped as $category => $categoryBases): ?>
            <div>
                <strong><?= htmlspecialchars($category) ?></strong><br>
                <?php foreach ($categoryBases as $base): ?>
                    <label>
                        <input
                            type="radio"
                            name="service_base[<?= htmlspecialchars($category) ?>]"
                            value="<?= $base['id'] ?>"
                            required>
                        <?= htmlspecialchars($base['label']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($options)): ?>
        <h2>Options</h2>
        <?php foreach ($options as $option): ?>
            <label>
                <input
                    type="checkbox"
                    name="options[]"
                    value="<?= $option['id'] ?>"
                    data-price="<?= $option['extra_price'] ?>"
                    class="option-checkbox">
                <?= htmlspecialchars($option['label']) ?>
                (+<?= number_format($option['extra_price'] / 100, 2) ?> €)
            </label>
        <?php endforeach; ?>
    <?php endif; ?>

    <div>
        <label for="reference">Fichier de référence (optionnel)</label>
        <input type="file" id="reference" name="reference" accept="image/jpeg,image/png,image/webp">
        <?php if (isset($errors['reference'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['reference']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($shop['accepts_quotes'])): ?>
        <div>
            <label>
                <input type="checkbox" name="is_quote">
                Je préfère demander un devis d'abord
            </label>
        </div>
    <?php endif; ?>

    <h2>
        Total estimé :
        <span id="total-price"><?= number_format($service['base_price'] / 100, 2) ?></span> €
    </h2>

    <button type="submit">Envoyer ma demande</button>
</form>

<script>
    const basePrice = <?= $service['base_price'] ?>;
    const checkboxes = document.querySelectorAll('.option-checkbox');
    const totalDisplay = document.getElementById('total-price');

    function updateTotal() {
        let total = basePrice;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                total += parseInt(cb.dataset.price);
            }
        });

        totalDisplay.textContent = (total / 100).toFixed(2);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
</script>

<p><a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>">Retour à la boutique</a></p>
