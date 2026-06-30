<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Devenir artiste — Toile</title>
</head>

<body>
    <h1>Devenir artiste sur Toile</h1>

    <?php if (isset($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($user['role'] === 'artist'): ?>
        <p>Tu es déjà artiste sur la plateforme.</p>
    <?php elseif ($user['artist_request_status'] === 'pending'): ?>
        <p>Ta demande est en cours d'examen. Tu seras notifié(e) dès qu'elle sera traitée.</p>
    <?php elseif ($user['artist_request_status'] === 'rejected'): ?>
        <p>Ta précédente demande n'a pas été retenue. Tu peux soumettre une nouvelle demande ci-dessous.</p>
    <?php endif; ?>

    <?php if ($user['role'] !== 'artist' && $user['artist_request_status'] !== 'pending'): ?>
        <form method="POST" action="/become-artist">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

            <p>
                En soumettant cette demande, tu confirmes vouloir proposer
                tes services artistiques sur Toile. Un administrateur
                examinera ta demande prochainement.
            </p>

            <button type="submit">Envoyer ma demande</button>
        </form>
    <?php endif; ?>

    <p><a href="/">Retour à l'accueil</a></p>
</body>

</html>