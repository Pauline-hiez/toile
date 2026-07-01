<h1>Notifications</h1>

<?php if (empty($notifications)): ?>
    <p>Tu n'as aucune notification pour l'instant.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($notifications as $notification): ?>
            <li style="
                padding: 0.75rem;
                margin-bottom: 0.5rem;
                border-radius: 6px;
                background: <?= $notification['is_read'] ? '#f9fafb' : '#ede9fe' ?>;
                border-left: 3px solid <?= $notification['is_read'] ? '#d1d5db' : '#6f42c1' ?>;
            ">
                <?php if ($notification['link']): ?>
                    <a href="<?= htmlspecialchars($notification['link']) ?>">
                        <?= htmlspecialchars($notification['message']) ?>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($notification['message']) ?>
                <?php endif; ?>

                <span style="float: right; font-size: 0.8rem; color: #666;">
                    <?= htmlspecialchars($notification['created_at']) ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p><a href="/">Retour à l'accueil</a></p>