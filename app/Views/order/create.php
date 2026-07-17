<?php $pageTitle = 'Commander : ' . htmlspecialchars($service['title']) . ' — Toile'; ?>

<div class="max-w-[640px] mx-auto px-5 min-[641px]:px-10 py-6">
    <h1 class="font-cursive text-[1.5rem] min-[641px]:text-[1.8rem] font-semibold text-ink leading-tight mb-1">Commander : <?= htmlspecialchars($service['title']) ?></h1>
    <p class="text-[0.85rem] text-muted mb-6">
        Boutique : <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="text-primary hover:underline"><?= htmlspecialchars($shop['name']) ?></a>
    </p>

    <form method="POST" action="/commander" enctype="multipart/form-data" class="flex flex-col gap-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">

        <div class="bg-white border border-border rounded-2xl shadow-sm p-5 flex flex-col gap-4">
            <div>
                <label for="title" class="block text-[0.85rem] font-semibold text-ink mb-1">Titre de ton projet</label>
                <input type="text" id="title" name="title" placeholder="Ex : Portrait de mon personnage" required
                    class="w-full border border-border rounded-full px-4 py-[0.5rem] font-main text-[0.9rem] outline-none focus:border-primary">
                <?php if (isset($errors['title'])): ?>
                    <p class="text-danger text-[0.78rem] mt-1"><?= htmlspecialchars($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="description" class="block text-[0.85rem] font-semibold text-ink mb-1">Décris ton idée en détail</label>
                <textarea id="description" name="description" rows="6" required
                    class="w-full border border-border rounded-2xl px-4 py-3 font-main text-[0.9rem] outline-none focus:border-primary resize-none"></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="text-danger text-[0.78rem] mt-1"><?= htmlspecialchars($errors['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($basesGrouped)): ?>
            <div class="bg-white border border-border rounded-2xl shadow-sm p-5">
                <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Précise ta demande</h2>
                <?php if (isset($errors['service_base'])): ?>
                    <p class="text-danger text-[0.78rem] mb-3"><?= htmlspecialchars($errors['service_base']) ?></p>
                <?php endif; ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($basesGrouped as $category => $categoryBases): ?>
                        <div>
                            <p class="text-[0.82rem] font-semibold text-ink mb-2"><?= htmlspecialchars($category) ?></p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($categoryBases as $base): ?>
                                    <label class="inline-flex items-center gap-2 border border-border rounded-full px-4 py-[0.4rem] text-[0.82rem] text-ink cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light has-[:checked]:text-primary transition-colors">
                                        <input type="radio" name="service_base[<?= htmlspecialchars($category) ?>]" value="<?= $base['id'] ?>" required class="accent-primary">
                                        <?= htmlspecialchars($base['label']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($options)): ?>
            <div class="bg-white border border-border rounded-2xl shadow-sm p-5">
                <h2 class="font-cursive text-[1.1rem] font-semibold text-ink mb-3">Options</h2>
                <div class="flex flex-col gap-2">
                    <?php foreach ($options as $option): ?>
                        <label class="flex items-center justify-between gap-3 border border-border rounded-full px-4 py-[0.45rem] text-[0.85rem] text-ink cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary-light transition-colors">
                            <span class="flex items-center gap-2">
                                <input type="checkbox" name="options[]" value="<?= $option['id'] ?>" data-price="<?= $option['extra_price'] ?>" class="option-checkbox accent-primary">
                                <?= htmlspecialchars($option['label']) ?>
                            </span>
                            <span class="text-muted shrink-0">+<?= number_format($option['extra_price'] / 100, 2) ?> €</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-border rounded-2xl shadow-sm p-5 flex flex-col gap-4">
            <div>
                <label for="reference" class="block text-[0.85rem] font-semibold text-ink mb-1">Fichier de référence (optionnel)</label>
                <input type="file" id="reference" name="reference" accept="image/jpeg,image/png,image/webp" class="text-[0.82rem] text-muted">
                <?php if (isset($errors['reference'])): ?>
                    <p class="text-danger text-[0.78rem] mt-1"><?= htmlspecialchars($errors['reference']) ?></p>
                <?php endif; ?>
            </div>

            <?php if (!empty($shop['accepts_quotes'])): ?>
                <label class="inline-flex items-center gap-2 text-[0.85rem] text-ink cursor-pointer">
                    <input type="checkbox" name="is_quote" class="w-4 h-4 accent-primary cursor-pointer">
                    Je préfère demander un devis d'abord
                </label>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-between gap-3 bg-primary-light rounded-xl px-5 py-4">
            <span class="text-[0.85rem] font-semibold text-primary">Total estimé</span>
            <span class="text-[1.3rem] font-cursive font-semibold text-primary"><span id="total-price"><?= number_format($service['base_price'] / 100, 2) ?></span> €</span>
        </div>

        <button type="submit" class="btn btn--primary self-center px-12">Envoyer ma demande</button>
    </form>

    <p class="text-center mt-5">
        <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="text-[0.85rem] text-primary hover:underline">← Retour à la boutique</a>
    </p>
</div>

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
