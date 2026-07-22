<?php

namespace App\Controllers;

use App\Core\ChartHelper;
use App\Core\Paginator;
use App\Core\Renderer;
use App\Models\Order;
use App\Models\RaffleEntry;
use App\Models\Shop;
use App\Models\ShopSubscription;
use App\Models\Review;
use App\Models\Service;
use App\Models\PortfolioImage;
use App\Models\Favorite;
use App\Models\User;
use App\Models\CategoryRequest;

class ShopController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private ShopSubscription $subscriptionModel;
    private Review $reviewModel;
    private Service $serviceModel;
    private PortfolioImage $portfolioModel;
    private Favorite $favoriteModel;
    private User $userModel;
    private CategoryRequest $categoryRequestModel;
    private Order $orderModel;
    private RaffleEntry $raffleModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->subscriptionModel = new ShopSubscription();
        $this->reviewModel = new Review();
        $this->serviceModel = new Service();
        $this->portfolioModel = new PortfolioImage();
        $this->favoriteModel = new Favorite();
        $this->userModel = new User();
        $this->categoryRequestModel = new CategoryRequest();
        $this->orderModel = new Order();
        $this->raffleModel = new RaffleEntry();
    }

    public function manage(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        // Le choix d'abonnement précède désormais la création de la
        // boutique (voir SubscriptionController) — un artiste qui arrive
        // ici sans boutique ni choix en attente doit d'abord passer par
        // /my-subscription.
        if ($shop === null && !isset($_SESSION['pending_shop_subscription'])) {
            header('Location: /my-subscription');
            exit;
        }

        $tab = in_array($_GET['tab'] ?? '', ['infos', 'stats', 'raffle'], true) ? $_GET['tab'] : 'infos';
        $days = (int) ($_GET['days'] ?? 30);
        $days = in_array($days, [14, 30, 90], true) ? $days : 30;

        $this->renderer->render('artist/shop', array_merge([
            'shop' => $shop,
            'errors' => [],
            'success' => null,
            'tab' => $tab,
            'days' => $days,
            'pageTitle' => 'Ma boutique — Toile',
            'pageHeading' => 'Ma boutique',
            'pageSubtitle' => "Personnalise la vitrine publique de ta boutique.",
        ], $this->shopStats($shop), $this->shopFormExtras($shop), $this->shopStatsCharts($shop, $days), $this->shopRaffleHistory($shop)), 'layouts/artist');
    }

    /**
     * Historique paginé des tickets de tirage au sort de la boutique
     * (onglet "Tirage au sort" de /my-shop) — null si la boutique
     * n'existe pas encore.
     */
    private function shopRaffleHistory(?array $shop): array
    {
        if ($shop === null) {
            return ['raffleHistory' => null, 'raffleHistoryTotal' => null, 'rafflePage' => 1, 'raffleTotalPages' => 1, 'rafflePageNumbers' => []];
        }

        $page = max(1, (int) ($_GET['raffle_page'] ?? 1));
        $perPage = 10;

        $result = $this->raffleModel->findByShopIdPaginated($shop['id'], $page, $perPage);
        $totalPages = max(1, (int) ceil($result['total'] / $perPage));

        return [
            'raffleHistory' => $result['entries'],
            'raffleHistoryTotal' => $result['total'],
            'rafflePage' => $page,
            'raffleTotalPages' => $totalPages,
            'rafflePerPage' => $perPage,
            'rafflePageNumbers' => Paginator::buildPageNumbers($page, $totalPages),
        ];
    }

    /**
     * Courbes + chiffres "à vie" de l'onglet Statistiques de /my-shop —
     * revenu net (après commission) et commandes, mêmes conventions que
     * les graphiques de l'admin (voir ChartHelper, admin-chart.js).
     * Null si la boutique n'existe pas encore.
     */
    private function shopStatsCharts(?array $shop, int $days): array
    {
        if ($shop === null) {
            return ['revenueChart' => null, 'ordersChart' => null, 'lifetimeStats' => null];
        }

        $revenueChart = ChartHelper::dailySeries(
            "SELECT DATE(created_at) AS day, COALESCE(SUM(total_price - commission_amount), 0) / 100 AS total
             FROM orders
             WHERE shop_id = :shop_id
             AND status IN ('accepted', 'in_progress', 'delivered', 'completed')
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)",
            $days,
            true,
            ['shop_id' => $shop['id']]
        );

        $ordersChart = ChartHelper::dailySeries(
            "SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM orders
             WHERE shop_id = :shop_id
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)",
            $days,
            false,
            ['shop_id' => $shop['id']]
        );

        return [
            'revenueChart' => $revenueChart,
            'ordersChart' => $ordersChart,
            'lifetimeStats' => $this->orderModel->getShopLifetimeStats($shop['id']),
        ];
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

    /**
     * Listes de styles/types (fixes + validés par l'admin) et demandes de
     * catégorie de l'artiste, pour le formulaire /my-shop.
     */
    private function shopFormExtras(?array $shop): array
    {
        return [
            'availableStyles' => $this->shopModel->getAllStyles(),
            'availableTypes' => $this->shopModel->getAllTypes(),
            'categoryRequests' => $shop !== null ? $this->categoryRequestModel->findByShopId($shop['id']) : [],
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
        $styles = array_values(array_intersect($_POST['styles'] ?? [], $this->shopModel->getAllStyles()));
        $types = array_values(array_intersect($_POST['types'] ?? [], $this->shopModel->getAllTypes()));

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
            ], $this->shopStats($existingShop), $this->shopFormExtras($existingShop)), 'layouts/artist');
            return;
        }

        // is_open n'est pas piloté par ce formulaire : une boutique ne
        // s'ouvre qu'après avoir choisi un abonnement (voir
        // SubscriptionController), puis se pilote via /my-shop/toggle.
        $data = [
            'name' => $name,
            'bio' => $bio,
            'social_instagram' => $socialInstagram !== '' ? $socialInstagram : null,
            'social_facebook' => $socialFacebook !== '' ? $socialFacebook : null,
            'social_pinterest' => $socialPinterest !== '' ? $socialPinterest : null,
            'social_tiktok' => $socialTiktok !== '' ? $socialTiktok : null,
            'banner' => $bannerFilename,
            'styles' => json_encode($styles),
            'types' => json_encode($types),
        ];

        if ($existingShop === null) {
            // Création : on génère un nouveau slug. La boutique démarre
            // fermée et sans formule choisie, sauf si un abonnement a déjà
            // été choisi juste avant (voir SubscriptionController) —
            // c'est désormais l'ordre normal du parcours "Devenir Artiste".
            $data['slug'] = $this->shopModel->generateUniqueSlug($name);
            $data['user_id'] = $_SESSION['user_id'];
            $data['is_open'] = 0;
            $data['plan_selected'] = 0;

            $shopId = $this->shopModel->create($data);

            $pendingSubscription = $_SESSION['pending_shop_subscription'] ?? null;
            unset($_SESSION['pending_shop_subscription']);

            if ($pendingSubscription !== null) {
                if (isset($pendingSubscription['stripe_subscription_id'])) {
                    $this->subscriptionModel->create(array_merge($pendingSubscription, [
                        'shop_id' => $shopId,
                        'status' => 'active',
                    ]));
                } else {
                    $this->subscriptionModel->assignFreePlan($shopId, $pendingSubscription['plan_id']);
                }

                $this->shopModel->update($shopId, ['plan_selected' => 1, 'is_open' => 1]);

                header('Location: /my-shop');
                exit;
            }

            // Accès direct à /my-shop sans être passé par le choix
            // d'abonnement au préalable (ex: URL mise en favori) — la
            // boutique existe mais reste fermée tant qu'un plan n'est
            // pas choisi, /my-subscription gère déjà ce cas normalement.
            header('Location: /my-subscription');
            exit;
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
        ], $this->shopStats($shop), $this->shopFormExtras($shop)), 'layouts/artist');
    }

    // Soumission d'une demande de nouveau style/type (POST /my-shop/category-requests)
    public function submitCategoryRequest(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            header('Location: /my-shop');
            exit;
        }

        $categoryType = $_POST['category_type'] ?? '';
        $name = trim($_POST['name'] ?? '');

        $errors = [];

        if (!in_array($categoryType, ['style', 'type'], true)) {
            $errors['category_request'] = 'Type de catégorie invalide.';
        }

        if (mb_strlen($name) < 2) {
            $errors['category_request'] = 'Le nom proposé doit faire au moins 2 caractères.';
        }

        $imageFilename = null;

        if ($categoryType === 'style') {
            if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errors['category_request'] = 'Un visuel est requis pour proposer un nouveau style.';
            } else {
                $uploadResult = \App\Core\FileUploader::upload(
                    $_FILES['image'],
                    __DIR__ . '/../../public/uploads/category-requests'
                );

                if ($uploadResult['error'] !== null) {
                    $errors['category_request'] = $uploadResult['error'];
                } else {
                    $imageFilename = $uploadResult['filename'];
                }
            }
        }

        if (empty($errors)) {
            $this->categoryRequestModel->create([
                'shop_id' => $shop['id'],
                'category_type' => $categoryType,
                'name' => $name,
                'image' => $imageFilename,
            ]);
        }

        $this->renderer->render('artist/shop', array_merge([
            'shop' => $shop,
            'errors' => $errors,
            'success' => empty($errors) ? 'Ta proposition a bien été envoyée, elle est en attente de validation.' : null,
            'pageTitle' => 'Ma boutique — Toile',
            'pageHeading' => 'Ma boutique',
            'pageSubtitle' => "Personnalise la vitrine publique de ta boutique.",
        ], $this->shopStats($shop), $this->shopFormExtras($shop)), 'layouts/artist');
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
            'availableStyles' => $this->shopModel->getAllStyles(),
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
