<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::howItWorks()).
 */

$tabs = [
    'client' => 'Je suis client',
    'artist' => 'Je suis artiste',
];

$clientSteps = [
    [
        'icon' => '<path d="M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14Z"></path><path d="m20 20-4.35-4.35"></path>',
        'title' => 'Trouve ton artiste',
        'text' => 'Parcours les boutiques, filtre par style ou par budget, et découvre l\'univers de chaque artiste.',
    ],
    [
        'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M9 13h6M9 17h6"></path>',
        'title' => 'Demande un devis ou commande',
        'text' => 'Décris ton projet pour une création sur-mesure, ou commande directement une prestation à prix fixe.',
    ],
    [
        'icon' => '<rect x="4" y="10" width="16" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>',
        'title' => 'Paiement sécurisé',
        'text' => 'Ton paiement est mis de côté dès la commande et n\'est débloqué à l\'artiste qu\'à la livraison de ta création.',
    ],
    [
        'icon' => '<path d="M21 8 12 3 3 8l9 5 9-5Z"></path><path d="M3 8v8l9 5 9-5V8"></path><path d="M12 13v8"></path>',
        'title' => 'Reçois ta création',
        'text' => 'Au format numérique par défaut, ou livrée chez toi si tu choisis l\'option adresse de livraison à la commande.',
    ],
];

$artistSteps = [
    [
        'icon' => '<path d="M12 3v2M12 19v2M5 5l1.4 1.4M17.6 17.6 19 19M3 12h2M19 12h2M5 19l1.4-1.4M17.6 6.4 19 5"></path><circle cx="12" cy="12" r="4"></circle>',
        'title' => 'Crée ta boutique',
        'text' => 'Présente ton univers, ton style et tes prestations avec des tarifs libres.',
    ],
    [
        'icon' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path>',
        'title' => 'Reçois des demandes',
        'text' => 'Devis personnalisés ou commandes à prix fixe arrivent directement dans ton espace artiste.',
    ],
    [
        'icon' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="M17 8l-5-5-5 5"></path><path d="M12 3v12"></path>',
        'title' => 'Livre ta création',
        'text' => 'Échange avec ton client, partage l\'avancement, puis livre le fichier final.',
    ],
    [
        'icon' => '<rect x="2" y="6" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path>',
        'title' => 'Sois payé directement',
        'text' => 'Connecte ton compte bancaire : ta part de chaque commande t\'est reversée automatiquement, sans étape manuelle.',
    ],
];

?>

<div class="max-w-[1000px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="font-title text-title text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Comment ça marche&nbsp;?</h1>
    <p class="text-center text-muted text-[0.9rem] mb-8">Que tu cherches une création unique ou que tu sois artiste, voici comment fonctionne Toile.</p>

    <div class="flex items-center justify-center gap-2 mb-10 flex-wrap">
        <?php foreach ($tabs as $key => $label): ?>
            <button type="button" data-hiw-tab="<?= $key ?>"
                class="hiw-tab-btn inline-flex items-center rounded-full border px-5 py-[0.4rem] text-[0.9rem] font-medium transition-colors <?= $key === 'client' ? 'bg-primary text-white border-primary' : 'bg-white text-ink border-border hover:border-primary' ?>">
                <?= htmlspecialchars($label) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div data-hiw-panel="client">
        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[900px]:grid-cols-4 gap-5 mb-10">
            <?php foreach ($clientSteps as $i => $step): ?>
                <div class="bg-white border border-border rounded-md p-5 shadow-sm text-center relative">
                    <?php if ($i === 0): ?>
                        <img src="/assets/images/decor/papier.png" alt="" class="hidden min-[1024px]:block absolute -bottom-24 -left-10 w-[190px] h-auto pointer-events-none select-none opacity-90 z-10">
                    <?php endif; ?>
                    <?php if ($i === 3): ?>
                        <img src="/assets/images/decor/peinture.png" alt="" class="hidden min-[1024px]:block absolute -top-24 -right-10 w-[190px] h-auto pointer-events-none select-none opacity-90 z-10">
                    <?php endif; ?>
                    <span class="absolute top-3 left-3 text-[0.75rem] font-semibold text-muted">0<?= $i + 1 ?></span>
                    <span class="w-12 h-12 rounded-full bg-primary-light text-primary flex items-center justify-center mx-auto mb-3">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><?= $step['icon'] ?></svg>
                    </span>
                    <h3 class="font-semibold text-[0.95rem] text-ink mb-1"><?= htmlspecialchars($step['title']) ?></h3>
                    <p class="text-[0.8rem] text-muted"><?= htmlspecialchars($step['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center">
            <a href="/boutiques" class="btn btn--primary">Découvrir les artistes</a>
        </div>
    </div>

    <div data-hiw-panel="artist" class="hidden">
        <div class="grid grid-cols-1 min-[641px]:grid-cols-2 min-[900px]:grid-cols-4 gap-5 mb-10">
            <?php foreach ($artistSteps as $i => $step): ?>
                <div class="bg-white border border-border rounded-md p-5 shadow-sm text-center relative">
                    <?php if ($i === 0): ?>
                        <img src="/assets/images/decor/papier.png" alt="" class="hidden min-[1024px]:block absolute -bottom-24 -left-10 w-[190px] h-auto pointer-events-none select-none opacity-90 z-10">
                    <?php endif; ?>
                    <?php if ($i === 3): ?>
                        <img src="/assets/images/decor/peinture.png" alt="" class="hidden min-[1024px]:block absolute -top-24 -right-10 w-[190px] h-auto pointer-events-none select-none opacity-90 z-10">
                    <?php endif; ?>
                    <span class="absolute top-3 left-3 text-[0.75rem] font-semibold text-muted">0<?= $i + 1 ?></span>
                    <span class="w-12 h-12 rounded-full bg-primary-light text-primary flex items-center justify-center mx-auto mb-3">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><?= $step['icon'] ?></svg>
                    </span>
                    <h3 class="font-semibold text-[0.95rem] text-ink mb-1"><?= htmlspecialchars($step['title']) ?></h3>
                    <p class="text-[0.8rem] text-muted"><?= htmlspecialchars($step['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center">
            <a href="/become-artist" class="btn btn--primary">Devenir artiste</a>
        </div>
    </div>
</div>

<script>
    (function () {
        var buttons = document.querySelectorAll('[data-hiw-tab]');
        var panels = document.querySelectorAll('[data-hiw-panel]');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.dataset.hiwTab;

                buttons.forEach(function (b) {
                    var isActive = b === btn;
                    b.classList.toggle('bg-primary', isActive);
                    b.classList.toggle('text-white', isActive);
                    b.classList.toggle('border-primary', isActive);
                    b.classList.toggle('bg-white', !isActive);
                    b.classList.toggle('text-ink', !isActive);
                    b.classList.toggle('border-border', !isActive);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.hiwPanel !== target);
                });
            });
        });
    })();
</script>
