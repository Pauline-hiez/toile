<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ServiceController::create()/edit()/save()).
 *
 * @var array|null $service
 * @var array $options
 * @var array $bases
 * @var int $maxOptions Nombre max d'options/catégories de base selon
 *                       l'abonnement de la boutique (voir
 *                       ShopSubscription::getMaxOptionsPerService()).
 * @var array<string, string> $errors
 */
$pageTitle = ($service ? 'Modifier' : 'Créer') . ' une prestation — Toile';

// Options : au moins 3 lignes affichées, jamais plus que le nombre déjà
// enregistré, plafonné par l'abonnement — un bouton JS permet d'en
// ajouter d'autres jusqu'à cette limite.
$rows = $options;
$optionRowsToShow = min(max(3, count($rows)), max($maxOptions, count($rows)));
for ($i = count($rows); $i < $optionRowsToShow; $i++) {
    $rows[] = ['label' => '', 'extra_price' => ''];
}

// Éléments de base : une ligne par catégorie, avec les choix regroupés
// en une seule chaîne séparée par des virgules (ex. "Réaliste,
// Illustration, Caricature") — bien plus rapide à saisir que de répéter
// la catégorie sur chaque choix. Même plafond que les options.
$baseGroups = [];
foreach ($bases as $base) {
    $baseGroups[$base['category']][] = $base['label'];
}
$baseRows = [];
foreach ($baseGroups as $category => $labels) {
    $baseRows[] = ['category' => $category, 'choices' => implode(', ', $labels)];
}
$baseRowsToShow = min(max(3, count($baseRows)), max($maxOptions, count($baseRows)));
for ($i = count($baseRows); $i < $baseRowsToShow; $i++) {
    $baseRows[] = ['category' => '', 'choices' => ''];
}
?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <h2 class="text-base font-semibold mb-5 text-ink"><?= $service ? 'Modifier' : 'Créer' ?> une prestation</h2>

    <form method="POST" action="/my-services/save" enctype="multipart/form-data" class="flex flex-col gap-5 max-w-[560px]">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <?php if ($service): ?>
            <input type="hidden" name="id" value="<?= $service['id'] ?>">
        <?php endif; ?>

        <div>
            <label class="block font-semibold text-[0.9rem] mb-2">Image de la prestation</label>

            <img id="servicePreviewImg"
                src="<?= !empty($service['image']) ? '/uploads/services/' . htmlspecialchars($service['image']) : '' ?>"
                alt="Prestation"
                class="w-[220px] aspect-[3/4] object-cover rounded-md border border-border mb-3 <?= empty($service['image']) ? 'hidden' : '' ?>">

            <label class="btn btn--outline cursor-pointer inline-block">
                <?= !empty($service['image']) ? "Changer l'image" : 'Ajouter une image' ?>
                <input type="file" id="serviceImageInput" name="image" accept="image/jpeg,image/png,image/webp" class="hidden">
            </label>
            <?php if (isset($errors['image'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['image']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="title" class="block font-semibold text-[0.9rem] mb-2">Titre</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($service['title'] ?? '') ?>" required
                class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['title'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['title']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="description" class="block font-semibold text-[0.9rem] mb-2">Description</label>
            <textarea id="description" name="description" rows="4"
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary resize-y"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-5 flex-wrap">
            <div class="flex-1 min-w-[180px]">
                <label for="base_price" class="block font-semibold text-[0.9rem] mb-2">Prix de base (€)</label>
                <input type="number" id="base_price" name="base_price" step="0.01" min="0"
                    value="<?= isset($service['base_price']) ? number_format($service['base_price'] / 100, 2, '.', '') : '' ?>" required
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                <?php if (isset($errors['base_price'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['base_price']) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex-1 min-w-[180px]">
                <label for="delivery_days" class="block font-semibold text-[0.9rem] mb-2">Délai de livraison (jours)</label>
                <input type="number" id="delivery_days" name="delivery_days" min="1"
                    value="<?= htmlspecialchars($service['delivery_days'] ?? '') ?>" required
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] font-main outline-none focus:border-primary">
                <?php if (isset($errors['delivery_days'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['delivery_days']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <label class="inline-flex items-center gap-2 text-[0.9rem] cursor-pointer">
            <input type="checkbox" name="is_active" <?= !isset($service) || ($service['is_active'] ?? false) ? 'checked' : '' ?> class="w-4 h-4 accent-primary cursor-pointer">
            Prestation active (visible publiquement)
        </label>

        <div>
            <h3 class="font-semibold text-[0.9rem] mb-1 text-ink">Éléments de base (facultatif)</h3>
            <p class="text-[0.8rem] text-muted mb-3">Une ligne par catégorie : sépare les choix par des virgules. Ex. catégorie "Style", choix "Réaliste, Illustration, Caricature". Ton client choisira un seul choix par catégorie, sans impact sur le prix. Laisse la catégorie vide pour ignorer une ligne. Maximum <?= $maxOptions ?> catégorie<?= $maxOptions > 1 ? 's' : '' ?> selon ton abonnement.</p>

            <div class="flex flex-col gap-2" id="baseRowsContainer">
                <?php foreach ($baseRows as $base): ?>
                    <div class="flex gap-3 base-row">
                        <input type="text" name="base_category[]" placeholder="Catégorie (ex : Style)" value="<?= htmlspecialchars($base['category']) ?>"
                            class="w-[180px] border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">
                        <input type="text" name="base_choices[]" placeholder="Choix séparés par des virgules (ex : Réaliste, Illustration, Caricature)" value="<?= htmlspecialchars($base['choices']) ?>"
                            class="flex-1 border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="addBaseRow" class="btn btn--outline mt-2" data-max="<?= (int) $maxOptions ?>">+ Ajouter une catégorie</button>
        </div>

        <div>
            <h3 class="font-semibold text-[0.9rem] mb-1 text-ink">Options de prix (facultatif)</h3>
            <p class="text-[0.8rem] text-muted mb-3">Laisse le libellé vide pour ignorer une ligne. Maximum <?= $maxOptions ?> option<?= $maxOptions > 1 ? 's' : '' ?> selon ton abonnement.</p>

            <div class="flex flex-col gap-2" id="optionRowsContainer">
                <?php foreach ($rows as $option): ?>
                    <div class="flex gap-3 option-row">
                        <input type="text" name="option_label[]" placeholder="Ex : Couleur" value="<?= htmlspecialchars($option['label']) ?>"
                            class="flex-1 border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">
                        <input type="number" name="option_price[]" step="0.01" min="0" placeholder="Supplément en €"
                            value="<?= $option['extra_price'] !== '' ? number_format($option['extra_price'] / 100, 2, '.', '') : '' ?>"
                            class="w-[160px] border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="addOptionRow" class="btn btn--outline mt-2" data-max="<?= (int) $maxOptions ?>">+ Ajouter une option</button>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn btn--primary">Enregistrer</button>
            <a href="/my-services" class="text-[0.85rem] text-muted hover:underline">Retour à mes prestations</a>
        </div>
    </form>
</div>

<script>
    (function () {
        function setupDynamicRows(containerId, buttonId, rowClass, makeRowHtml) {
            var container = document.getElementById(containerId);
            var button = document.getElementById(buttonId);
            var max = parseInt(button.dataset.max, 10);

            function updateButtonState() {
                var count = container.querySelectorAll('.' + rowClass).length;
                button.style.display = count >= max ? 'none' : '';
            }

            button.addEventListener('click', function () {
                var count = container.querySelectorAll('.' + rowClass).length;
                if (count >= max) return;

                var wrapper = document.createElement('div');
                wrapper.innerHTML = makeRowHtml();
                container.appendChild(wrapper.firstElementChild);
                updateButtonState();
            });

            updateButtonState();
        }

        setupDynamicRows('baseRowsContainer', 'addBaseRow', 'base-row', function () {
            return '<div class="flex gap-3 base-row">'
                + '<input type="text" name="base_category[]" placeholder="Catégorie (ex : Style)" class="w-[180px] border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">'
                + '<input type="text" name="base_choices[]" placeholder="Choix séparés par des virgules (ex : Réaliste, Illustration, Caricature)" class="flex-1 border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">'
                + '</div>';
        });

        setupDynamicRows('optionRowsContainer', 'addOptionRow', 'option-row', function () {
            return '<div class="flex gap-3 option-row">'
                + '<input type="text" name="option_label[]" placeholder="Ex : Couleur" class="flex-1 border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">'
                + '<input type="number" name="option_price[]" step="0.01" min="0" placeholder="Supplément en €" class="w-[160px] border border-border rounded-full px-4 py-[0.35rem] font-main outline-none focus:border-primary">'
                + '</div>';
        });

        var imageInput = document.getElementById('serviceImageInput');
        var previewImg = document.getElementById('servicePreviewImg');

        imageInput.addEventListener('change', function () {
            var file = imageInput.files[0];
            if (!file) return;

            previewImg.src = URL.createObjectURL(file);
            previewImg.classList.remove('hidden');
        });
    })();
</script>
