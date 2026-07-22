<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::salesTerms()).
 */

$sections = [
    [
        'title' => '1. Champ d\'application',
        'body' => "Les présentes conditions générales de vente (CGV) régissent les ventes de créations et prestations artistiques conclues entre un artiste (vendeur) et un client (acheteur) par l'intermédiaire de la plateforme Toile. Elles complètent les Conditions d'utilisation du site.",
    ],
    [
        'title' => '2. Prix',
        'body' => "Les prix des prestations sont fixés librement par chaque artiste et affichés en euros, toutes taxes comprises le cas échéant. Toile n'intervient pas dans la fixation des prix, hormis la commission de plateforme prélevée sur chaque commande.",
    ],
    [
        'title' => '3. Commande',
        'body' => "Une commande est conclue soit par l'achat direct d'une prestation à prix fixe, soit par l'acceptation d'un devis personnalisé proposé par l'artiste suite à une demande du client. La validation de la commande vaut acceptation du prix et de la description de la prestation.",
    ],
    [
        'title' => '4. Paiement',
        'body' => "Le paiement s'effectue en ligne, en totalité, au moment de la commande, via notre prestataire de paiement sécurisé. Les fonds sont conservés par Toile et reversés à l'artiste uniquement après livraison et validation de la création par le client.",
    ],
    [
        'title' => '5. Livraison',
        'body' => "Sauf option de livraison physique choisie et renseignée par le client à la commande, les créations sont livrées au format numérique directement sur la plateforme. Les délais de réalisation sont indiqués par l'artiste et peuvent varier selon la complexité de la prestation.",
    ],
    [
        'title' => '6. Droit de rétractation',
        'body' => "Conformément à l'article L221-28 du Code de la consommation, le droit de rétractation ne s'applique pas aux créations réalisées sur-mesure selon les spécifications du client. En passant commande d'une création personnalisée, le client reconnaît et accepte cette exception.",
    ],
    [
        'title' => '7. Garanties',
        'body' => "Les créations vendues sur Toile bénéficient des garanties légales de conformité applicables. En cas de non-conformité manifeste de la création livrée par rapport à la commande convenue, le client peut solliciter une correction auprès de l'artiste ou saisir Toile en cas de litige.",
    ],
    [
        'title' => '8. Rôle de Toile',
        'body' => "Toile agit en qualité d'intermédiaire technique entre l'artiste et le client, notamment pour la mise en relation et la sécurisation du paiement. Le contrat de vente est conclu directement entre l'artiste et le client ; Toile n'est pas partie à ce contrat.",
    ],
    [
        'title' => '9. Réclamations et litiges',
        'body' => "Toute réclamation relative à une commande peut être adressée à notre support depuis la page Aide et FAQ. En cas de litige persistant entre un client et un artiste, Toile peut intervenir en tant que médiateur, sans que cela ne constitue une obligation de résultat.",
    ],
    [
        'title' => '10. Droit applicable',
        'body' => "Les présentes CGV sont soumises au droit français. En cas de litige, une solution amiable sera recherchée en priorité avant toute action judiciaire.",
    ],
];
?>

<div class="max-w-[800px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10 relative">
    <img src="/assets/images/decor/tache7.png" alt="" style="width: 210px; top: 4%; right: -62px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante5.png" alt="" style="width: 125px; top: 7%; right: -34px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <img src="/assets/images/decor/tache8.png" alt="" style="width: 240px; top: 32%; left: -70px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante8.png" alt="" style="width: 140px; top: 35%; left: -38px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">

    <img src="/assets/images/decor/tache7.png" alt="" style="width: 250px; top: 64%; right: -76px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante6.png" alt="" style="width: 145px; top: 67%; right: -40px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <img src="/assets/images/decor/tache8.png" alt="" style="width: 270px; top: 90%; left: -80px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante2.png" alt="" style="width: 155px; top: 93%; left: -44px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">

    <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Conditions générales de vente</h1>
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
