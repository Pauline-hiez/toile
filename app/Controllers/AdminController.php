<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Core\FileUploader;
use App\Models\User;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Review;
use App\Models\ShopSubscription;
use App\Models\SubscriptionPlan;
use App\Models\RaffleEntry;
use App\Models\Report;
use App\Models\Setting;
use App\Models\CategoryRequest;

class AdminController
{
    private Renderer $renderer;
    private User $userModel;
    private Shop $shopModel;
    private Order $orderModel;
    private Review $reviewModel;
    private ShopSubscription $subscriptionModel;
    private SubscriptionPlan $subscriptionPlanModel;
    private RaffleEntry $raffleModel;
    private Report $reportModel;
    private Setting $settingModel;
    private CategoryRequest $categoryRequestModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->userModel = new User();
        $this->shopModel = new Shop();
        $this->orderModel = new Order();
        $this->reviewModel = new Review();
        $this->subscriptionModel = new ShopSubscription();
        $this->subscriptionPlanModel = new SubscriptionPlan();
        $this->raffleModel = new RaffleEntry();
        $this->reportModel = new Report();
        $this->settingModel = new Setting();
        $this->categoryRequestModel = new CategoryRequest();
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
     * Page Statistiques : courbes d'activité étendues (inscriptions,
     * commandes, revenus) sur une période paramétrable, réparties en
     * deux onglets — Activité (site/utilisateurs) et Revenus (finances).
     */
    public function statistics(): void
    {
        $days = (int) ($_GET['days'] ?? 30);
        $days = in_array($days, [14, 30, 90], true) ? $days : 30;

        $ordersRevenueChart = $this->getRevenueChartData($days);
        $commissionsChart = $this->getCommissionsChartData($days);
        $subscriptionsChart = $this->getSubscriptionRevenueChartData($days);
        $raffleChart = $this->getRaffleRevenueChartData($days);

        $this->renderer->render('admin/statistics', [
            'days' => $days,
            'signupsChart' => $this->getSignupsChartData($days),
            'ordersChart' => $this->getActivityChartData($days),
            'ordersRevenueChart' => $ordersRevenueChart,
            'commissionsChart' => $commissionsChart,
            'subscriptionsChart' => $subscriptionsChart,
            'raffleChart' => $raffleChart,
            'totalRevenueChart' => $this->sumSeries([$commissionsChart, $subscriptionsChart, $raffleChart]),
            'pageTitle' => 'Statistiques - Administration',
            'pageHeading' => 'Statistiques',
            'pageSubtitle' => "Suis l'évolution de l'activité de la plateforme dans le temps.",
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
        return $this->getDailySeries(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)",
            $days
        );
    }

    // Nombre d'inscriptions par jour (page Statistiques).
    private function getSignupsChartData(int $days): array
    {
        return $this->getDailySeries(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM users
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)",
            $days
        );
    }

    // Revenus (montant total des commandes capturées) par jour (page Statistiques).
    private function getRevenueChartData(int $days): array
    {
        return $this->getDailySeries(
            "SELECT DATE(created_at) AS day, COALESCE(SUM(total_price), 0) / 100 AS total
             FROM orders
             WHERE status IN ('accepted', 'in_progress', 'delivered', 'completed')
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)",
            $days,
            true
        );
    }

    // Commissions perçues par la plateforme par jour (page Statistiques).
    private function getCommissionsChartData(int $days): array
    {
        return $this->getDailySeries(
            "SELECT DATE(created_at) AS day, COALESCE(SUM(commission_amount), 0) / 100 AS total
             FROM orders
             WHERE status IN ('accepted', 'in_progress', 'delivered', 'completed')
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)",
            $days,
            true
        );
    }

    /**
     * Revenus tirage au sort par jour (montant réellement payé, voir
     * raffle_entry.amount_paid — NULL pour les tickets vendus avant
     * l'ajout de cette colonne, comptés comme 0).
     */
    private function getRaffleRevenueChartData(int $days): array
    {
        return $this->getDailySeries(
            "SELECT DATE(created_at) AS day, COALESCE(SUM(amount_paid), 0) / 100 AS total
             FROM raffle_entry
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)",
            $days,
            true
        );
    }

    /**
     * Revenus abonnements par jour — compte les nouvelles souscriptions
     * ou changements de formule (shop_subscription.created_at), pas les
     * renouvellements récurrents : aucun webhook Stripe n'est branché sur
     * les factures pour l'instant, donc pas de vrai suivi MRR dans le temps.
     */
    private function getSubscriptionRevenueChartData(int $days): array
    {
        return $this->getDailySeries(
            "SELECT DATE(ss.created_at) AS day, COALESCE(SUM(sp.price), 0) / 100 AS total
             FROM shop_subscription ss
             INNER JOIN subscription_plan sp ON sp.id = ss.plan_id
             WHERE ss.created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(ss.created_at)",
            $days,
            true
        );
    }

    /**
     * Additionne plusieurs séries temporelles (même format labels/values,
     * mêmes jours) point par point — utilisé pour "Revenus totaux
     * plateforme" (commissions + abonnements + tirage au sort).
     */
    private function sumSeries(array $series): array
    {
        $labels = $series[0]['labels'] ?? [];
        $values = array_fill(0, count($labels), 0.0);

        foreach ($series as $s) {
            foreach ($s['values'] as $i => $v) {
                $values[$i] += $v;
            }
        }

        return ['labels' => $labels, 'values' => array_map(fn($v) => round($v, 2), $values)];
    }

    /**
     * Série temporelle jour par jour à partir d'une requête SQL fournie
     * (doit retourner les colonnes day/total, avec un paramètre nommé
     * :days) — comble les jours sans donnée à 0, pour que le graphique
     * (voir admin-chart.js) ait toujours une courbe continue. $asFloat
     * pour les montants (revenus/commissions), int par défaut (compteurs).
     */
    private function getDailySeries(string $sql, int $days, bool $asFloat = false): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('days', $days - 1, \PDO::PARAM_INT);
        $stmt->execute();

        $countsByDay = array_column($stmt->fetchAll(), 'total', 'day');

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($date));
            $rawValue = $countsByDay[$date] ?? 0;
            $values[] = $asFloat ? round((float) $rawValue, 2) : (int) $rawValue;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // Liste des demandes artistes en attente
    public function artistRequests(): void
    {
        $requests = $this->userModel->findPendingArtistRequests();

        $this->renderer->render('admin/artist-requests', [
            'requests' => $requests,
            'pageTitle' => 'Demandes artiste - Administration',
            'pageHeading' => 'Demandes artiste',
            'pageSubtitle' => 'Examine les candidatures et décide qui rejoint la plateforme en tant qu\'artiste.',
        ], 'layouts/admin');
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
            '/my-subscription'
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

    // Liste des demandes de nouveau style/type en attente
    public function categoryRequests(): void
    {
        $requests = $this->categoryRequestModel->findPending();

        $this->renderer->render('admin/category-requests', [
            'requests' => $requests,
            'pageTitle' => 'Demandes de catégories - Administration',
            'pageHeading' => 'Demandes de catégories',
            'pageSubtitle' => 'Valide les nouveaux styles et types proposés par les artistes.',
        ], 'layouts/admin');
    }

    public function approveCategoryRequest(int $id): void
    {
        $request = $this->categoryRequestModel->findById($id);
        if ($request === null || $request['status'] !== 'pending') {
            http_response_code(404);
            echo 'Demande introuvable.';
            exit;
        }

        $this->categoryRequestModel->approve($id);

        $shop = $this->shopModel->findById($request['shop_id']);
        if ($shop !== null) {
            $notificationModel = new \App\Models\Notification();
            $notificationModel->notify(
                $shop['user_id'],
                'category_request_approved',
                'Ta proposition "' . $request['name'] . '" a été acceptée !',
                '/my-shop'
            );
        }

        header('Location: /admin/category-requests');
        exit;
    }

    public function rejectCategoryRequest(int $id): void
    {
        $request = $this->categoryRequestModel->findById($id);
        if ($request === null || $request['status'] !== 'pending') {
            http_response_code(404);
            echo 'Demande introuvable.';
            exit;
        }

        $this->categoryRequestModel->reject($id);

        $shop = $this->shopModel->findById($request['shop_id']);
        if ($shop !== null) {
            $notificationModel = new \App\Models\Notification();
            $notificationModel->notify(
                $shop['user_id'],
                'category_request_rejected',
                'Ta proposition "' . $request['name'] . '" n\'a pas été retenue.',
                '/my-shop'
            );
        }

        header('Location: /admin/category-requests');
        exit;
    }

    public function shops(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'registered' => $_GET['registered'] ?? '',
            'page' => $page,
            'per_page' => $perPage,
        ];

        $result = $this->shopModel->adminSearch($filters);

        $this->renderer->render('admin/shops', [
            'shops' => $result['shops'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'filters' => $filters,
            'stats' => $this->getShopStats(),
            'pageTitle' => 'Boutiques - Administration',
            'pageHeading' => 'Boutiques',
            'pageSubtitle' => "Consultez et gérez l'ensemble des boutiques de la plateforme.",
        ], 'layouts/admin');
    }

    /**
     * Indicateurs clés pour les cartes de la page Artistes.
     */
    private function getShopStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $total = (int) $pdo->query('SELECT COUNT(*) FROM shop')->fetchColumn();

        $pending = (int) $pdo->query(
            "SELECT COUNT(*) FROM shop
             INNER JOIN users u ON u.id = shop.user_id
             WHERE shop.plan_selected = 0 AND u.is_banned = 0"
        )->fetchColumn();

        $active = (int) $pdo->query(
            "SELECT COUNT(*) FROM shop
             INNER JOIN users u ON u.id = shop.user_id
             WHERE shop.is_open = 1 AND u.is_banned = 0"
        )->fetchColumn();

        $suspended = (int) $pdo->query(
            "SELECT COUNT(*) FROM shop
             INNER JOIN users u ON u.id = shop.user_id
             WHERE u.is_banned = 1"
        )->fetchColumn();

        $newThisWeek = (int) $pdo->query(
            'SELECT COUNT(*) FROM shop WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        return [
            'total' => $total,
            'pending' => $pending,
            'active' => $active,
            'suspended' => $suspended,
            'new_this_week' => $newThisWeek,
        ];
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

    public function toggleShopOpen(int $id): void
    {
        $shop = $this->shopModel->findById($id);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        $this->shopModel->toggleOpen($id);

        header('Location: /admin/shops');
        exit;
    }

    public function orders(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'registered' => $_GET['registered'] ?? '',
            'archived' => $_GET['archived'] ?? '',
            'page' => $page,
            'per_page' => $perPage,
        ];

        $result = $this->orderModel->adminSearch($filters);

        $this->renderer->render('admin/orders', [
            'orders' => $result['orders'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'filters' => $filters,
            'stats' => $this->getOrderStats(),
            'pageTitle' => 'Commandes - Administration',
            'pageHeading' => 'Commandes',
            'pageSubtitle' => "Consultez et gérez l'ensemble des commandes de la plateforme.",
        ], 'layouts/admin');
    }

    /**
     * Indicateurs clés pour les cartes de la page Commandes.
     */
    private function getOrderStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $total = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();

        $pending = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders WHERE status IN ('quote_requested', 'pending')"
        )->fetchColumn();

        $inProgress = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders WHERE status IN ('accepted', 'in_progress', 'delivered')"
        )->fetchColumn();

        $completed = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders WHERE status = 'completed'"
        )->fetchColumn();

        $cancelled = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders WHERE status IN ('cancelled', 'rejected')"
        )->fetchColumn();

        $newThisWeek = (int) $pdo->query(
            'SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        $totalRevenue = (int) $pdo->query(
            'SELECT COALESCE(SUM(total_price), 0) FROM orders'
        )->fetchColumn();

        $newRevenueThisWeek = (int) $pdo->query(
            "SELECT COALESCE(SUM(total_price), 0) FROM orders
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        $archived = (int) $pdo->query(
            'SELECT COUNT(*) FROM orders WHERE is_archived = 1'
        )->fetchColumn();

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'new_this_week' => $newThisWeek,
            'total_revenue' => $totalRevenue,
            'new_revenue_this_week' => $newRevenueThisWeek,
            'archived' => $archived,
        ];
    }

    public function toggleOrderArchive(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        $this->orderModel->toggleArchived($id);

        header('Location: /admin/orders');
        exit;
    }

    public function bulkArchiveOrders(): void
    {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        $this->orderModel->setArchivedMany($ids, true);

        header('Location: /admin/orders');
        exit;
    }

    public function subscriptions(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'registered' => $_GET['registered'] ?? '',
            'plan' => $_GET['plan'] ?? '',
            'page' => $page,
            'per_page' => $perPage,
        ];

        $result = $this->subscriptionModel->adminSearch($filters);

        $this->renderer->render('admin/subscriptions', [
            'subscriptions' => $result['subscriptions'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'filters' => $filters,
            'stats' => $this->getSubscriptionStats(),
            'pageTitle' => 'Abonnements - Administration',
            'pageHeading' => 'Abonnements',
            'pageSubtitle' => "Consultez et gérez l'ensemble des abonnements de la plateforme.",
        ], 'layouts/admin');
    }

    /**
     * Indicateurs clés pour les cartes de la page Abonnements : répartition
     * par formule (une boutique n'a qu'une seule ligne shop_subscription,
     * donc ces trois compteurs correspondent aux trois paliers réels).
     */
    private function getSubscriptionStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $commission = (int) $pdo->query(
            "SELECT COUNT(*) FROM shop_subscription ss
             INNER JOIN subscription_plan sp ON sp.id = ss.plan_id AND sp.name = 'Commission'"
        )->fetchColumn();

        $essentiel = (int) $pdo->query(
            "SELECT COUNT(*) FROM shop_subscription ss
             INNER JOIN subscription_plan sp ON sp.id = ss.plan_id AND sp.name = 'Essentiel'"
        )->fetchColumn();

        $pro = (int) $pdo->query(
            "SELECT COUNT(*) FROM shop_subscription ss
             INNER JOIN subscription_plan sp ON sp.id = ss.plan_id AND sp.name = 'Pro'"
        )->fetchColumn();

        // Boutiques créées mais qui n'ont pas encore choisi de formule —
        // donc pas encore ouvertes, et sans ligne shop_subscription.
        $pendingChoice = (int) $pdo->query(
            'SELECT COUNT(*) FROM shop WHERE plan_selected = 0'
        )->fetchColumn();

        $mrr = (int) $pdo->query(
            "SELECT COALESCE(SUM(sp.price), 0)
             FROM shop_subscription ss
             INNER JOIN subscription_plan sp ON sp.id = ss.plan_id AND sp.name != 'Commission'
             WHERE ss.status = 'active' AND ss.current_period_end > NOW()"
        )->fetchColumn();

        return [
            'commission' => $commission,
            'essentiel' => $essentiel,
            'pro' => $pro,
            'pending_choice' => $pendingChoice,
            'mrr' => $mrr,
        ];
    }

    public function raffle(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => $page,
            'per_page' => $perPage,
        ];

        $result = $this->raffleModel->adminSearch($filters);

        $this->renderer->render('admin/raffle', [
            'entries' => $result['entries'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'filters' => $filters,
            'stats' => $this->getRaffleStats(),
            'boutiquesWinners' => $this->raffleModel->findSelectedBoutiquesThisMonth(),
            'nextBoutiquesDraw' => date('Y-m-d 00:00:00', strtotime('first day of next month')),
            'nextHomepageDraw' => date('Y-m-d 00:00:00', strtotime('next monday')),
            'pageTitle' => 'Tirage au sort - Administration',
            'pageHeading' => 'Tirage au sort',
            'pageSubtitle' => "Consultez et gérez les tirages au sort de la plateforme.",
        ], 'layouts/admin');
    }

    /**
     * Indicateurs clés pour les cartes de la page Tirage au sort.
     */
    private function getRaffleStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $currentMonth = date('Y-m');
        $currentMonday = date('Y-m-d', strtotime('monday this week'));
        $rafflePrice = (int) $this->settingModel->get('raffle_price', $_ENV['RAFFLE_PRICE'] ?? '500');
        $homepagePrice = (int) $this->settingModel->get('raffle_homepage_price', $_ENV['RAFFLE_HOMEPAGE_PRICE'] ?? '700');

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM raffle_entry WHERE type = 'boutiques' AND period = :period AND status = 'entered'"
        );
        $stmt->execute(['period' => $currentMonth]);
        $entriesBoutiques = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM raffle_entry WHERE type = 'homepage' AND period = :period AND status = 'entered'"
        );
        $stmt->execute(['period' => $currentMonday]);
        $entriesHomepage = (int) $stmt->fetchColumn();

        // Même logique que RaffleEntry::findSelectedBoutiquesThisMonth(),
        // pour rester cohérent avec le panneau "Gagnants" de la vue.
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM raffle_entry WHERE type = 'boutiques' AND status = 'selected' AND period = :period"
        );
        $stmt->execute(['period' => $currentMonth]);
        $winnersBoutiques = (int) $stmt->fetchColumn();

        $winnersHomepage = (int) $pdo->query(
            "SELECT COUNT(*) FROM raffle_entry WHERE type = 'homepage' AND status = 'selected'
             AND featured_until >= CURDATE()"
        )->fetchColumn();

        return [
            'entries_boutiques' => $entriesBoutiques,
            'entries_homepage' => $entriesHomepage,
            'revenue_boutiques' => $entriesBoutiques * $rafflePrice,
            'revenue_homepage' => $entriesHomepage * $homepagePrice,
            'winners_boutiques' => $winnersBoutiques,
            'winners_homepage' => $winnersHomepage,
        ];
    }

    public function reports(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => $page,
            'per_page' => $perPage,
        ];

        $result = $this->reportModel->adminSearch($filters);

        $this->renderer->render('admin/reports', [
            'reports' => $result['reports'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'filters' => $filters,
            'stats' => $this->getReportStats(),
            'pageTitle' => 'Signalements - Administration',
            'pageHeading' => 'Signalements',
            'pageSubtitle' => "Consultez et traitez les signalements des utilisateurs.",
        ], 'layouts/admin');
    }

    /**
     * Indicateurs clés pour les cartes de la page Signalements.
     */
    private function getReportStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $total = (int) $pdo->query('SELECT COUNT(*) FROM report')->fetchColumn();

        $pending = (int) $pdo->query(
            "SELECT COUNT(*) FROM report WHERE status = 'pending'"
        )->fetchColumn();

        $resolved = (int) $pdo->query(
            "SELECT COUNT(*) FROM report WHERE status = 'resolved'"
        )->fetchColumn();

        $dismissed = (int) $pdo->query(
            "SELECT COUNT(*) FROM report WHERE status = 'dismissed'"
        )->fetchColumn();

        $newThisWeek = (int) $pdo->query(
            'SELECT COUNT(*) FROM report WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        return [
            'total' => $total,
            'pending' => $pending,
            'resolved' => $resolved,
            'dismissed' => $dismissed,
            'new_this_week' => $newThisWeek,
        ];
    }

    public function resolveReport(int $id): void
    {
        $report = $this->reportModel->findById($id);

        if ($report === null) {
            http_response_code(404);
            echo 'Signalement introuvable.';
            exit;
        }

        $this->reportModel->markResolved($id, (int) $_SESSION['user_id']);

        header('Location: /admin/reports');
        exit;
    }

    public function dismissReport(int $id): void
    {
        $report = $this->reportModel->findById($id);

        if ($report === null) {
            http_response_code(404);
            echo 'Signalement introuvable.';
            exit;
        }

        $this->reportModel->markDismissed($id, (int) $_SESSION['user_id']);

        header('Location: /admin/reports');
        exit;
    }

    public function deleteReport(int $id): void
    {
        $report = $this->reportModel->findById($id);

        if ($report === null) {
            http_response_code(404);
            echo 'Signalement introuvable.';
            exit;
        }

        $this->reportModel->delete($id);

        header('Location: /admin/reports');
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
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'registered' => $_GET['registered'] ?? '',
            'page' => $page,
            'per_page' => $perPage,
        ];

        $result = $this->userModel->search($filters);
        $users = $result['users'];

        // Boutiques des artistes affichés, pour le lien "voir".
        $artistIds = array_column(
            array_filter($users, fn($u) => $u['role'] === 'artist'),
            'id'
        );
        $shopSlugsByUserId = $artistIds !== [] ? $this->shopModel->findSlugsByUserIds($artistIds) : [];

        $this->renderer->render('admin/users', [
            'users' => $users,
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'filters' => $filters,
            'shopSlugsByUserId' => $shopSlugsByUserId,
            'stats' => $this->getUserStats(),
            'pageTitle' => 'Utilisateurs - Administration',
            'pageHeading' => 'Utilisateurs',
            'pageSubtitle' => "Consultez et gérez l'ensemble des utilisateurs de la plateforme.",
        ], 'layouts/admin');
    }

    /**
     * Construit une liste de numéros de page avec des '...' pour les
     * séquences non affichées (ex: 1 2 3 ... 12).
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

    /**
     * Indicateurs clés pour les cartes de la page Utilisateurs.
     */
    private function getUserStats(): array
    {
        $pdo = \App\Core\Database::getInstance()->getConnection();

        $total = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $active = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_banned = 0')->fetchColumn();
        $suspended = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_banned = 1')->fetchColumn();
        $artists = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'artist'")->fetchColumn();

        $newThisWeek = (int) $pdo->query(
            'SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        $newPrevWeek = (int) $pdo->query(
            'SELECT COUNT(*) FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();

        $newArtistsThisWeek = (int) $pdo->query(
            "SELECT COUNT(*) FROM users
             WHERE role = 'artist' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        return [
            'total' => $total,
            'active' => $active,
            'suspended' => $suspended,
            'artists' => $artists,
            'new_this_week' => $newThisWeek,
            'new_vs_prev_week' => $newThisWeek - $newPrevWeek,
            'new_artists_this_week' => $newArtistsThisWeek,
        ];
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

    public function bulkBanUsers(): void
    {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        $this->userModel->banMany($ids, (int) $_SESSION['user_id']);

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

    public function settings(): void
    {
        $this->renderer->render('admin/settings', array_merge([
            'settings' => $this->settingModel->all(),
            'plans' => $this->subscriptionPlanModel->findAll(),
            'section' => $_GET['section'] ?? '',
            'success' => isset($_GET['success']),
            'pageTitle' => 'Paramètres - Administration',
            'pageHeading' => 'Paramètres',
            'pageSubtitle' => "Configure les informations générales, les réseaux sociaux et les réglages de la plateforme.",
        ], $this->homepageStylesExtras()), 'layouts/admin');
    }

    /**
     * Styles candidats (5 fixes + approuvés) et sélection actuelle, pour
     * l'onglet "Styles à la une" de /admin/settings.
     */
    private function homepageStylesExtras(): array
    {
        $candidates = [];
        foreach (Shop::STYLES as $style) {
            $candidates[] = ['name' => $style, 'image' => isset(Shop::STYLE_TILE_IMAGES[$style]) ? '/assets/images/decor/' . Shop::STYLE_TILE_IMAGES[$style] : null];
        }
        foreach ($this->categoryRequestModel->findApprovedStyleRows() as $request) {
            $candidates[] = ['name' => $request['name'], 'image' => '/uploads/category-requests/' . $request['image'], 'requestId' => $request['id']];
        }

        $selected = json_decode($this->settingModel->get('homepage_styles', '') ?: '[]', true) ?: array_column($candidates, 'name');
        $selected = array_slice($selected, 0, 5);

        return [
            'homepageStyleCandidates' => $candidates,
            'homepageStyleSelected' => $selected,
        ];
    }

    public function updateHomepageStylesSettings(): void
    {
        $selected = array_slice(array_values($_POST['homepage_styles'] ?? []), 0, 5);
        $this->settingModel->set('homepage_styles', json_encode($selected));

        header('Location: /admin/settings?section=homepage_styles&success=1');
        exit;
    }

    // Renomme un style validé et/ou remplace son visuel (POST /admin/category-requests/[id]/edit)
    public function updateApprovedStyle(int $id): void
    {
        $request = $this->categoryRequestModel->findById($id);
        if ($request === null || $request['category_type'] !== 'style' || $request['status'] !== 'approved') {
            http_response_code(404);
            echo 'Style introuvable.';
            exit;
        }

        $newName = trim($_POST['name'] ?? '');
        if (mb_strlen($newName) < 2) {
            header('Location: /admin/settings?section=homepage_styles');
            exit;
        }

        $imageFilename = $request['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = FileUploader::upload(
                $_FILES['image'],
                __DIR__ . '/../../public/uploads/category-requests'
            );
            if ($uploadResult['filename'] !== null) {
                if ($request['image'] !== null) {
                    @unlink(__DIR__ . '/../../public/uploads/category-requests/' . $request['image']);
                }
                $imageFilename = $uploadResult['filename'];
            }
        }

        $this->categoryRequestModel->update($id, ['name' => $newName, 'image' => $imageFilename]);

        // Garde la sélection page d'accueil cohérente si ce style y figurait
        // sous son ancien nom.
        $selected = json_decode($this->settingModel->get('homepage_styles', '') ?: '[]', true) ?: [];
        $index = array_search($request['name'], $selected, true);
        if ($index !== false) {
            $selected[$index] = $newName;
            $this->settingModel->set('homepage_styles', json_encode($selected));
        }

        header('Location: /admin/settings?section=homepage_styles&success=1');
        exit;
    }

    // Supprime définitivement un style validé (POST /admin/category-requests/[id]/delete)
    public function deleteApprovedStyle(int $id): void
    {
        $request = $this->categoryRequestModel->findById($id);
        if ($request === null || $request['category_type'] !== 'style' || $request['status'] !== 'approved') {
            http_response_code(404);
            echo 'Style introuvable.';
            exit;
        }

        if ($request['image'] !== null) {
            @unlink(__DIR__ . '/../../public/uploads/category-requests/' . $request['image']);
        }
        $this->categoryRequestModel->delete($id);

        $selected = json_decode($this->settingModel->get('homepage_styles', '') ?: '[]', true) ?: [];
        $selected = array_values(array_diff($selected, [$request['name']]));
        $this->settingModel->set('homepage_styles', json_encode($selected));

        header('Location: /admin/settings?section=homepage_styles&success=1');
        exit;
    }

    public function updateGeneralSettings(): void
    {
        $this->settingModel->setMany([
            'site_name' => trim($_POST['site_name'] ?? '') ?: 'Toile',
            'site_description' => trim($_POST['site_description'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
        ]);

        if (!empty($_FILES['site_logo']['name'])) {
            $result = FileUploader::upload(
                $_FILES['site_logo'],
                __DIR__ . '/../../public/uploads/branding',
                ['image/png', 'image/jpeg', 'image/webp'],
                2 * 1024 * 1024
            );
            if ($result['filename'] !== null) {
                $this->settingModel->set('site_logo', $result['filename']);
            }
        }

        if (!empty($_FILES['site_favicon']['name'])) {
            $result = FileUploader::upload(
                $_FILES['site_favicon'],
                __DIR__ . '/../../public/uploads/branding',
                ['image/png', 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon'],
                512 * 1024
            );
            if ($result['filename'] !== null) {
                $this->settingModel->set('site_favicon', $result['filename']);
            }
        }

        header('Location: /admin/settings?section=general&success=1');
        exit;
    }

    public function updateSocialSettings(): void
    {
        $this->settingModel->setMany([
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_facebook' => trim($_POST['social_facebook'] ?? ''),
            'social_pinterest' => trim($_POST['social_pinterest'] ?? ''),
            'social_tiktok' => trim($_POST['social_tiktok'] ?? ''),
        ]);

        header('Location: /admin/settings?section=social&success=1');
        exit;
    }

    public function updateRaffleSettings(): void
    {
        $rafflePrice = (float) str_replace(',', '.', $_POST['raffle_price'] ?? '3');
        $homepagePrice = (float) str_replace(',', '.', $_POST['raffle_homepage_price'] ?? '5');

        $this->settingModel->setMany([
            'raffle_price' => (string) max(0, (int) round($rafflePrice * 100)),
            'raffle_max_winners' => (string) max(1, (int) ($_POST['raffle_max_winners'] ?? 10)),
            'raffle_homepage_price' => (string) max(0, (int) round($homepagePrice * 100)),
            'raffle_homepage_winners' => (string) max(1, (int) ($_POST['raffle_homepage_winners'] ?? 5)),
        ]);

        header('Location: /admin/settings?section=raffle&success=1');
        exit;
    }

    /**
     * Met à jour le prix et les caractéristiques des formules d'abonnement.
     * Le palier gratuit "Commission" n'a pas de prix (pas de facturation
     * Stripe), sa valeur soumise est ignorée.
     *
     * Si le prix d'un palier payant change réellement, un nouveau Price
     * Stripe est créé automatiquement (les Price Stripe sont immuables,
     * impossible de modifier le montant d'un Price existant), et TOUS les
     * abonnés déjà actifs sur ce plan sont basculés vers ce nouveau Price
     * (proration_behavior 'none' : effectif à leur prochain renouvellement,
     * pas de facturation immédiate en plein milieu du cycle en cours) —
     * pas de grandfathering, tout le monde paie le même tarif à terme.
     */
    public function updateSubscriptionPlans(): void
    {
        $plans = $this->subscriptionPlanModel->findAll();

        foreach ($plans as $plan) {
            $input = $_POST['plan'][$plan['id']] ?? null;
            if ($input === null) {
                continue;
            }

            $price = $plan['name'] === 'Commission'
                ? 0
                : max(0, (int) round((float) str_replace(',', '.', $input['price'] ?? '0') * 100));

            $updateData = [
                'price' => $price,
                'commission_rate' => max(0, min(100, (float) str_replace(',', '.', $input['commission_rate'] ?? '0'))),
                'max_services' => max(1, (int) ($input['max_services'] ?? 1)),
                'max_portfolio_images' => max(1, (int) ($input['max_portfolio_images'] ?? 1)),
                'max_options_per_service' => max(0, (int) ($input['max_options_per_service'] ?? 0)),
            ];

            if ($plan['name'] !== 'Commission' && $price !== (int) $plan['price'] && !empty($plan['stripe_price_id'])) {
                $stripe = new \App\Core\StripeService();
                $newPriceId = $stripe->createPriceForSamePlan($plan['stripe_price_id'], $price);
                $updateData['stripe_price_id'] = $newPriceId;

                foreach ($this->subscriptionModel->findActiveByPlanId($plan['id']) as $activeSubscription) {
                    if (empty($activeSubscription['stripe_subscription_id'])) {
                        continue;
                    }

                    try {
                        $stripe->migrateSubscriptionToPrice($activeSubscription['stripe_subscription_id'], $newPriceId);
                        $this->notifySubscriptionPriceChange($activeSubscription, $plan['name'], (int) $plan['price'], $price);
                    } catch (\Exception $e) {
                        // Un abonnement isolé peut échouer (ex: déjà annulé
                        // côté Stripe) — n'interrompt pas la bascule des autres.
                    }
                }
            }

            $this->subscriptionPlanModel->update($plan['id'], $updateData);
        }

        header('Location: /admin/settings?section=subscriptions&success=1');
        exit;
    }

    /**
     * Notifie (in-app + email) l'artiste dont l'abonnement vient d'être
     * basculé vers un nouveau tarif — voir updateSubscriptionPlans().
     */
    private function notifySubscriptionPriceChange(array $subscription, string $planName, int $oldPrice, int $newPrice): void
    {
        $shop = $this->shopModel->findById($subscription['shop_id']);
        if ($shop === null) {
            return;
        }

        $user = $this->userModel->findById($shop['user_id']);
        if ($user === null) {
            return;
        }

        $renewalDate = \App\Core\FrenchDate::format('d MMMM y', $subscription['current_period_end']);

        (new \App\Models\Notification())->notify(
            $user['id'],
            'subscription_price_changed',
            "Le prix de la formule {$planName} passe à " . number_format($newPrice / 100, 2) . ' € — effectif à ton prochain renouvellement le ' . $renewalDate . '.',
            '/my-subscription'
        );

        $html = \App\Core\Mailer::renderTemplate('subscription-price-changed', [
            'username' => $user['username'],
            'planName' => $planName,
            'oldPrice' => $oldPrice,
            'newPrice' => $newPrice,
            'renewalDate' => $renewalDate,
        ]);
        \App\Core\Mailer::send($user['email'], 'Le tarif de ton abonnement évolue', $html, 'subscription_price_changed');
    }

    public function updateMaintenanceSettings(): void
    {
        $this->settingModel->setMany([
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
            'maintenance_message' => trim($_POST['maintenance_message'] ?? ''),
        ]);

        header('Location: /admin/settings?section=maintenance&success=1');
        exit;
    }
}
