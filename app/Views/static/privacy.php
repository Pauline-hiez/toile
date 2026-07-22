<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::privacy()).
 */

$sections = [
    [
        'title' => '1. Préambule',
        'body' => "Toile attache une grande importance à la protection des données personnelles de ses utilisateurs. Cette politique explique quelles données sont collectées, pourquoi, et quels droits tu peux exercer sur celles-ci.",
    ],
    [
        'title' => '2. Responsable du traitement',
        'body' => "Toile est responsable du traitement des données personnelles collectées sur la plateforme, conformément au Règlement Général sur la Protection des Données (RGPD).",
    ],
    [
        'title' => '3. Données collectées',
        'body' => "Nous collectons les données que tu fournis lors de ton inscription (nom d'utilisateur, e-mail, mot de passe), les informations de ta boutique si tu es artiste, ainsi que les données liées à tes commandes, tes messages et, si tu choisis la livraison physique, ton adresse postale.",
    ],
    [
        'title' => '4. Finalités du traitement',
        'body' => "Ces données sont utilisées pour créer et gérer ton compte, permettre les commandes et le paiement entre clients et artistes, assurer le suivi et la livraison des créations, et t'envoyer des notifications relatives à ton activité sur la plateforme.",
    ],
    [
        'title' => '5. Base légale',
        'body' => "Le traitement de tes données repose sur l'exécution du contrat qui te lie à Toile lorsque tu utilises la plateforme, ainsi que sur ton consentement pour les communications facultatives (newsletters, notifications non essentielles).",
    ],
    [
        'title' => '6. Partage des données',
        'body' => "Tes données de paiement sont traitées directement par notre prestataire de paiement sécurisé (Stripe) et ne sont jamais stockées par Toile. Certaines données peuvent être partagées avec notre hébergeur dans le seul cadre du fonctionnement technique du site.",
    ],
    [
        'title' => '7. Durée de conservation',
        'body' => "Tes données sont conservées le temps de ton inscription sur la plateforme. En cas de suppression de compte, elles sont supprimées ou anonymisées, sous réserve des obligations légales de conservation (facturation notamment).",
    ],
    [
        'title' => '8. Cookies',
        'body' => "Toile utilise des cookies strictement nécessaires au fonctionnement du site (session, authentification). Aucun cookie publicitaire ou de traçage tiers n'est utilisé.",
    ],
    [
        'title' => '9. Sécurité des données',
        'body' => "Des mesures techniques et organisationnelles sont mises en place pour protéger tes données contre tout accès, modification ou divulgation non autorisés, notamment le chiffrement des mots de passe et des communications.",
    ],
    [
        'title' => '10. Tes droits',
        'body' => "Conformément au RGPD, tu disposes d'un droit d'accès, de rectification, d'effacement, d'opposition et de portabilité de tes données. Tu peux exercer ces droits à tout moment depuis ton espace profil ou en nous contactant.",
    ],
    [
        'title' => '11. Mineurs',
        'body' => "Toile est réservée aux personnes majeures. Nous ne collectons pas sciemment de données concernant des mineurs.",
    ],
    [
        'title' => '12. Modification de la politique',
        'body' => "Cette politique de confidentialité peut être mise à jour. Toute modification substantielle te sera communiquée avant son entrée en vigueur.",
    ],
    [
        'title' => '13. Contact',
        'body' => "Pour toute question relative à tes données personnelles ou pour exercer tes droits, tu peux nous contacter depuis la page Aide et FAQ.",
    ],
];
?>

<div class="max-w-[800px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10 relative">
    <img src="/assets/images/decor/tache8.png" alt="" style="width: 210px; top: 6%; left: -62px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante7.png" alt="" style="width: 120px; top: 8%; left: -32px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <img src="/assets/images/decor/tache7.png" alt="" style="width: 230px; top: 30%; right: -68px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante9.png" alt="" style="width: 130px; top: 33%; right: -36px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <img src="/assets/images/decor/tache8.png" alt="" style="width: 240px; top: 58%; left: -72px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante12.png" alt="" style="width: 140px; top: 61%; left: -40px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10 -scale-x-100">

    <img src="/assets/images/decor/tache7.png" alt="" style="width: 260px; top: 84%; right: -78px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-30 -z-10">
    <img src="/assets/images/decor/plante1.png" alt="" style="width: 150px; top: 87%; right: -42px" class="hidden min-[1024px]:block absolute h-auto pointer-events-none select-none opacity-90 -z-10">

    <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Politique de confidentialité</h1>
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
