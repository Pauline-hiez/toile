<h1>Inscription confirmée ! 🎉</h1>

<p>
    Ton ticket a bien été payé pour le tirage
    <strong><?= $type === 'homepage' ? 'Page d\'accueil' : 'Vitrine boutiques' ?></strong>.<br>

    <?php if ($type === 'homepage'): ?>
        Le tirage a lieu chaque lundi. Tu seras notifié·e du résultat.
    <?php else: ?>
        Le tirage a lieu le 1er de chaque mois. Tu seras notifié·e du résultat.
    <?php endif; ?>
</p>

<a href="/raffle">← Retour aux tirages</a>