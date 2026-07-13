<?php

/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir HomeController::index()).
 *
 * @var array $featuredShops Boutiques mises en avant ("Nos artistes du jour").
 * @var array<int, true> $favoriteShopIds Favoris de l'utilisateur, indexés par shop_id.
 * @var int $unreadCount Nombre de notifications non lues.
 */
$pageTitle = 'Accueil — Toile';
?>

<section class="home-hero">
    <div class="home-hero__text">
        <h1>Donnez vie à<br>vos idées.</h1>
        <p class="home-hero__tagline">L'art n'attend que vous.</p>
        <p class="home-hero__desc">
            Toile est un marketplace de comissions artistiques
            qui met en relation des artistes talentieux
            et des passionnés en quête de créations uniques.
        </p>

        <form method="GET" action="/boutiques" class="home-hero__search">
            <input type="text" name="q" placeholder="Que souhaitez-vous créer ? Ex: Illustration, portrait, paysage...">
            <button type="submit" aria-label="Rechercher">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>
    </div>

    <div class="home-hero__image">
        <img src="/assets/images/decor/hero.png" alt="Artiste illustrant une commande">
    </div>
</section>

<section class="home-quicklinks">
    <h2>Explorez l'univers de la création</h2>

    <div class="home-quicklinks__grid">
        <a href="/boutiques" class="home-quicklink">
            <span class="home-quicklink__icon"><img src="/assets/images/icones/search.png" alt=""></span>
            <span class="home-quicklink__label">Rechercher</span>
        </a>

        <a href="/boutiques?sort=rating" class="home-quicklink">
            <span class="home-quicklink__icon"><img src="/assets/images/icones/voir.png" alt=""></span>
            <span class="home-quicklink__label">Découvrir</span>
        </a>

        <a href="/mes-commandes" class="home-quicklink">
            <span class="home-quicklink__icon"><img src="/assets/images/icones/messages.png" alt=""></span>
            <span class="home-quicklink__label">Messages</span>
        </a>

        <a href="/mes-commandes" class="home-quicklink">
            <span class="home-quicklink__icon"><img src="/assets/images/icones/commandes.png" alt=""></span>
            <span class="home-quicklink__label">Commandes</span>
        </a>

        <a href="/notifications" class="home-quicklink">
            <span class="home-quicklink__icon">
                <img src="/assets/images/icones/notifications.png" alt="">
                <?php if (!empty($unreadCount)): ?>
                    <span class="home-quicklink__badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </span>
            <span class="home-quicklink__label">Notifications</span>
        </a>

        <a href="/mes-favoris" class="home-quicklink">
            <span class="home-quicklink__icon"><img src="/assets/images/icones/favoris.png" alt=""></span>
            <span class="home-quicklink__label">Favoris</span>
        </a>
    </div>
</section>

<?php if (!empty($featuredShops)): ?>
    <section class="home-artists">
        <h2>Nos artistes de la semaine</h2>

        <div class="home-artists__grid">
            <?php foreach ($featuredShops as $shop): ?>
                <?php
                $styles = $shop['styles'] ? json_decode($shop['styles'], true) : [];
                $coverImage = $shop['cover_image']
                    ? '/uploads/portfolio/' . htmlspecialchars($shop['cover_image'])
                    : ($shop['banner'] ? '/uploads/banners/' . htmlspecialchars($shop['banner']) : null);
                $isFavorite = isset($favoriteShopIds[$shop['id']]);
                ?>
                <article class="home-artist-card">
                    <div class="home-artist-card__cover">
                        <?php if ($coverImage): ?>
                            <img src="<?= $coverImage ?>" alt="">
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="/favoris/toggle/<?= $shop['id'] ?>" class="home-artist-card__fav">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
                                <input type="hidden" name="redirect" value="/">
                                <button type="submit" class="<?= $isFavorite ? 'is-active' : '' ?>" aria-label="<?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                                    <svg viewBox="0 0 24 24" fill="<?= $isFavorite ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
                                    </svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="home-artist-card__body">
                        <a href="/boutiques/<?= htmlspecialchars($shop['slug']) ?>" class="home-artist-card__name"><?= htmlspecialchars($shop['name']) ?></a>

                        <?php if (!empty($styles)): ?>
                            <div class="home-artist-card__tags">
                                <?php foreach (array_slice($styles, 0, 3) as $style): ?>
                                    <span class="badge badge--neutral"><?= htmlspecialchars(ucfirst($style)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="home-artist-card__rating">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.5l2.9 6.3 6.9.7-5.2 4.7 1.5 6.8L12 17.6 5.9 21l1.5-6.8-5.2-4.7 6.9-.7L12 2.5Z"></path>
                            </svg>
                            <?php if ($shop['avg_rating'] !== null): ?>
                                <?= number_format((float) $shop['avg_rating'], 1) ?> (<?= (int) $shop['review_count'] ?>)
                            <?php else: ?>
                                Pas encore d'avis
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="home-steps">
    <h2>Comment ça marche ?</h2>

    <div class="home-steps__grid">
        <div class="home-step">
            <span class="home-step__icon"><img src="/assets/images/icones/search.png" alt=""></span>
            <h3>1. Recherchez</h3>
            <p>Découvrez les artistes et trouvez celui qui correspond à votre projet.</p>
        </div>

        <div class="home-step">
            <span class="home-step__icon"><img src="/assets/images/icones/messages.png" alt=""></span>
            <h3>2. Echangez</h3>
            <p>Discutez de votre idée avec les artistes et trouvez la prestation idéale.</p>
        </div>

        <div class="home-step">
            <span class="home-step__icon"><img src="/assets/images/icones/create.png" alt=""></span>
            <h3>3. Créez</h3>
            <p>L'artiste donne vie à votre projet avec passion et créativité.</p>
        </div>

        <div class="home-step">
            <span class="home-step__icon"><img src="/assets/images/icones/commande.png" alt=""></span>
            <h3>4. Recevez</h3>
            <p>Recevez votre commande et partagez votre expérience avec la communauté.</p>
        </div>
    </div>
</section>