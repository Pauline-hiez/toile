<?php
$unreadCount = 0;
$recentNotifications = [];
$userShop = null;
$currentUserAvatar = null;
if (isset($_SESSION['user_id'])) {
    $notificationModel = new \App\Models\Notification();
    $unreadCount = $notificationModel->countUnread($_SESSION['user_id']);
    $recentNotifications = $notificationModel->findRecentByUserId($_SESSION['user_id']);

    // $_SESSION['user_avatar'] n'est jamais renseigné à la connexion — on
    // relit l'avatar courant depuis la base à chaque page (comme pour
    // $unreadCount/$userShop ci-dessus), pour rester à jour même après
    // un changement de photo sans reconnexion.
    $userModel = new \App\Models\User();
    $currentUser = $userModel->findById($_SESSION['user_id']);
    $currentUserAvatar = $currentUser['avatar'] ?? null;

    if (in_array($_SESSION['user_role'] ?? '', ['artist', 'admin'], true)) {
        $shopModel = new \App\Models\Shop();
        $userShop = $shopModel->findByUserId($_SESSION['user_id']);
    }
}

$settingModel = new \App\Models\Setting();
$siteFavicon = $settingModel->get('site_favicon');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? ($settingModel->get('site_name', 'Toile'))) ?></title>
    <link rel="icon" href="<?= $siteFavicon ? '/uploads/branding/' . htmlspecialchars($siteFavicon) : '/assets/images/site/favicon-logo.png' ?>">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/tailwind-config.js"></script>
    <?php if (!empty($extraHead)): ?>
        <?= $extraHead ?>
    <?php endif; ?>
</head>

<body>
    <img src="/assets/images/decor/tache6.png" alt="" class="hidden min-[1024px]:block fixed bottom-0 -left-16 w-[480px] h-auto pointer-events-none select-none opacity-40 -z-10">

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main class="site-main">
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <script src="/assets/js/notif-dropdown.js"></script>
    <?php else: ?>
        <?php require __DIR__ . '/../components/auth-modal.php'; ?>
        <script src="/assets/js/password-toggle.js"></script>
        <script src="/assets/js/auth-modal.js"></script>
    <?php endif; ?>

    <?php require __DIR__ . '/../components/contact-modal.php'; ?>
    <script src="/assets/js/input-sparks.js"></script>
    <script src="/assets/js/contact-modal.js"></script>
</body>

</html>