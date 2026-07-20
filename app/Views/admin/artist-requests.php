<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir AdminController::artistRequests()).
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
                        <h2 class="text-base font-semibold text-ink"><?= htmlspecialchars($request['artist_display_name'] ?: $request['username']) ?></h2>
                        <p class="text-[0.8rem] text-muted">
                            Compte : <?= htmlspecialchars($request['username']) ?> (<?= htmlspecialchars($request['email']) ?>)
                            · Candidature envoyée le <?= \App\Core\FrenchDate::format("d MMM y 'à' HH'h'mm", $request['created_at']) ?>
                        </p>
                    </div>
                    <span class="<?= \App\Core\Badge::classes('warning') ?>">En attente</span>
                </div>

                <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-4 mb-5 text-[0.85rem]">
                    <div>
                        <span class="block text-[0.75rem] font-semibold text-muted uppercase tracking-[0.03em] mb-1">Boutique souhaitée</span>
                        <span class="text-ink"><?= htmlspecialchars($request['requested_shop_name'] ?: '—') ?></span>
                    </div>
                    <div>
                        <span class="block text-[0.75rem] font-semibold text-muted uppercase tracking-[0.03em] mb-1">Email de contact</span>
                        <span class="text-ink"><?= htmlspecialchars($request['artist_contact_email'] ?: '—') ?></span>
                    </div>
                    <div class="min-[641px]:col-span-2">
                        <span class="block text-[0.75rem] font-semibold text-muted uppercase tracking-[0.03em] mb-1">Présentation</span>
                        <p class="text-ink leading-[1.6]"><?= nl2br(htmlspecialchars($request['artist_presentation'] ?: '—')) ?></p>
                    </div>
                    <div class="min-[641px]:col-span-2">
                        <span class="block text-[0.75rem] font-semibold text-muted uppercase tracking-[0.03em] mb-1">Pourquoi ouvrir une boutique ?</span>
                        <p class="text-ink leading-[1.6]"><?= nl2br(htmlspecialchars($request['artist_motivation'] ?: '—')) ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <form method="POST" action="/admin/artist-requests/<?= (int) $request['id'] ?>/approve">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                        <button type="submit" class="btn btn--primary" onclick="return confirm('Valider la demande de <?= htmlspecialchars(addslashes($request['username'])) ?> ?');">
                            Approuver
                        </button>
                    </form>
                    <form method="POST" action="/admin/artist-requests/<?= (int) $request['id'] ?>/reject">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                        <button type="submit" class="btn btn--outline" onclick="return confirm('Refuser la demande de <?= htmlspecialchars(addslashes($request['username'])) ?> ?');">
                            Refuser
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
