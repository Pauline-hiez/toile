<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ArtistDashboardController::index()).
 *
 * @var array|null $shop
 * @var array{order_count: int, favorite_count: int, client_count: int} $stats
 * @var array $history
 */
?>

<?php if ($shop === null): ?>
    <div class="bg-warning-bg border border-warning/25 text-warning rounded-md px-5 py-4 mb-8 text-[0.9rem]">
        Tu n'as pas encore créé de boutique. <a href="/my-shop" class="font-semibold underline">Configure-la maintenant →</a>
    </div>
<?php endif; ?>

<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Nombre de commandes</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= (int) $stats['order_count'] ?></div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Favoris</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= (int) $stats['favorite_count'] ?></div>
    </div>

    <div class="bg-white border border-border rounded-md p-5 text-center shadow-sm">
        <div class="text-[0.85rem] text-muted font-medium mb-2">Clients</div>
        <div class="font-cursive text-[2.2rem] font-semibold text-ink"><?= (int) $stats['client_count'] ?></div>
    </div>
</div>

<div class="bg-white border border-border rounded-md p-6 shadow-sm">
    <h2 class="text-base font-semibold mb-5 text-ink">Historique</h2>

    <?php if (empty($history)): ?>
        <p class="text-muted text-[0.85rem] text-center py-6">Aucune activité pour le moment.</p>
    <?php else: ?>
        <div class="flex flex-col gap-4">
            <?php foreach ($history as $item): ?>
                <div class="flex items-center gap-3">
                    <img src="/assets/images/icones/notifications.png" alt="" class="w-10 h-10 rounded-full object-contain shrink-0 bg-primary-light p-2">
                    <div>
                        <p class="text-[0.9rem] text-ink font-medium">
                            <?php if (!empty($item['link'])): ?>
                                <a href="<?= htmlspecialchars($item['link']) ?>" class="no-underline text-ink hover:underline"><?= htmlspecialchars($item['message']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($item['message']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="text-[0.8rem] text-muted"><?= \App\Core\FrenchDate::format("d MMM y 'à' HH'h'mm", $item['created_at']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-6">
            <a href="/notifications" class="btn btn--primary">Voir tout l'historique</a>
        </div>
    <?php endif; ?>
</div>
