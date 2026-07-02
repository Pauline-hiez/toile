<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Favorite;
use App\Models\Shop;

class FavoriteController
{
    private Renderer $renderer;
    private Favorite $favoriteModel;
    private Shop $shopModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->favoriteModel = new Favorite();
        $this->shopModel = new Shop();
    }

    // Ajoute ou supprime une boutique aux favoris
    public function toggle(int $shopId): void
    {
        $shop = $this->shopModel->findById($shopId);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        $userId = $_SESSION['user_id'];

        if ($this->favoriteModel->isFavorite($userId, $shopId)) {
            $this->favoriteModel->remove($userId, $shopId);
        } else {
            $this->favoriteModel->add($userId, $shopId);
        }

        // Redirige vers la page boutique après toggle
        header('Location: /boutiques/' . $shop['slug']);
        exit;
    }

    // Liste des boutiques en favori
    public function index(): void
    {
        $shops = $this->favoriteModel->findShopsByUserID($_SESSION['user_id']);
        $this->renderer->render('favorite/index', [
            'shops' => $shops,
            'pageTitle' => 'Mes favoris - Toile',
        ]);
    }
}
