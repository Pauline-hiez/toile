<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::rules()).
 */

$sections = [
    [
        'title' => '1. Objet',
        'body' => "Ce règlement intérieur définit les règles de comportement et de bonne conduite applicables à tous les utilisateurs de Toile, clients comme artistes. Il complète les Conditions d'utilisation du site.",
    ],
    [
        'title' => '2. Comportement attendu de tous les utilisateurs',
        'body' => "Le respect mutuel est la base des échanges sur Toile. Tout propos injurieux, discriminatoire, harcelant ou menaçant envers un autre utilisateur, quel que soit son rôle, est strictement interdit et peut entraîner une suspension immédiate du compte.",
    ],
    [
        'title' => '3. Engagements du client',
        'body' => "Le client s'engage à communiquer clairement et de bonne foi ses attentes, à respecter le temps et le travail de l'artiste, à régler ses commandes dans les délais convenus, et à ne pas formuler de demandes abusives ou de pression indue sur l'artiste (délais irréalistes, modifications excessives, menaces en cas de désaccord).",
    ],
    [
        'title' => '4. Engagements de l\'artiste',
        'body' => "L'artiste s'engage à fournir un travail sérieux et de qualité, à respecter les délais annoncés au client ou à le prévenir en cas de retard, et à maintenir une communication claire et régulière tout au long de la réalisation de la commande.",
    ],
    [
        'title' => '5. Originalité des créations — interdiction du contenu généré par IA',
        'body' => "L'artiste s'engage à ce que toute création vendue sur Toile soit réalisée par ses propres moyens (dessin traditionnel ou numérique, peinture, illustration, etc.), à l'exclusion de tout visuel généré ou substantiellement produit par une intelligence artificielle générative (Midjourney, DALL-E, Stable Diffusion ou tout outil similaire). Toute création dont il serait avéré qu'elle a été générée par IA entraîne la suspension immédiate du compte concerné et l'annulation des commandes en cours.",
    ],
    [
        'title' => '6. Signalement',
        'body' => "Tout comportement contraire à ce règlement, qu'il émane d'un client ou d'un artiste, peut être signalé à notre support depuis la page Aide et FAQ. Chaque signalement est examiné individuellement avant toute décision.",
    ],
    [
        'title' => '7. Sanctions',
        'body' => "Selon la gravité et la répétition des faits constatés, Toile peut prononcer un avertissement, une suspension temporaire ou une résiliation définitive du compte concerné, sans préjudice d'éventuelles poursuites en cas d'infraction à la loi.",
    ],
    [
        'title' => '8. Modification du règlement',
        'body' => "Ce règlement intérieur peut être mis à jour à tout moment. Toute modification substantielle sera communiquée aux utilisateurs avant son entrée en vigueur.",
    ],
];
?>

<div class="max-w-[800px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10 relative">
    <img src="/assets/images/decor/tache7.png" alt="" style="width: 200px; top: 6%; right: -60px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante4.png" alt="" style="width: 120px; top: 8%; right: -32px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <img src="/assets/images/decor/tache8.png" alt="" style="width: 240px; top: 34%; left: -72px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante9.png" alt="" style="width: 140px; top: 37%; left: -40px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">

    <img src="/assets/images/decor/tache7.png" alt="" style="width: 250px; top: 68%; right: -76px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante7.png" alt="" style="width: 145px; top: 71%; right: -40px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Règlement intérieur</h1>
    <p class="text-center text-muted text-[0.85rem] mb-10">Dernière mise à jour&nbsp;: <?= \App\Core\FrenchDate::format('d MMMM y', date('Y-m-d')) ?></p>

    <div class="bg-white border border-border rounded-md shadow-sm p-6 min-[641px]:p-10 flex flex-col gap-8">
        <?php foreach ($sections as $section): ?>
            <div>
                <h2 class="font-semibold text-[1rem] text-ink mb-2"><?= htmlspecialchars($section['title']) ?></h2>
                <p class="text-[0.9rem] text-muted leading-relaxed"><?= htmlspecialchars($section['body']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
