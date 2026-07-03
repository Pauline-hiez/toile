<h1>Utilisateurs</h1>
<p><a href="/admin">← Retour au tableau de bord</a></p>

<?php if (empty($users)): ?>
    <p>Aucun utilisateur.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="padding: 0.75rem; text-align: left;">Utilisateur</th>
                <th style="padding: 0.75rem; text-align: left;">Email</th>
                <th style="padding: 0.75rem; text-align: left;">Rôle</th>
                <th style="padding: 0.75rem; text-align: left;">Statut</th>
                <th style="padding: 0.75rem; text-align: left;">Inscrit le</th>
                <th style="padding: 0.75rem; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr style="border-top: 1px solid #e5e7eb; <?= $user['is_banned'] ? 'background: #fff5f5;' : '' ?>">
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($user['username']) ?></td>
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($user['email']) ?></td>
                    <td style="padding: 0.75rem;">
                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" action="/admin/users/<?= $user['id'] ?>/role" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <select name="role">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="artist" <?= $user['role'] === 'artist' ? 'selected' : '' ?>>Artiste</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit">Changer</button>
                            </form>
                        <?php else: ?>
                            <em>Admin (vous)</em>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 0.75rem;">
                        <?= $user['is_banned'] ? '🔴 Suspendu' : '🟢 Actif' ?>
                    </td>
                    <td style="padding: 0.75rem;"><?= htmlspecialchars($user['created_at']) ?></td>
                    <td style="padding: 0.75rem;">
                        <?php if ($user['id'] !== $_SESSION['user_id'] && $user['role'] !== 'admin'): ?>
                            <?php if ($user['is_banned']): ?>
                                <form method="POST" action="/admin/users/<?= $user['id'] ?>/unban">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                    <button type="submit">Réactiver</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="/admin/users/<?= $user['id'] ?>/ban">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                    <button
                                        type="submit"
                                        onclick="return confirm('Suspendre <?= htmlspecialchars($user['username']) ?> ?');"
                                        style="color: #e53e3e;">
                                        Suspendre
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <em>—</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>