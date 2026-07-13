<?php
/**
 * Page de maintenance, rendue sans layout (voir public/index.php).
 *
 * @var string $message
 */
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Maintenance — Toile') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?>">
    <style>
        .maintenance-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }

        .maintenance-page__box {
            max-width: 480px;
        }

        .maintenance-page__box img {
            width: 96px;
            margin-bottom: 1.5rem;
        }

        .maintenance-page__box h1 {
            font-family: var(--font-title);
            font-size: 2.5rem;
            color: var(--color-title);
            margin-bottom: 1rem;
        }

        .maintenance-page__box p {
            color: var(--color-text-muted);
            font-size: 1.05rem;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="maintenance-page">
        <div class="maintenance-page__box">
            <img src="/assets/images/site/logo-toile.png" alt="Toile">
            <h1>Site en maintenance</h1>
            <p><?= nl2br(htmlspecialchars($message)) ?></p>
        </div>
    </div>
</body>

</html>
