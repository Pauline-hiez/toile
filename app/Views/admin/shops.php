<h1>Boutiques</h1>
<p><a href="/admin">← Retour au tableau de bord</a></p>

<?php if (empty($shops)): ?>
    <p>Aucune boutique.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="padding: 0.75rem; text-align: left;">Boutique</th>
                <th style="padding: 0.75rem; text-align: left;">Propriétaire</th>
                <th style="padding: 0.75rem; text-align: left;">Statut</th>
                <th style="padding: 0.75rem; text-align: left;">Créée le</th>
                <th style="padding: 0.75rem; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shops as $shop): ?>
                <tr style="border-top: 1px solid #e5e7eb;">
                    <td style="padding: 0.75rem;">
                        <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>">
                            <?= htmlspecialchars($shop['name']) ?>
                        </a>
                    </td>
                    <td style="padding: 0.75rem;">
                        <?= htmlspecialchars($shop['owner_name']) ?>
                        <span style="color: #666; font-size: 0.85rem;">(<?= htmlspecialchars($shop['owner_email']) ?>)</span>
                    </td>
                    <td style="padding: 0.75rem;">
                        <?= $shop['is_open'] ? '🟢 Ouverte' : '🔴 Fermée' ?>
                    </td>
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($shop['created_at']) ?></td>
                    <td style="padding: 0.75rem;">
                        <form method="POST" action="/admin/shops/<?= $shop['id'] ?>/delete">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <button
                                type="submit"
                                onclick="return confirm('Supprimer la boutique <?= htmlspecialchars($shop['name']) ?> et tout son contenu ?');"
                                style="color: #e53e3e;">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>