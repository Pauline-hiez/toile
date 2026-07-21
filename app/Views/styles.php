<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::styles()).
 *
 * @var array<int, array{name: string, image: string|null}> $styleTiles Tous les styles (fixes + validés par l'admin).
 */
$pageTitle = 'Tous les styles — Toile';
?>

<div class="max-w-[1400px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="font-title text-title text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Tous les styles</h1>
    <p class="text-center text-muted text-[0.9rem] mb-10">Explore l'ensemble des styles artistiques disponibles sur Toile.</p>

    <div class="grid grid-cols-2 min-[641px]:grid-cols-3 min-[1100px]:grid-cols-5 gap-6">
        <?php foreach ($styleTiles as $tile): ?>
            <?php $tileImage = $tile['image'] ? htmlspecialchars($tile['image']) : '/assets/images/default/style.png'; ?>
            <a href="/boutiques?style=<?= urlencode($tile['name']) ?>" class="group relative flex items-end h-[200px] rounded-md overflow-hidden bg-primary-light shadow-sm no-underline transition hover:shadow-[0_4px_16px_rgba(0,0,0,0.12)] hover:-translate-y-0.5">
                <img src="<?= $tileImage ?>" alt="" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[250ms] ease group-hover:scale-105">
                <span class="absolute inset-0 bg-gradient-to-b from-transparent from-40% to-black/55"></span>
                <span class="relative z-10 w-full p-4 font-cursive text-[1.1rem] font-semibold text-white"><?= htmlspecialchars(ucfirst($tile['name'])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
