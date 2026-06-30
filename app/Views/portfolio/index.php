<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon portfolio — Toile</title>
</head>

<body>
    <h1>Mon portfolio</h1>

    <?php if ($error !== null): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/my-portfolio/upload" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">

        <label for="images">Ajouter des images</label>
        <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>

        <button type="submit">Uploader</button>
    </form>

    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
        <?php foreach ($images as $image): ?>
            <div>
                <img
                    src="/uploads/portfolio/<?= htmlspecialchars($image['filename']) ?>"
                    alt="Image de portfolio"
                    width="150">
                <form method="POST" action="/my-portfolio/<?= $image['id'] ?>/delete">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                    <button type="submit" onclick="return confirm('Supprimer cette image ?');">Supprimer</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <p><a href="/my-shop">Retour à ma boutique</a></p>
</body>

</html>