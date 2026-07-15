<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir PortfolioController::index()/upload()).
 *
 * @var array $images Images de la page courante (36 max, 6x6).
 * @var int $total Nombre total d'images du portfolio, toutes pages confondues.
 * @var int $page
 * @var int $perPage
 * @var array<int, int|string> $pageNumbers
 * @var int $maxImages Nombre max d'images de portfolio selon l'abonnement de la boutique (>= 9999 = illimité).
 * @var string|null $error
 */
$pageTitle = 'Mon portfolio — Toile';
$isUnlimited = $maxImages >= 9999;
$limitReached = !$isUnlimited && $total >= $maxImages;

$totalPages = max(1, (int) ceil($total / $perPage));
$rangeStart = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$rangeEnd = min($total, $page * $perPage);
$pageOffset = ($page - 1) * $perPage;

$queryWithout = function (array $overrides = []) use ($page) {
    $params = array_filter(array_merge(['page' => $page], $overrides), fn($v) => $v !== '' && $v !== null);
    return '/my-portfolio' . ($params !== [] ? '?' . http_build_query($params) : '');
};
$entityLabel = 'images';
?>

<?php if ($error !== null): ?>
    <div class="bg-danger-bg border border-danger/25 text-danger rounded-md px-5 py-[0.9rem] mb-6 text-[0.9rem]">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white border border-border rounded-md p-6 shadow-sm mb-8">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-5">
        <h2 class="text-base font-semibold text-ink">Mon portfolio</h2>
        <span class="text-[0.85rem] <?= $limitReached ? 'text-danger' : 'text-muted' ?>"><?= $total ?> / <?= $isUnlimited ? 'Illimité' : $maxImages ?> images selon ton abonnement</span>
    </div>

    <?php if ($limitReached): ?>
        <p class="text-[0.85rem] text-muted">Tu as atteint la limite d'images de ton abonnement. Supprime une image ou passe à un palier supérieur pour en ajouter d'autres.</p>
    <?php else: ?>
        <form method="POST" action="/my-portfolio/upload" enctype="multipart/form-data" class="flex flex-col gap-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <div class="flex items-center gap-4 flex-wrap">
                <label class="btn btn--outline cursor-pointer">
                    Choisir des images
                    <input type="file" id="portfolioImageInput" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="hidden">
                </label>

                <button type="submit" class="btn btn--primary">Uploader</button>
            </div>

            <ul id="portfolioSelectedFiles" class="text-[0.8rem] text-muted list-disc pl-5 hidden"></ul>
        </form>
    <?php endif; ?>
</div>

<script>
    (function () {
        var input = document.getElementById('portfolioImageInput');
        var list = document.getElementById('portfolioSelectedFiles');
        if (!input || !list) return;

        input.addEventListener('change', function () {
            list.innerHTML = '';

            if (input.files.length === 0) {
                list.classList.add('hidden');
                return;
            }

            Array.prototype.forEach.call(input.files, function (file) {
                var li = document.createElement('li');
                li.textContent = file.name;
                list.appendChild(li);
            });

            list.classList.remove('hidden');
        });
    })();
</script>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <?php if (empty($images)): ?>
        <p class="text-muted text-[0.85rem] text-center py-6">Tu n'as pas encore d'image dans ton portfolio.</p>
    <?php else: ?>
        <input type="hidden" id="portfolioCsrfToken" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
        <p class="text-[0.8rem] text-muted mb-3">Glisse-dépose une image pour changer son ordre d'affichage.</p>
        <div class="grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] gap-4" id="portfolioGrid">
            <?php foreach ($images as $image): ?>
                <div class="relative rounded-md overflow-hidden border border-border cursor-grab" draggable="true" data-image-id="<?= $image['id'] ?>">
                    <img src="/uploads/portfolio/<?= htmlspecialchars($image['filename']) ?>" alt="Image de portfolio" class="w-full aspect-square object-cover block pointer-events-none">

                    <?php if (!empty($image['label'])): ?>
                        <span class="absolute bottom-2 left-2 bg-white/90 text-ink text-[0.75rem] font-medium px-2 py-1 rounded-full max-w-[calc(100%-3.5rem)] truncate"><?= htmlspecialchars($image['label']) ?></span>
                    <?php endif; ?>

                    <details class="absolute top-2 left-2">
                        <summary class="list-none cursor-pointer flex items-center justify-center w-8 h-8 rounded-full bg-white/90 text-muted hover:text-primary hover:bg-white transition-colors [&::-webkit-details-marker]:hidden" title="Modifier la légende">
                            <img src="/assets/images/icones/modifier.png" alt="Modifier" class="w-4 h-4">
                        </summary>
                        <div class="absolute top-full left-0 mt-2 bg-white border border-border rounded-md shadow-sm p-3 min-w-[200px] z-30">
                            <form method="POST" action="/my-portfolio/<?= $image['id'] ?>/label">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <input type="text" name="label" value="<?= htmlspecialchars($image['label'] ?? '') ?>" maxlength="100" placeholder="Légende (facultatif)" class="w-full border border-border rounded-full px-3 py-[0.3rem] text-[0.85rem] font-main outline-none focus:border-primary mb-2">
                                <button type="submit" class="btn btn--primary" style="width: 100%;">Enregistrer</button>
                            </form>
                        </div>
                    </details>

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

<script>
    (function () {
        var grid = document.getElementById('portfolioGrid');
        if (!grid) return;

        var dragged = null;

        Array.prototype.forEach.call(grid.children, function (item) {
            item.addEventListener('dragstart', function () {
                dragged = item;
                item.classList.add('opacity-40');
            });

            item.addEventListener('dragend', function () {
                item.classList.remove('opacity-40');
                saveOrder();
            });

            item.addEventListener('dragover', function (e) {
                e.preventDefault();
                if (!dragged || dragged === item) return;

                var rect = item.getBoundingClientRect();
                var before = (e.clientX - rect.left) < rect.width / 2;
                grid.insertBefore(dragged, before ? item : item.nextSibling);
            });
        });

        function saveOrder() {
            var ids = Array.prototype.map.call(grid.children, function (el) {
                return el.dataset.imageId;
            });

            var formData = new FormData();
            formData.append('csrf_token', document.getElementById('portfolioCsrfToken').value);
            ids.forEach(function (id) { formData.append('order[]', id); });

            fetch('/my-portfolio/reorder', { method: 'POST', body: formData });
        }
    })();
</script>
