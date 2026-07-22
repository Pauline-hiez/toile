<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ServiceController::index()).
 *
 * @var array $services
 * @var array $options
 * @var array $bases
 * @var int $categoryCount Nombre de catégories d'éléments de base distinctes.
 * @var int $servicesWithOptionsCount Nombre de prestations ayant au moins une option payante.
 * @var string|null $shopSlug
 * @var string $tab 'services'|'bases'|'options'
 */
$pageTitle = 'Mes prestations — Toile';
?>

<div class="grid grid-cols-3 gap-4 mb-8 max-w-[640px]">
    <a href="/my-services?tab=services" class="bg-white border border-border rounded-md p-5 text-center shadow-sm no-underline text-inherit transition-colors <?= $tab === 'services' ? 'border-primary' : 'hover:border-primary' ?>">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Prestations</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= count($services) ?></div>
    </a>

    <a href="/my-services?tab=bases" class="bg-white border border-border rounded-md p-5 text-center shadow-sm no-underline text-inherit transition-colors <?= $tab === 'bases' ? 'border-primary' : 'hover:border-primary' ?>">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Catégories</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= $categoryCount ?></div>
    </a>

    <a href="/my-services?tab=options" class="bg-white border border-border rounded-md p-5 text-center shadow-sm no-underline text-inherit transition-colors <?= $tab === 'options' ? 'border-primary' : 'hover:border-primary' ?>">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Prestations avec options</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= $servicesWithOptionsCount ?></div>
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
    <?php elseif ($tab === 'bases'): ?>
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-semibold text-ink">Mes éléments de base</h2>
            <a href="/my-services" class="btn btn--primary">+ Ajouter un élément de base</a>
        </div>
        <p class="text-[0.8rem] text-muted mb-5">Les éléments de base sont des choix groupés par catégorie (format, style, matériaux...) que ton client sélectionne pour préciser sa demande — un seul choix par catégorie, sans impact sur le prix.</p>

        <?php if (empty($bases)): ?>
            <p class="text-muted text-[0.85rem] text-center py-6">Tu n'as pas encore d'élément de base. Ajoutes-en depuis une prestation.</p>
        <?php else: ?>
            <div class="flex flex-col gap-3">
                <?php foreach ($bases as $base): ?>
                    <div class="flex items-center justify-between gap-4 p-3 border border-border rounded-md flex-wrap">
                        <p class="text-[0.9rem] text-ink">
                            <strong><?= htmlspecialchars($base['service_title']) ?></strong> —
                            <?= htmlspecialchars($base['category']) ?> :
                            <?= htmlspecialchars($base['label']) ?>
                        </p>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="/my-services/<?= $base['service_id'] ?>/edit" class="btn btn--outline">Modifier</a>
                            <form method="POST" action="/my-services/bases/<?= $base['id'] ?>/delete">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <button type="submit" class="btn btn--outline" onclick="return confirm('Supprimer cet élément de base ?');">Supprimer</button>
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
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[0.875rem] max-[720px]:min-w-[640px] [&_th]:py-3 [&_th]:px-4 [&_th]:text-left [&_th]:font-semibold [&_th]:text-[0.8rem] [&_th]:text-muted [&_th]:bg-bg [&_th]:border-b [&_th]:border-border [&_td]:py-3 [&_td]:px-4 [&_td]:border-b [&_td]:border-border [&_td]:align-middle [&_tr:last-child_td]:border-b-0 [&_tr:hover_td]:bg-[#faf7f2]">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Prestation</th>
                            <th>Prix</th>
                            <th>Options</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <?php $serviceOptionCount = count(array_filter($options, fn($option) => $option['service_id'] === $service['id'])); ?>
                            <tr>
                                <td>
                                    <?php if (!empty($service['image'])): ?>
                                        <img src="/uploads/services/<?= htmlspecialchars($service['image']) ?>" alt="" class="w-12 h-12 rounded-md object-cover border border-border">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($service['title']) ?></td>
                                <td><?= number_format($service['base_price'] / 100, 2) ?> €</td>
                                <td>
                                    <?php if ($serviceOptionCount > 0): ?>
                                        <a href="/my-services?tab=options" class="text-primary hover:underline"><?= $serviceOptionCount ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($service['is_active']): ?>
                                        <span class="<?= \App\Core\Badge::classes('success') ?>">Active</span>
                                    <?php else: ?>
                                        <span class="<?= \App\Core\Badge::classes('neutral') ?>">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2 [&_a]:bg-transparent [&_a]:border-0 [&_a]:cursor-pointer [&_a]:p-1 [&_a]:rounded-sm [&_a]:text-muted [&_a]:transition-colors [&_a]:flex [&_a]:items-center [&_a]:no-underline [&_a:hover]:text-primary [&_a:hover]:bg-primary-light [&_button]:bg-transparent [&_button]:border-0 [&_button]:cursor-pointer [&_button]:p-1 [&_button]:rounded-sm [&_button]:text-muted [&_button]:transition-colors [&_button]:flex [&_button]:items-center [&_button:hover]:text-primary [&_button:hover]:bg-primary-light [&_svg]:w-4 [&_svg]:h-4 [&_img]:w-4 [&_img]:h-4 [&_img]:object-contain">
                                        <?php if ($shopSlug): ?>
                                            <a href="/boutiques/<?= htmlspecialchars($shopSlug) ?>?preview=1&tab=prestations#service-<?= $service['id'] ?>" target="_blank" rel="noopener" title="Voir le rendu">
                                                <img src="/assets/images/icones/voir.png" alt="Voir">
                                            </a>
                                        <?php endif; ?>
                                        <a href="/my-services/<?= $service['id'] ?>/edit" title="Modifier">
                                            <img src="/assets/images/icones/modifier.png" alt="Modifier">
                                        </a>
                                        <form method="POST" action="/my-services/<?= $service['id'] ?>/delete">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                            <button type="submit" title="Supprimer" onclick="return confirm('Supprimer cette prestation ?');">
                                                <img src="/assets/images/icones/supprimer.png" alt="Supprimer">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
