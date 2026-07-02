<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Shop;
use App\Models\Review;
use App\Models\Service;
use App\Models\PortfolioImage;
use App\Models\Favorite;

class ShopController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private Review $reviewModel;
    private Service $serviceModel;
    private PortfolioImage $portfolioModel;
    private Favorite $favoriteModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->reviewModel = new Review();
        $this->serviceModel = new Service();
        $this->portfolioModel = new PortfolioImage();
        $this->favoriteModel = new Favorite();
    }

    public function manage(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $this->renderer->render('shop/manage', [
            'shop' => $shop,
            'errors' => [],
            'success' => null,
        ]);
    }

    public function save(): void
    {
        $existingShop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $isOpen = isset($_POST['is_open']) ? 1 : 0;

        // Styles envoyés comme plusieurs cases à cocher du même nom "styles[]".
        $styles = $_POST['styles'] ?? [];

        $errors = [];

        if (mb_strlen($name) < 3) {
            $errors['name'] = 'Le nom de la boutique doit faire au moins 3 caractères.';
        }

        if (!empty($errors)) {
            $this->renderer->render('shop/manage', [
                'shop' => $existingShop,
                'errors' => $errors,
                'success' => null,
            ]);
            return;
        }

        $data = [
            'name' => $name,
            'bio' => $bio,
            'is_open' => $isOpen,
            'styles' => json_encode($styles),
        ];

        if ($existingShop === null) {
            // Création : on génère un nouveau slug.
            $data['slug'] = $this->shopModel->generateUniqueSlug($name);
            $data['user_id'] = $_SESSION['user_id'];

            $this->shopModel->create($data);
        } else {
            if ($name !== $existingShop['name']) {
                $data['slug'] = $this->shopModel->generateUniqueSlug($name, $existingShop['id']);
            }

            $this->shopModel->update($existingShop['id'], $data);
        }

        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $this->renderer->render('shop/manage', [
            'shop' => $shop,
            'errors' => [],
            'success' => 'Boutique enregistrée avec succès.',
        ]);
    }

    public function show(string $slug): void
    {
        $shop = $this->shopModel->findBySlug($slug);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        $services = $this->serviceModel->findActiveByShopId($shop['id']);
        $portfolioImages = $this->portfolioModel->findByShopId($shop['id']);
        $ratingStats = $this->reviewModel->getShopRatingStats($shop['id']);
        $isFavorite = isset($_SESSION['user_id'])
            ? $this->favoriteModel->isFavorite($_SESSION['user_id'], $shop['id'])
            : false;

        $this->renderer->render('shop/show', [
            'shop' => $shop,
            'services' => $services,
            'portfolioImages' => $portfolioImages,
            'ratingStats' => $ratingStats,
            'isFavorite' => $isFavorite,
            'pageTitle' => htmlspecialchars($shop['name']) . ' — Toile',
        ]);
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
            'availableStyles' => ['anime', 'réaliste', 'pixel art', 'digital art', 'bande déssinée'],
        ]);
    }
}
