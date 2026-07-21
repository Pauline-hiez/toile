<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::categoryRequests()).
 *
 * @var array $requests
 */
?>

<p class="text-muted text-[0.9rem] mb-6">
    <strong class="text-ink"><?= count($requests) ?></strong> demande<?= count($requests) > 1 ? 's' : '' ?> en attente de traitement.
</p>

<?php if (empty($requests)): ?>
    <div class="bg-white border border-border rounded-md p-6 shadow-sm text-center">
        <p class="text-muted text-[0.85rem]">Aucune demande en attente.</p>
    </div>
<?php else: ?>
    <div class="flex flex-col gap-5">
        <?php foreach ($requests as $request): ?>
            <div class="bg-white border border-border rounded-md p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-ink">
                            <?= htmlspecialchars(ucfirst($request['name'])) ?>
                            <span class="<?= \App\Core\Badge::classes('neutral') ?> ml-2"><?= $request['category_type'] === 'style' ? 'Style' : 'Type / spécialité' ?></span>
                        </h2>
                        <p class="text-[0.8rem] text-muted">
                            Proposé par <a href="/boutiques/<?= htmlspecialchars($request['shop_slug']) ?>" target="_blank" class="text-primary underline"><?= htmlspecialchars($request['shop_name']) ?></a>
                            · le <?= \App\Core\FrenchDate::format("d MMM y 'à' HH'h'mm", $request['created_at']) ?>
                        </p>
                    </div>
                    <span class="<?= \App\Core\Badge::classes('warning') ?>">En attente</span>
                </div>

                <?php if (!empty($request['image'])): ?>
                    <img src="/uploads/category-requests/<?= htmlspecialchars($request['image']) ?>" alt="" class="w-32 h-32 object-cover rounded-md border border-border mb-5">
                <?php endif; ?>

                <?php if ($request['category_type'] === 'style'): ?>
                    <p class="text-[0.78rem] text-muted mb-3">La mise en avant en page d'accueil se choisit séparément, dans Paramètres → Styles à la une.</p>
                <?php endif; ?>

                <form method="POST" action="/admin/category-requests/<?= (int) $request['id'] ?>/approve" class="inline-block">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <button type="submit" class="btn btn--primary">Approuver</button>
                </form>

                <form method="POST" action="/admin/category-requests/<?= (int) $request['id'] ?>/reject" class="inline-block mt-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <button type="submit" class="btn btn--outline" onclick="return confirm('Refuser la proposition « <?= htmlspecialchars(addslashes($request['name'])) ?> » ?');">
                        Refuser
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
