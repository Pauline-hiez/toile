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

        $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        $newOrders = array_filter($orders, fn($o) => $o['created_at'] >= $weekAgo);

        $clientIds = array_unique(array_column($orders, 'client_id'));
        $newClientIds = array_unique(array_column($newOrders, 'client_id'));

        $stats = [
            'order_count' => count($orders),
            'new_orders' => count($newOrders),
            'favorite_count' => $shop !== null ? $this->favoriteModel->countByShopId($shop['id']) : 0,
            'new_favorites' => $shop !== null ? $this->favoriteModel->countNewThisWeek($shop['id']) : 0,
            'client_count' => count($clientIds),
            'new_clients' => count($newClientIds),
        ];

        $history = array_slice($this->notificationModel->findByUserId($_SESSION['user_id']), 0, 6);

        $this->renderer->render('artist/dashboard', [
            'shop' => $shop,
            'stats' => $stats,
            'history' => $history,
            'recentOrders' => array_slice($orders, 0, 5),
            'activityChart' => $this->buildActivityChart($orders),
            'pageTitle' => 'Mon espace — Toile',
            'pageHeading' => 'Dashboard',
            'pageSubtitle' => "Bienvenue sur ton espace artiste.\nVoici un aperçu de ton activité.",
        ], 'layouts/artist');
    }

    /**
     * Nombre de commandes de la boutique par jour sur les N derniers jours,
     * même format que AdminController::getActivityChartData() (consommé
     * par le même script public/assets/js/admin-chart.js).
     */
    private function buildActivityChart(array $orders, int $days = 14): array
    {
        $countsByDay = [];
        foreach ($orders as $order) {
            $day = substr($order['created_at'], 0, 10);
            $countsByDay[$day] = ($countsByDay[$day] ?? 0) + 1;
        }

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($date));
            $values[] = $countsByDay[$date] ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
