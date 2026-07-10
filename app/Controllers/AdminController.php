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
            'recentArtists' => $this->getRecentArtists(),
            'recentOrders' => $this->getRecentOrders(),
            'recentSignups' => $this->getRecentSignups(),
            'activityChart' => $this->getActivityChartData(),
            'pageTitle' => 'Administration — Toile',
            'pageHeading' => 'Dashboard',
            'pageSubtitle' => "Bienvenue sur l'administration de Toile.\nVoici un aperçu global de votre plateforme.",
        ], 'layouts/admin');
    }

    /**
     * Calcule les indicateurs clés de la plateforme.
     */
    private function getStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $capturedStatuses = "'accepted', 'in_progress', 'delivered', 'completed'";

        // Nombre total d'utilisateurs.
        $totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $newUsers = (int) $pdo->query(
            'SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        // Nombre total de boutiques (artistes).
        $totalShops = (int) $pdo->query('SELECT COUNT(*) FROM shop')->fetchColumn();
        $newShops = (int) $pdo->query(
            'SELECT COUNT(*) FROM shop WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        // Nombre total de commandes.
        $totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $newOrders = (int) $pdo->query(
            'SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        // Commandes en cours (non terminées, non annulées, non refusées).
        $activeOrders = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders
             WHERE status NOT IN ('completed', 'cancelled', 'rejected')"
        )->fetchColumn();

        // Volume total des paiements capturés (commandes acceptées ou plus).
        $totalRevenue = (int) $pdo->query(
            "SELECT COALESCE(SUM(total_price), 0) FROM orders
             WHERE status IN ({$capturedStatuses})"
        )->fetchColumn();
        $newRevenue = (int) $pdo->query(
            "SELECT COALESCE(SUM(total_price), 0) FROM orders
             WHERE status IN ({$capturedStatuses})
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        // Commissions perçues par la plateforme.
        $totalCommissions = (int) $pdo->query(
            "SELECT COALESCE(SUM(commission_amount), 0) FROM orders
             WHERE status IN ({$capturedStatuses})"
        )->fetchColumn();
        $newCommissions = (int) $pdo->query(
            "SELECT COALESCE(SUM(commission_amount), 0) FROM orders
             WHERE status IN ({$capturedStatuses})
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        // Demandes artiste en attente.
        $pendingArtistRequests = (int) $pdo->query(
            "SELECT COUNT(*) FROM users WHERE artist_request_status = 'pending'"
        )->fetchColumn();

        return [
            'total_users' => $totalUsers,
            'new_users' => $newUsers,
            'total_shops' => $totalShops,
            'new_shops' => $newShops,
            'total_orders' => $totalOrders,
            'new_orders' => $newOrders,
            'active_orders' => $activeOrders,
            'total_revenue' => $totalRevenue,
            'new_revenue' => $newRevenue,
            'total_commissions' => $totalCommissions,
            'new_commissions' => $newCommissions,
            'pending_artist_requests' => $pendingArtistRequests,
        ];
    }

    /**
     * Derniers artistes (boutiques) inscrits, avec leur nombre de prestations.
     */
    private function getRecentArtists(int $limit = 5): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $stmt = $pdo->prepare(
            "SELECT shop.name, shop.slug, shop.is_open, shop.created_at,
                    u.username, u.avatar,
                    (SELECT COUNT(*) FROM service WHERE service.shop_id = shop.id) AS service_count
             FROM shop
             INNER JOIN users u ON u.id = shop.user_id
             ORDER BY shop.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Dernières commandes passées sur la plateforme.
     */
    private function getRecentOrders(int $limit = 5): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $stmt = $pdo->prepare(
            "SELECT o.id, o.title, o.status, o.created_at,
                    u.username AS client_name,
                    s.name AS shop_name
             FROM orders o
             INNER JOIN users u ON u.id = o.client_id
             INNER JOIN shop s ON s.id = o.shop_id
             ORDER BY o.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Utilisateurs inscrits durant les 7 derniers jours.
     */
    private function getRecentSignups(int $limit = 6): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $stmt = $pdo->prepare(
            "SELECT username, avatar, created_at
             FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Nombre de commandes par jour sur les 14 derniers jours (pour le
     * graphique "Aperçu de l'activité"), avec les jours sans commande
     * comblés à 0.
     */
    private function getActivityChartData(int $days = 14): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $stmt = $pdo->prepare(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)"
        );
        $stmt->bindValue('days', $days - 1, \PDO::PARAM_INT);
        $stmt->execute();

        $countsByDay = array_column($stmt->fetchAll(), 'total', 'day');

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($date));
            $values[] = (int) ($countsByDay[$date] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
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

        $html = \App\Core\Mailer::renderTemplate('artist-approved', [
            'username' => $user['username'],
        ]);
        \App\Core\Mailer::send($user['email'], 'Ta demande artiste a été acceptée !', $html, 'artist_approved');

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

        $html = \App\Core\Mailer::renderTemplate('artist-rejected', [
            'username' => $user['username'],
        ]);
        \App\Core\Mailer::send($user['email'], 'Réponse à ta demande artiste', $html, 'artist_rejected');

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

    public function users(): void
    {
        $users = $this->userModel->findAllUsers();
        $this->renderer->render('admin/users', [
            'users' => $users,
            'pageTitle' => 'Utilisateurs - Administration',
        ]);
    }

    public function banUser(int $id): void
    {
        $user = $this->userModel->findById($id);

        if ($user === null) {
            http_response_code(404);
            echo 'Utilisateur introuvable.';
            exit;
        }

        if ($user['role'] === 'admin') {
            http_response_code(403);
            echo 'Impossible de suspendre un administrateur.';
            exit;
        }

        $this->userModel->ban($id);

        if (!isset($_SESSION['banned_users'])) {
            $_SESSION['banned_users'] = [];
        }

        $_SESSION['banned_users'][$id] = true;

        header('Location: /admin/users');
        exit;
    }

    public function unbanUser(int $id): void
    {
        $user = $this->userModel->findById($id);

        if ($user === null) {
            http_response_code(404);
            echo 'Utilisateur introuvable.';
            exit;
        }

        $this->userModel->unban($id);

        header('Location: /admin/users');
        exit;
    }

    public function changeRole(int $id): void
    {
        $user =  $this->userModel->findById($id);

        if ($user === null) {
            http_response_code(404);
            echo 'Utilisateur introuvable.';
            exit;
        }

        $role = $_POST['role'] ?? '';
        $allowedRoles = ['user', 'artist', 'admin'];

        if (!in_array($role, $allowedRoles, true)) {
            http_response_code(400);
            echo 'Rôle invalide.';
            exit;
        }

        if ($id === $_SESSION['user_id'] && $role !== 'admin') {
            http_response_code(403);
            echo 'Vous ne pouvez pas modifier votre propre rôle.';
            exit;
        }

        $this->userModel->changeRole($id, $role);

        header('Location: /admin/users');
        exit;
    }
}
