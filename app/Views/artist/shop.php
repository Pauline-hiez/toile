<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ShopController::manage()/save()).
 *
 * @var array|null $shop
 * @var array<string, string> $errors
 * @var string|null $success
 */
$pageTitle = 'Ma boutique — Toile';

$currentStyles = $shop !== null && $shop['styles']
    ? json_decode($shop['styles'], true)
    : [];

$availableStyles = \App\Models\Shop::STYLES;
?>

<?php if ($success !== null): ?>
    <div class="bg-success-bg border border-success/25 text-success rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($shop !== null && !$shop['plan_selected']): ?>
    <div class="bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-4 mb-6 text-[0.9rem]">
        <strong>Ta boutique n'est pas encore ouverte.</strong>
        Choisis ta formule d'abonnement (gratuite ou payante) pour l'activer.
        <a href="/my-subscription" class="font-semibold underline">Choisir ma formule →</a>
    </div>
<?php endif; ?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <h2 class="text-base font-semibold text-ink">Ma boutique</h2>

    <?php if ($shop !== null): ?>
        <p class="text-[0.85rem] text-muted mb-1">URL publique de ma boutique : <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="text-primary hover:underline">/boutiques/<?= htmlspecialchars($shop['slug']) ?></a></p>

        <?php if ($shop['plan_selected']): ?>
            <div class="flex items-center gap-3 mb-5">
                <p class="text-[0.85rem]">
                    Statut : <strong class="text-ink"><?= $shop['is_open'] ? 'Ouverte aux commandes' : 'Fermée aux commandes' ?></strong>
                </p>
                <form method="POST" action="/my-shop/toggle">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <button type="submit" class="btn <?= $shop['is_open'] ? 'btn--outline' : 'btn--primary' ?>">
                        <?= $shop['is_open'] ? 'Fermer la boutique' : 'Ouvrir la boutique' ?>
                    </button>
                </form>
            </div>
            <p class="text-[0.8rem] text-muted mb-5">
                Ferme temporairement ta boutique si tu ne peux pas honorer de nouvelles commandes (vacances, surcharge...).
                Elle reste visible dans la recherche mais les clients ne pourront plus commander tant qu'elle n'est pas rouverte.
            </p>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-[0.85rem] text-muted mb-5">Configure ta boutique pour qu'elle apparaisse sur Toile.</p>
    <?php endif; ?>

    <form method="POST" action="/my-shop" enctype="multipart/form-data" class="flex flex-col gap-5 max-w-[640px]">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <div>
            <label class="block font-semibold text-[0.9rem] mb-2">Image de bannière</label>

            <?php if (!empty($shop['banner'])): ?>
                <img src="/uploads/banners/<?= htmlspecialchars($shop['banner']) ?>" alt="Bannière" class="w-full h-[200px] object-cover rounded-md border border-border mb-3">
            <?php endif; ?>

            <label class="btn btn--primary cursor-pointer inline-block">
                <?= !empty($shop['banner']) ? 'Modifier ma bannière' : 'Ajouter une bannière' ?>
                <input type="file" name="banner" accept="image/jpeg,image/png,image/webp" class="hidden">
            </label>
            <?php if (isset($errors['banner'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['banner']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="name" class="block font-semibold text-[0.9rem] mb-2">Nom de la boutique</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($shop['name'] ?? '') ?>" required
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary">
            <?php if (isset($errors['name'])): ?>
                <p class="text-danger text-[0.8rem] mt-1"><?= htmlspecialchars($errors['name']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="bio" class="block font-semibold text-[0.9rem] mb-2">Description</label>
            <textarea id="bio" name="bio" rows="4"
                class="w-full border border-border rounded-md px-4 py-[0.6rem] font-main outline-none focus:border-primary resize-y"><?= htmlspecialchars($shop['bio'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block font-semibold text-[0.9rem] mb-2">Styles artistiques</label>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <?php foreach ($availableStyles as $style): ?>
                    <label class="inline-flex items-center gap-2 text-[0.9rem] cursor-pointer">
                        <input type="checkbox" name="styles[]" value="<?= htmlspecialchars($style) ?>"
                            <?= in_array($style, $currentStyles, true) ? 'checked' : '' ?>
                            class="w-4 h-4 accent-primary cursor-pointer">
                        <?= htmlspecialchars(ucfirst($style)) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn--primary">Enregistrer</button>
        </div>
    </form>
</div>
