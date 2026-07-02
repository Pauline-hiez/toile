<h1>Avis</h1>
<p><a href="/admin">← Retour au tableau de bord</a></p>

<?php if (empty($reviews)): ?>
    <p>Aucun avis.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="padding: 0.75rem; text-align: left;">Commande</th>
                <th style="padding: 0.75rem; text-align: left;">Boutique</th>
                <th style="padding: 0.75rem; text-align: left;">Client</th>
                <th style="padding: 0.75rem; text-align: left;">Note</th>
                <th style="padding: 0.75rem; text-align: left;">Commentaire</th>
                <th style="padding: 0.75rem; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reviews as $review): ?>
                <tr style="border-top: 1px solid #e5e7eb;">
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($review['order_title']) ?></td>
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($review['shop_name']) ?></td>
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($review['client_name']) ?></td>
                    <td style="padding: 0.75rem;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $review['rating'] ? '⭐' : '☆' ?>
                        <?php endfor; ?>
                    </td>
                    <td style="padding: 0.75rem;">
                        <?= !empty($review['comment'])
                            ? htmlspecialchars(mb_substr($review['comment'], 0, 80)) . '...'
                            : '<em>Aucun commentaire</em>' ?>
                    </td>
                    <td style="padding: 0.75rem;">
                        <form method="POST" action="/admin/reviews/<?= $review['id'] ?>/delete">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <button
                                type="submit"
                                onclick="return confirm('Supprimer cet avis ?');"
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