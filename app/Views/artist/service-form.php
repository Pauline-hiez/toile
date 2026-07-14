<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ServiceController::create()/edit()/save()).
 *
 * @var array|null $service
 * @var array $options
 * @var array<string, string> $errors
 */
$pageTitle = ($service ? 'Modifier' : 'Créer') . ' une prestation — Toile';

$rows = $options;
$rows[] = ['label' => '', 'extra_price' => ''];
$rows[] = ['label' => '', 'extra_price' => ''];
?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <h2 class="text-base font-semibold mb-5 text-ink"><?= $service ? 'Modifier' : 'Créer' ?> une prestation</h2>

    <form method="POST" action="/my-services/save" class="flex flex-col gap-5 max-w-[560px]">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <?php if ($service): ?>
            <input type="hidden" name="id" value="<?= $service['id'] ?>">
        <?php endif; ?>

        <div>
            <label for="title" class="block font-semibold text-[0.9rem] mb-2">Titre</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($service['title'] ?? '') ?>" required
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
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
                    class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
                <?php if (isset($errors['base_price'])): ?>
                    <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['base_price']) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex-1 min-w-[180px]">
                <label for="delivery_days" class="block font-semibold text-[0.9rem] mb-2">Délai de livraison (jours)</label>
                <input type="number" id="delivery_days" name="delivery_days" min="1"
                    value="<?= htmlspecialchars($service['delivery_days'] ?? '') ?>" required
                    class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
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
            <h3 class="font-semibold text-[0.9rem] mb-1 text-ink">Options de prix (facultatif)</h3>
            <p class="text-[0.8rem] text-muted mb-3">Laisse le libellé vide pour ignorer une ligne.</p>

            <div class="flex flex-col gap-2">
                <?php foreach ($rows as $option): ?>
                    <div class="flex gap-3">
                        <input type="text" name="option_label[]" placeholder="Ex : Couleur" value="<?= htmlspecialchars($option['label']) ?>"
                            class="flex-1 border border-border rounded-md px-4 py-[0.5rem] font-main outline-none focus:border-primary">
                        <input type="number" name="option_price[]" step="0.01" min="0" placeholder="Supplément en €"
                            value="<?= $option['extra_price'] !== '' ? number_format($option['extra_price'] / 100, 2, '.', '') : '' ?>"
                            class="w-[160px] border border-border rounded-md px-4 py-[0.5rem] font-main outline-none focus:border-primary">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn btn--primary">Enregistrer</button>
            <a href="/my-services" class="text-[0.85rem] text-muted hover:underline">Retour à mes prestations</a>
        </div>
    </form>
</div>
