<h1>Tirage au sort mensuel</h1>

<p>
    Chaque mois, <?= $maxWinners ?> boutiques sont tirées au sort et mises en avant
    sur la page d'accueil pendant tout le mois.<br>
    Prix de participation : <strong><?= number_format($rafflePrice / 100, 2) ?> €</strong>
    (débité uniquement si tu es sélectionné·e).
</p>

<?php if ($shop === null): ?>
    <p>Tu dois avoir une boutique pour participer.</p>
<?php elseif ($currentEntry !== null): ?>
    <div style="border: 2px solid #6f42c1; border-radius: 8px; padding: 1.5rem; max-width: 400px;">
        <h2>Tu es inscrit·e pour <?= htmlspecialchars($currentMonth) ?> !</h2>
        <p>
            Statut :
            <?php match ($currentEntry['status']) {
                'entered' => print('⏳ En attente du tirage'),
                'selected' => print('🎉 Sélectionné·e !'),
                'not_selected' => print('😔 Non sélectionné·e ce mois-ci'),
                'cancelled' => print('❌ Annulé'),
                default => print($currentEntry['status']),
            }; ?>
        </p>
        <?php if ($currentEntry['status'] === 'entered'): ?>
            <form method="POST" action="/raffle/cancel">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                <button
                    type="submit"
                    onclick="return confirm('Annuler ton inscription ? L\'autorisation Stripe sera annulée.');"
                    style="color: #e53e3e; margin-top: 1rem;">
                    Annuler mon inscription
                </button>
            </form>
        <?php endif; ?>
        <?php if ($currentEntry['status'] === 'selected'): ?>
            <p>Ta boutique est mise en avant sur la page d'accueil ce mois !</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <form method="POST" action="/raffle/enter">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
        <p>
            En participant, tu autorises un prélèvement de
            <strong><?= number_format($rafflePrice / 100, 2) ?> €</strong>
            sur ta carte. Ce montant ne sera débité que si tu es sélectionné·e.
            Sinon, l'autorisation sera annulée sans frais.
        </p>
        <button type="submit">Participer au tirage de <?= htmlspecialchars($currentMonth) ?></button>
    </form>
<?php endif; ?>

<p><a href="/my-shop">← Retour à ma boutique</a></p>