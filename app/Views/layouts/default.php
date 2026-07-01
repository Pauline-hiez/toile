<?php
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    $notificationModel = new \App\Models\Notification();
    $unreadCount = $notificationModel->countUnread($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Toile') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (!empty($extraHead)): ?>
        <?= $extraHead ?>
    <?php endif; ?>
</head>

<body>
    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>