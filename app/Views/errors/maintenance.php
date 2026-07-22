<?php
/**
 * Page de maintenance, rendue sans layout (voir public/index.php).
 * L'illustration occupe toute la page en fond ; le texte (logo, titre,
 * compte à rebours) est superposé sur sa partie gauche, dégagée. Le
 * compte à rebours réutilise le même script que celui des tirages au
 * sort (public/assets/js/admin-countdown.js, attribut data-countdown).
 *
 * @var string|null $endsAt Date/heure de fin prévue ("Y-m-d H:i:s"), ou
 *                           null/vide si aucune date n'a été renseignée.
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
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .maintenance-page__bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            z-index: 0;
        }

        .maintenance-page__logo {
            position: absolute;
            top: 8%;
            left: min(8vw, 90px);
            z-index: 1;
            width: 210px;
            height: auto;
        }

        .maintenance-page__overlay {
            position: absolute;
            top: 50%;
            left: min(8vw, 90px);
            transform: translateY(-50%);
            z-index: 1;
            max-width: 480px;
            padding: 2rem;
        }

        .maintenance-page__overlay h1 {
            font-family: var(--font-title);
            font-size: 2.6rem;
            line-height: 1.15;
            color: var(--color-title);
            margin-bottom: 1.25rem;
        }

        .maintenance-page__overlay p:not(.maintenance-page__countdown) {
            color: var(--color-text);
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .maintenance-page__countdown {
            font-size: 2rem !important;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-top: 0.25rem !important;
        }

        @media (max-width: 900px) {
            .maintenance-page__overlay {
                max-width: 90vw;
                left: 5vw;
                background: rgba(255, 251, 241, 0.82);
                border-radius: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="maintenance-page">
        <img src="/assets/images/decor/maintenance.png" alt="" class="maintenance-page__bg">

        <img src="/assets/images/site/logo-toile.png" alt="Toile" class="maintenance-page__logo">

        <div class="maintenance-page__overlay">
            <h1 class="text-shine">Site en maintenance</h1>
            <?php if (!empty($endsAt)): ?>
                <p>On se retrouve dans :</p>
                <p class="maintenance-page__countdown" data-countdown="<?= htmlspecialchars($endsAt) ?>">--J : --H : --MIN : --S</p>
            <?php else: ?>
                <p>Nous serons bientôt de retour, merci de votre patience.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="/assets/js/admin-countdown.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/admin-countdown.js') ?>"></script>
</body>

</html>
