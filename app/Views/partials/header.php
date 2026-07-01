<header>
    <nav>
        <a href="/"><strong>Toile</strong></a>

        <a href="/boutiques">Découvrir les artistes</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/profile">Mon profil</a>
            <a href="/mes-commandes">Mes commandes</a>

            <?php if (($_SESSION['user_role'] ?? '') === 'artist'): ?>
                <a href="/my-shop">Ma boutique</a>
                <a href="/my-services">Mes prestations</a>
                <a href="/my-portfolio">Mon portfolio</a>
                <a href="/commandes-recues">Commandes reçues</a>
            <?php endif; ?>

            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="/admin">Administration</a>
            <?php endif; ?>

            <a href="/logout">Se déconnecter</a>
        <?php else: ?>
            <a href="/login">Se connecter</a>
            <a href="/register">S'inscrire</a>
        <?php endif; ?>
    </nav>
</header>