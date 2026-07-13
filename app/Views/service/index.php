<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ServiceController::index()).
 *
 * @var array $services
 * @var array $options
 * @var string $tab 'services'|'options'
 */
$pageTitle = 'Mes prestations — Toile';
?>

<div class="grid grid-cols-2 gap-4 mb-8 max-w-[480px]">
    <a href="/my-services?tab=services" class="bg-white border border-border rounded-md p-5 text-center shadow-sm no-underline text-inherit transition-colors <?= $tab === 'services' ? 'border-primary' : 'hover:border-primary' ?>">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Prestations</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= count($services) ?></div>
    </a>

    <a href="/my-services?tab=options" class="bg-white border border-border rounded-md p-5 text-center shadow-sm no-underline text-inherit transition-colors <?= $tab === 'options' ? 'border-primary' : 'hover:border-primary' ?>">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Options</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= count($options) ?></div>
    </a>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <?php if ($tab === 'options'): ?>
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-semibold text-ink">Mes options</h2>
            <a href="/my-services" class="btn btn--primary">+ Ajouter une option</a>
        </div>

        <?php if (empty($options)): ?>
            <p class="text-muted text-[0.85rem] text-center py-6">Tu n'as pas encore d'option. Ajoute-en depuis une prestation.</p>
        <?php else: ?>
            <div class="flex flex-col gap-3">
                <?php foreach ($options as $option): ?>
                    <div class="flex items-center justify-between gap-4 p-3 border border-border rounded-md flex-wrap">
                        <p class="text-[0.9rem] text-ink">
                            <strong><?= htmlspecialchars($option['service_title']) ?></strong> :
                            + <?= htmlspecialchars($option['label']) ?>
                            — <?= number_format($option['extra_price'] / 100, 2) ?> €
                        </p>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="/my-services/<?= $option['service_id'] ?>/edit" class="btn btn--outline">Modifier</a>
                            <form method="POST" action="/my-services/options/<?= $option['id'] ?>/delete">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <button type="submit" class="btn btn--outline" onclick="return confirm('Supprimer cette option ?');">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-semibold text-ink">Mes prestations</h2>
            <a href="/my-services/create" class="btn btn--primary">+ Ajouter une prestation</a>
        </div>

        <?php if (empty($services)): ?>
            <p class="text-muted text-[0.85rem] text-center py-6">Tu n'as pas encore de prestation. Crée ta première prestation !</p>
        <?php else: ?>
            <div class="flex flex-col gap-3">
                <?php foreach ($services as $service): ?>
                    <div class="flex items-center gap-4 p-3 border border-border rounded-md flex-wrap">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[0.95rem] text-ink">
                                <?= htmlspecialchars($service['title']) ?> - <?= number_format($service['base_price'] / 100, 2) ?> €
                                <?php if (!$service['is_active']): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium bg-border text-muted ml-2">Inactive</span>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($service['description'])): ?>
                                <p class="text-[0.8rem] text-muted"><?= htmlspecialchars($service['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="/my-services/<?= $service['id'] ?>/edit" class="btn btn--outline">Modifier</a>
                            <form method="POST" action="/my-services/<?= $service['id'] ?>/delete">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <button type="submit" class="btn btn--outline" onclick="return confirm('Supprimer cette prestation ?');">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
