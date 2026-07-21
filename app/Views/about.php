<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::about()).
 */

$values = [
    [
        'icon' => '<path d="M12 19c-4-2.5-7-5.4-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 3.6-3 6.5-7 9Z"></path>',
        'title' => 'Créations 100% humaines',
        'text' => "Chaque création vendue sur Toile est réalisée à la main par un artiste, sans recours à l'intelligence artificielle générative.",
    ],
    [
        'icon' => '<rect x="4" y="10" width="16" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>',
        'title' => 'Paiement sécurisé',
        'text' => "Les fonds sont bloqués jusqu'à la livraison de la commande, pour une confiance mutuelle entre client et artiste.",
    ],
    [
        'icon' => '<circle cx="12" cy="12" r="9"></circle><path d="M9 15s.8 1 3 1 3-.9 3-2-1.5-1.7-3-2-3-.9-3-2 1.3-2 3-2 3 1 3 1"></path>',
        'title' => 'Rémunération juste',
        'text' => "Une commission raisonnable et dégressive selon l'abonnement, pour que les artistes gardent l'essentiel de leur travail.",
    ],
    [
        'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="10" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'title' => 'Communauté solidaire',
        'text' => "Des tirages au sort réguliers mettent en avant des artistes et des boutiques gratuitement, pour donner sa chance à chacun.",
    ],
];
?>

<div class="max-w-[900px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10 relative">
    <img src="/assets/images/decor/tache7.png" alt="" style="width: 210px; top: 2%; left: -64px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante1.png" alt="" style="width: 125px; top: 5%; left: -34px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">

    <img src="/assets/images/decor/tache8.png" alt="" style="width: 260px; bottom: -6%; right: -78px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante3.png" alt="" style="width: 150px; bottom: -2%; right: -42px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <h1 class="font-title text-title text-[2rem] min-[641px]:text-[2.4rem] text-center mb-6">Qui sommes-nous&nbsp;?</h1>

    <p class="text-center text-ink text-[0.95rem] leading-relaxed max-w-[640px] mx-auto mb-14">
        Toile est né d'une conviction simple : les créations artistiques méritent d'être valorisées et rémunérées à leur juste prix, dans un cadre sécurisé et humain.
        Nous mettons en relation des artistes talentueux et des passionnés en quête de créations uniques, sans jamais perdre de vue l'humain derrière chaque coup de pinceau.
    </p>

    <h2 class="font-title text-primary text-[1.6rem] text-center mb-8">Nos valeurs</h2>

    <div class="grid grid-cols-1 min-[641px]:grid-cols-2 gap-5 mb-14">
        <?php foreach ($values as $value): ?>
            <div class="bg-white border border-border rounded-md p-5 shadow-sm text-center">
                <span class="w-12 h-12 rounded-full bg-primary-light text-primary flex items-center justify-center mx-auto mb-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><?= $value['icon'] ?></svg>
                </span>
                <h3 class="font-semibold text-[0.95rem] text-ink mb-1"><?= htmlspecialchars($value['title']) ?></h3>
                <p class="text-[0.8rem] text-muted"><?= htmlspecialchars($value['text']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex items-center justify-center gap-3 flex-wrap">
        <a href="/boutiques" class="btn btn--primary">Découvrir les artistes</a>
        <a href="/become-artist" class="btn btn--outline">Devenir artiste</a>
    </div>
</div>
