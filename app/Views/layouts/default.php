<?php
$unreadCount = 0;
$userShop = null;
if (isset($_SESSION['user_id'])) {
    $notificationModel = new \App\Models\Notification();
    $unreadCount = $notificationModel->countUnread($_SESSION['user_id']);

    if (in_array($_SESSION['user_role'] ?? '', ['artist', 'admin'], true)) {
        $shopModel = new \App\Models\Shop();
        $userShop = $shopModel->findByUserId($_SESSION['user_id']);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Toile') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
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

    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>