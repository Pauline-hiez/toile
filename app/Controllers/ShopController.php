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

        $this->renderer->render('artist/shop', [
            'shop' => $shop,
            'errors' => [],
            'success' => null,
            'pageTitle' => 'Ma boutique — Toile',
            'pageHeading' => 'Ma boutique',
            'pageSubtitle' => "Personnalise la vitrine publique de ta boutique.",
        ], 'layouts/artist');
    }

    public function save(): void
    {
        $existingShop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        // Styles envoyés comme plusieurs cases à cocher du même nom "styles[]".
        $styles = $_POST['styles'] ?? [];

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
            $this->renderer->render('artist/shop', [
                'shop' => $existingShop,
                'errors' => $errors,
                'success' => null,
                'pageTitle' => 'Ma boutique — Toile',
            ], 'layouts/artist');
            return;
        }

        // is_open n'est pas piloté par ce formulaire : une boutique ne
        // s'ouvre qu'après avoir choisi un abonnement (voir
        // SubscriptionController), puis se pilote via /my-shop/toggle.
        $data = [
            'name' => $name,
            'bio' => $bio,
            'styles' => json_encode($styles),
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

        $this->renderer->render('artist/shop', [
            'shop' => $shop,
            'errors' => [],
            'success' => 'Boutique enregistrée avec succès.',
            'pageTitle' => 'Ma boutique — Toile',
            'pageHeading' => 'Ma boutique',
            'pageSubtitle' => "Personnalise la vitrine publique de ta boutique.",
        ], 'layouts/artist');
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
        $isFavorite = isset($_SESSION['user_id'])
            ? $this->favoriteModel->isFavorite($_SESSION['user_id'], $shop['id'])
            : false;
        $artist = $this->userModel->findById($shop['user_id']);

        $this->renderer->render('shop/show', [
            'shop' => $shop,
            'artist' => $artist,
            'services' => $services,
            'portfolioImages' => $portfolioImages,
            'ratingStats' => $ratingStats,
            'isFavorite' => $isFavorite,
            'previewMode' => $previewMode,
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

    public function search(): void
    {
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'style' => trim($_GET['style'] ?? ''),
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'sort' => $_GET['sort'] ?? 'rating',
        ];

        $shops = $this->shopModel->search($filters);

        $this->renderer->render('shop/search', [
            'shops' => $shops,
            'filters' => $filters,
            'availableStyles' => Shop::STYLES,
        ]);
    }
}
