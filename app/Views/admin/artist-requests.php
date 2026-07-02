<h1>Demandes artiste en attente</h1>

<p><a href="/admin">← Retour au tableau de bord</a></p>

<?php if (empty($requests)): ?>
    <p>Aucune demande en attente.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="padding: 0.75rem; text-align: left;">Utilisateur</th>
                <th style="padding: 0.75rem; text-align: left;">Email</th>
                <th style="padding: 0.75rem; text-align: left;">Inscrit le</th>
                <th style="padding: 0.75rem; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
                <tr style="border-top: 1px solid #e5e7eb;">
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($request['username']) ?></td>
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($request['email']) ?></td>
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($request['created_at']) ?></td>
                    <td style="padding: 0.75rem;">
                        <form method="POST" action="/admin/artist-requests/<?= $request['id'] ?>/approve" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <button type="submit" onclick="return confirm('Valider la demande de <?= htmlspecialchars($request['username']) ?> ?');">
                                ✅ Valider
                            </button>
                        </form>

                        <form method="POST" action="/admin/artist-requests/<?= $request['id'] ?>/reject" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                            <button type="submit" onclick="return confirm('Refuser la demande de <?= htmlspecialchars($request['username']) ?> ?');">
                                ❌ Refuser
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>