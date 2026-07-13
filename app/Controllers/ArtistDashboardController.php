<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Shop;

class ArtistDashboardController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private Order $orderModel;
    private Favorite $favoriteModel;
    private Notification $notificationModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->orderModel = new Order();
        $this->favoriteModel = new Favorite();
        $this->notificationModel = new Notification();
    }

    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $orders = $shop !== null ? $this->orderModel->findByShopId($shop['id']) : [];

        $clientIds = array_unique(array_column($orders, 'client_id'));

        $stats = [
            'order_count' => count($orders),
            'favorite_count' => $shop !== null ? $this->favoriteModel->countByShopId($shop['id']) : 0,
            'client_count' => count($clientIds),
        ];

        $history = array_slice($this->notificationModel->findByUserId($_SESSION['user_id']), 0, 6);

        $this->renderer->render('dashboard/artist', [
            'shop' => $shop,
            'stats' => $stats,
            'history' => $history,
            'pageTitle' => 'Mon espace — Toile',
        ], 'layouts/artist');
    }
}
