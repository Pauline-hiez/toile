<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir PortfolioController::index()/upload()).
 *
 * @var array $images
 * @var string|null $error
 */
$pageTitle = 'Mon portfolio — Toile';
?>

<?php if ($error !== null): ?>
    <div class="bg-danger-bg border border-danger/25 text-danger rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm mb-8">
    <h2 class="text-base font-semibold mb-5 text-ink">Mon portfolio</h2>

    <form method="POST" action="/my-portfolio/upload" enctype="multipart/form-data" class="flex items-center gap-4 flex-wrap">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <label class="btn btn--outline cursor-pointer">
            Choisir des images
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="hidden">
        </label>

        <button type="submit" class="btn btn--primary">Uploader</button>
    </form>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <?php if (empty($images)): ?>
        <p class="text-muted text-[0.85rem] text-center py-6">Tu n'as pas encore d'image dans ton portfolio.</p>
    <?php else: ?>
        <div class="grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] gap-4">
            <?php foreach ($images as $image): ?>
                <div class="relative rounded-md overflow-hidden border border-border">
                    <img src="/uploads/portfolio/<?= htmlspecialchars($image['filename']) ?>" alt="Image de portfolio" class="w-full h-[160px] object-cover block">
                    <form method="POST" action="/my-portfolio/<?= $image['id'] ?>/delete" class="absolute bottom-2 right-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                        <button type="submit" class="flex items-center justify-center w-8 h-8 rounded-full bg-white/90 text-muted hover:text-danger hover:bg-white transition-colors" title="Supprimer" onclick="return confirm('Supprimer cette image ?');">
                            <img src="/assets/images/icones/supprimer.png" alt="Supprimer" class="w-4 h-4">
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
