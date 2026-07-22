<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::faq()).
 */

$sections = [
    'Général' => [
        [
            'q' => 'Qu\'est-ce que Toile ?',
            'a' => 'Toile est un marketplace de commissions artistiques qui met en relation des artistes talentueux et des passionnés en quête de créations uniques.',
        ],
        [
            'q' => 'Le site est-il gratuit ?',
            'a' => 'Oui, l\'inscription et la navigation sur Toile sont gratuites, aussi bien côté client que côté artiste. Toile se rémunère uniquement via une commission prélevée sur les commandes.',
        ],
    ],
    'Client' => [
        [
            'q' => 'Comment commander une création ?',
            'a' => 'Trouve un artiste via la page Boutiques, puis demande un devis pour un projet sur-mesure ou commande directement une prestation à prix fixe.',
        ],
        [
            'q' => 'Comment fonctionne le paiement sécurisé ?',
            'a' => 'Ton paiement est débité à la commande mais reste bloqué : il n\'est versé à l\'artiste qu\'une fois la création livrée et validée.',
        ],
        [
            'q' => 'Puis-je recevoir ma création par la poste ?',
            'a' => 'Oui, à la commande tu peux cocher l\'option livraison et renseigner ton adresse. Sans cette option, tu reçois ta création au format numérique.',
        ],
        [
            'q' => 'Qu\'est-ce que le tirage au sort ?',
            'a' => 'Chaque mois, des artistes et des boutiques sont mis en avant gratuitement sur le site via un tirage au sort, pour donner de la visibilité à tout le monde.',
        ],
    ],
    'Artiste' => [
        [
            'q' => 'Comment devenir artiste sur Toile ?',
            'a' => 'Depuis ton compte, clique sur "Devenir artiste" et remplis le formulaire de demande. Une fois validée, tu peux créer ta boutique et publier tes prestations.',
        ],
        [
            'q' => 'Toile prend-elle une commission ?',
            'a' => 'Oui, une commission est prélevée sur chaque commande. Son taux dépend de l\'abonnement choisi : plus l\'abonnement est élevé, plus la commission est réduite.',
        ],
        [
            'q' => 'Comment reçois-je l\'argent de mes commandes ?',
            'a' => 'Connecte ton compte bancaire depuis ton espace artiste (page Mes paiements). Ta part de chaque commande t\'est ensuite reversée automatiquement.',
        ],
    ],
];
?>

<div class="max-w-[900px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Aide et FAQ</h1>
    <p class="text-center text-muted text-[0.9rem] mb-10">Les réponses aux questions les plus fréquentes sur Toile.</p>

    <?php
    $sectionDecor = [
        'Général' => ['side' => 'left', 'tache' => 'tache7.png', 'plante' => 'plante1.png', 'tacheSize' => 160, 'planteSize' => 100],
        'Client' => ['side' => 'right', 'tache' => 'tache8.png', 'plante' => 'plante5.png', 'tacheSize' => 260, 'planteSize' => 160],
        'Artiste' => ['side' => 'left', 'tache' => 'tache8.png', 'plante' => 'plante8.png', 'tacheSize' => 340, 'planteSize' => 210],
    ];
    ?>

    <?php foreach ($sections as $sectionTitle => $items): ?>
        <?php $decor = $sectionDecor[$sectionTitle] ?? null; ?>
        <h2 class="font-title text-[1.5rem] text-primary mb-4"><?= htmlspecialchars($sectionTitle) ?></h2>

        <?php
        $tacheOffset = $decor !== null ? -round($decor['tacheSize'] * 0.3) : 0;
        $planteOffset = $decor !== null ? -round($decor['planteSize'] * 0.28) : 0;
        ?>
        <div class="relative mb-10">
            <?php if ($decor !== null && $decor['side'] === 'left'): ?>
                <img src="/assets/images/decor/<?= $decor['tache'] ?>" alt="" style="width: <?= $decor['tacheSize'] ?>px; bottom: <?= $tacheOffset ?>px; left: <?= $tacheOffset ?>px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
                <img src="/assets/images/decor/<?= $decor['plante'] ?>" alt="" style="width: <?= $decor['planteSize'] ?>px; bottom: <?= $planteOffset ?>px; left: <?= $planteOffset ?>px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">
            <?php elseif ($decor !== null): ?>
                <img src="/assets/images/decor/<?= $decor['tache'] ?>" alt="" style="width: <?= $decor['tacheSize'] ?>px; bottom: <?= $tacheOffset ?>px; right: <?= $tacheOffset ?>px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
                <img src="/assets/images/decor/<?= $decor['plante'] ?>" alt="" style="width: <?= $decor['planteSize'] ?>px; bottom: <?= $planteOffset ?>px; right: <?= $planteOffset ?>px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">
            <?php endif; ?>

            <div class="bg-white border border-border rounded-md overflow-hidden shadow-sm">
            <?php foreach ($items as $i => $item): ?>
                <div class="<?= $i > 0 ? 'border-t border-border' : '' ?>">
                    <button type="button" data-faq-toggle
                        class="w-full flex items-center justify-between gap-4 p-5 text-left bg-transparent border-0 cursor-pointer">
                        <span class="font-semibold text-[0.9rem] text-ink"><?= htmlspecialchars($item['q']) ?></span>
                        <svg data-faq-chevron viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-muted shrink-0 transition-transform">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div data-faq-panel class="hidden px-5 pb-5">
                        <p class="text-[0.85rem] text-muted"><?= htmlspecialchars($item['a']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    (function () {
        document.querySelectorAll('[data-faq-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var panel = btn.nextElementSibling;
                var chevron = btn.querySelector('[data-faq-chevron]');
                var isOpen = !panel.classList.contains('hidden');

                panel.classList.toggle('hidden', isOpen);
                chevron.classList.toggle('rotate-180', !isOpen);
            });
        });
    })();
</script>
