<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\User;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Review;

class AdminController
{
    private Renderer $renderer;
    private User $userModel;
    private Shop $shopModel;
    private Order $orderModel;
    private Review $reviewModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->userModel = new User();
        $this->shopModel = new Shop();
        $this->orderModel = new Order();
        $this->reviewModel = new Review();
    }

    /**
     * Tableau de bord admin (GET /admin).
     */
    public function dashboard(): void
    {
        $stats = $this->getStats();

        $this->renderer->render('admin/dashboard', [
            'stats' => $stats,
            'pageTitle' => 'Administration — Toile',
        ]);
    }

    /**
     * Calcule les indicateurs clés de la plateforme.
     */
    private function getStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        // Nombre total d'utilisateurs.
        $totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

        // Nombre total de boutiques.
        $totalShops = (int) $pdo->query('SELECT COUNT(*) FROM shop')->fetchColumn();

        // Nombre total de commandes.
        $totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();

        // Commandes en cours (non terminées, non annulées, non refusées).
        $activeOrders = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders
             WHERE status NOT IN ('completed', 'cancelled', 'rejected')"
        )->fetchColumn();

        // Volume total des paiements capturés (commandes acceptées ou plus).
        $totalRevenue = (int) $pdo->query(
            "SELECT COALESCE(SUM(total_price), 0) FROM orders
             WHERE status IN ('accepted', 'in_progress', 'delivered', 'completed')"
        )->fetchColumn();

        // Demandes artiste en attente.
        $pendingArtistRequests = (int) $pdo->query(
            "SELECT COUNT(*) FROM users WHERE artist_request_status = 'pending'"
        )->fetchColumn();

        return [
            'total_users' => $totalUsers,
            'total_shops' => $totalShops,
            'total_orders' => $totalOrders,
            'active_orders' => $activeOrders,
            'total_revenue' => $totalRevenue,
            'pending_artist_requests' => $pendingArtistRequests,
        ];
    }

    // Liste des demandes artistes en attente
    public function artistRequests(): void
    {
        $requests = $this->userModel->findPendingArtistRequests();

        $this->renderer->render('admin/artist-requests', [
            'requests' => $requests,
            'pageTitle' => 'Demande artiste - Administration',
        ]);
    }

    public function approveArtistRequest(int $id): void
    {
        $user = $this->userModel->findById($id);
        if ($user === null || $user['artist_request_status'] !== 'pending') {
            http_response_code(404);
            echo 'Demande introuvable.';
            exit;
        }

        $this->userModel->approveArtistRequest($id);

        $notificationModel = new \App\Models\Notification();
        $notificationModel->notify(
            $id,
            'artist_approved',
            'Félicitations ! Ta demande a été acceptée, tu es maintenant un Artiste !',
            '/my-shop'
        );
        header('Location: /admin/artist-requests');
        exit;
    }

    public function rejectArtistRequest(int $id): void
    {
        $user = $this->userModel->findById($id);

        if ($user === null || $user['artist_request_status'] !== 'pending') {
            http_response_code(404);
            echo 'Demande introuvable.';
            exit;
        }

        $this->userModel->rejectArtistRequest($id);

        $notificationModel = new \App\Models\Notification();
        $notificationModel->notify(
            $id,
            'artist_rejected',
            'Ta demande pour devenir artiste n\'a pas été retenue.',
            '/become-artist'
        );

        header('Location: /admin/artist-requests');
        exit;
    }

    public function shops(): void
    {
        $shops = $this->shopModel->findAllWithOwner();
        $this->renderer->render('admin/shops', [
            'shops' => $shops,
            'pageTitle' => 'Boutiques - Administration',
        ]);
    }

    public function deleteShop(int $id): void
    {
        $shop = $this->shopModel->findById($id);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }
        $this->shopModel->delete($id);

        header('Location: /admin/shops');
        exit;
    }

    public function reviews(): void
    {
        $reviews = $this->reviewModel->findAllWithDetails();

        $this->renderer->render('admin/reviews', [
            'reviews' => $reviews,
            'pageTitle' => 'Avis - Administration',
        ]);
    }

    public function deleteReview(int $id): void
    {
        $review = $this->reviewModel->findById($id);

        if ($review === null) {
            http_response_code(404);
            echo 'Avis introuvable.';
            exit;
        }

        $this->reviewModel->delete($id);

        header('Location: /admin/reviews');
        exit;
    }
}
