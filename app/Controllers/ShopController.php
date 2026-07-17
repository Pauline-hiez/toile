<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Shop;
use App\Models\Review;
use App\Models\Service;
use App\Models\PortfolioImage;
use App\Models\Favorite;
use App\Models\User;

class ShopController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private Review $reviewModel;
    private Service $serviceModel;
    private PortfolioImage $portfolioModel;
    private Favorite $favoriteModel;
    private User $userModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->reviewModel = new Review();
        $this->serviceModel = new Service();
        $this->portfolioModel = new PortfolioImage();
        $this->favoriteModel = new Favorite();
        $this->userModel = new User();
    }

    public function manage(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $this->renderer->render('artist/shop', array_merge([
            'shop' => $shop,
            'errors' => [],
            'success' => null,
            'pageTitle' => 'Ma boutique — Toile',
            'pageHeading' => 'Ma boutique',
            'pageSubtitle' => "Personnalise la vitrine publique de ta boutique.",
        ], $this->shopStats($shop)), 'layouts/artist');
    }

    /**
     * Note moyenne et nombre de favoris de la boutique, pour les tuiles
     * stats de /my-shop — null si la boutique n'existe pas encore
     * (premier passage sur la page avant toute sauvegarde).
     */
    private function shopStats(?array $shop): array
    {
        if ($shop === null) {
            return ['ratingStats' => null, 'favoriteCount' => null];
        }

        return [
            'ratingStats' => $this->reviewModel->getShopRatingStats($shop['id']),
            'favoriteCount' => $this->favoriteModel->countByShopId($shop['id']),
        ];
    }

    public function save(): void
    {
        $existingShop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $socialInstagram = trim($_POST['social_instagram'] ?? '');
        $socialFacebook = trim($_POST['social_facebook'] ?? '');
        $socialPinterest = trim($_POST['social_pinterest'] ?? '');
        $socialTiktok = trim($_POST['social_tiktok'] ?? '');

        $errors = [];

        if (mb_strlen($name) < 3) {
            $errors['name'] = 'Le nom de la boutique doit faire au moins 3 caractères.';
        }

        $bannerFilename = $existingShop['banner'] ?? null;

        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = \App\Core\FileUploader::upload(
                $_FILES['banner'],
                __DIR__ . '/../../public/uploads/banners'
            );

            if ($uploadResult['error'] !== null) {
                $errors['banner'] = $uploadResult['error'];
            } else {
                $bannerFilename = $uploadResult['filename'];
            }
        }

        if (!empty($errors)) {
            $this->renderer->render('artist/shop', array_merge([
                'shop' => $existingShop,
                'errors' => $errors,
                'success' => null,
                'pageTitle' => 'Ma boutique — Toile',
                'pageHeading' => 'Ma boutique',
                'pageSubtitle' => "Personnalise la vitrine publique de ta boutique.",
            ], $this->shopStats($existingShop)), 'layouts/artist');
            return;
        }

        // is_open n'est pas piloté par ce formulaire : une boutique ne
        // s'ouvre qu'après avoir choisi un abonnement (voir
        // SubscriptionController), puis se pilote via /my-shop/toggle.
        // Les styles artistiques ne sont pas gérés par ce formulaire — ils
        // seront choisis à la création de la boutique (à venir) et ne
        // doivent pas être écrasés lors d'une simple mise à jour ici.
        $data = [
            'name' => $name,
            'bio' => $bio,
            'social_instagram' => $socialInstagram !== '' ? $socialInstagram : null,
            'social_facebook' => $socialFacebook !== '' ? $socialFacebook : null,
            'social_pinterest' => $socialPinterest !== '' ? $socialPinterest : null,
            'social_tiktok' => $socialTiktok !== '' ? $socialTiktok : null,
            'banner' => $bannerFilename,
        ];

        if ($existingShop === null) {
            // Création : on génère un nouveau slug. La boutique démarre
            // fermée et sans formule choisie.
            $data['slug'] = $this->shopModel->generateUniqueSlug($name);
            $data['user_id'] = $_SESSION['user_id'];
            $data['is_open'] = 0;
            $data['plan_selected'] = 0;

            $this->shopModel->create($data);
        } else {
            if ($name !== $existingShop['name']) {
                $data['slug'] = $this->shopModel->generateUniqueSlug($name, $existingShop['id']);
            }

            $this->shopModel->update($existingShop['id'], $data);
        }

        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $this->renderer->render('artist/shop', array_merge([
            'shop' => $shop,
            'errors' => [],
            'success' => 'Boutique enregistrée avec succès.',
            'pageTitle' => 'Ma boutique — Toile',
            'pageHeading' => 'Ma boutique',
            'pageSubtitle' => "Personnalise la vitrine publique de ta boutique.",
        ], $this->shopStats($shop)), 'layouts/artist');
    }

    public function show(string $slug): void
    {
        $shop = $this->shopModel->findBySlug($slug);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        // Mode aperçu : réservé au propriétaire de la boutique, permet de
        // voir le rendu de ses prestations même inactives (non visibles
        // publiquement) via le bouton "Voir" de /my-services.
        $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] === (int) $shop['user_id'];
        $previewMode = $isOwner && isset($_GET['preview']);

        $services = $previewMode
            ? $this->serviceModel->findByShopId($shop['id'])
            : $this->serviceModel->findActiveByShopId($shop['id']);
        $portfolioImages = $this->portfolioModel->findByShopId($shop['id']);
        $ratingStats = $this->reviewModel->getShopRatingStats($shop['id']);
        $reviews = $this->reviewModel->findByShopId($shop['id']);
        $isFavorite = isset($_SESSION['user_id'])
            ? $this->favoriteModel->isFavorite($_SESSION['user_id'], $shop['id'])
            : false;
        $artist = $this->userModel->findById($shop['user_id']);

        $allowedTabs = ['portfolio', 'prestations', 'avis'];
        $tab = in_array($_GET['tab'] ?? '', $allowedTabs, true) ? $_GET['tab'] : 'portfolio';

        $this->renderer->render('shop/show', [
            'shop' => $shop,
            'artist' => $artist,
            'services' => $services,
            'portfolioImages' => $portfolioImages,
            'ratingStats' => $ratingStats,
            'reviews' => $reviews,
            'isFavorite' => $isFavorite,
            'previewMode' => $previewMode,
            'tab' => $tab,
            'pageTitle' => htmlspecialchars($shop['name']) . ' — Toile',
        ]);
    }

    public function toggleOpen(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        $this->shopModel->toggleOpen($shop['id']);

        header('Location: /my-shop');
        exit;
    }

    public function toggleAcceptsQuotes(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        $this->shopModel->toggleAcceptsQuotes($shop['id']);

        header('Location: /my-shop');
        exit;
    }

    public function search(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25; // 5 lignes de 5

        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'style' => trim($_GET['style'] ?? ''),
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'sort' => $_GET['sort'] ?? 'rating',
            'page' => $page,
            'per_page' => $perPage,
        ];

        $result = $this->shopModel->search($filters);

        $this->renderer->render('shop/search', [
            'shops' => $result['shops'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'filters' => $filters,
            'availableStyles' => Shop::STYLES,
            'pageTitle' => 'Découvrir les artistes — Toile',
        ]);
    }

    /**
     * Construit une liste de numéros de page avec des '...' pour les
     * séquences non affichées (ex: 1 2 3 ... 12) — même logique que
     * AdminController::buildPageNumbers().
     *
     * @return array<int, int|string>
     */
    private function buildPageNumbers(int $currentPage, int $totalPages): array
    {
        $totalPages = max(1, $totalPages);
        $pages = [];

        for ($p = 1; $p <= $totalPages; $p++) {
            if ($p === 1 || $p === $totalPages || abs($p - $currentPage) <= 1) {
                $pages[] = $p;
            } elseif (end($pages) !== '...') {
                $pages[] = '...';
            }
        }

        return $pages;
    }
}
