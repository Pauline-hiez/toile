<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject ?? 'Toile') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: #6f42c1;
            color: white;
            padding: 1.5rem 2rem;
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .body {
            padding: 2rem;
            color: #374151;
            line-height: 1.6;
        }

        .footer {
            background: #f3f4f6;
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #6f42c1;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Toile 🎨</h1>
        </div>
        <div class="body">
            <?= $content ?>
        </div>
        <div class="footer">
            © <?= date('Y') ?> Toile — La marketplace de commissions artistiques.<br>
            Tu reçois cet email car tu es inscrit(e) sur Toile.
        </div>
    </div>
</body>

</html>