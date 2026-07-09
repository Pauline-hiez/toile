<h1>Tirages au sort</h1>

<?php if ($shop === null): ?>
    <p>Tu dois avoir une boutique pour participer.</p>
<?php else: ?>

    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">

        <!-- Tirage A : Vitrine boutiques -->
        <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 280px;">
            <h2>🏪 Vitrine boutiques</h2>
            <p>10 boutiques mises en avant en tête de la page Artistes pendant tout le mois.</p>
            <p><strong>Prix : <?= number_format($rafflePrice / 100, 2) ?> €</strong> (débité si sélectionné)</p>
            <p style="font-size: 0.85rem; color: #666;">Période : <?= htmlspecialchars($currentMonth) ?></p>

            <?php if ($boutiqueEntry !== null): ?>
                <p>
                    Statut :
                    <?php match ($boutiqueEntry['status']) {
                        'entered' => print('⏳ En attente du tirage'),
                        'selected' => print('🎉 Sélectionné·e !'),
                        'not_selected' => print('😔 Non sélectionné·e'),
                        'cancelled' => print('❌ Annulé'),
                        default => print($boutiqueEntry['status']),
                    }; ?>
                </p>
                <?php if ($boutiqueEntry['status'] === 'entered'): ?>
                    <form method="POST" action="/raffle/cancel">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                        <input type="hidden" name="type" value="boutiques">
                        <button type="submit" onclick="return confirm('Annuler l\'inscription ?');" style="color: #e53e3e;">
                            Annuler mon inscription
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <form method="POST" action="/raffle/enter">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <input type="hidden" name="type" value="boutiques">
                    <button type="submit">Participer (<?= number_format($rafflePrice / 100, 2) ?> €)</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Tirage B : Page d'accueil -->
        <div style="border: 1px solid #6f42c1; border-radius: 8px; padding: 1.5rem; min-width: 280px;">
            <h2>🏠 Page d'accueil</h2>
            <p>5 boutiques mises en avant sur la page d'accueil pendant 7 jours.</p>
            <p><strong>Prix : <?= number_format($homepagePrice / 100, 2) ?> €</strong> (débité si sélectionné)</p>
            <p style="font-size: 0.85rem; color: #666;">Semaine du : <?= htmlspecialchars($currentMonday) ?></p>

            <?php if ($homepageEntry !== null): ?>
                <p>
                    Statut :
                    <?php match ($homepageEntry['status']) {
                        'entered' => print('⏳ En attente du tirage'),
                        'selected' => print('🎉 Sélectionné·e ! Mise en avant jusqu\'au ' . htmlspecialchars($homepageEntry['featured_until'] ?? '')),
                        'not_selected' => print('😔 Non sélectionné·e'),
                        'cancelled' => print('❌ Annulé'),
                        default => print($homepageEntry['status']),
                    }; ?>
                </p>
                <?php if ($homepageEntry['status'] === 'entered'): ?>
                    <form method="POST" action="/raffle/cancel">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                        <input type="hidden" name="type" value="homepage">
                        <button type="submit" onclick="return confirm('Annuler l\'inscription ?');" style="color: #e53e3e;">
                            Annuler mon inscription
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <form method="POST" action="/raffle/enter">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <input type="hidden" name="type" value="homepage">
                    <button type="submit">Participer (<?= number_format($homepagePrice / 100, 2) ?> €)</button>
                </form>
            <?php endif; ?>
        </div>

    </div>

<?php endif; ?>

<p><a href="/my-shop">← Retour à ma boutique</a></p>