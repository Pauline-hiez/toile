<?php

namespace App\Controllers;

use App\Core\FileUploader;
use App\Core\Renderer;
use App\Models\PortfolioImage;
use App\Models\Shop;
use App\Models\ShopSubscription;

class PortfolioController
{
    // Grille 6 colonnes x 6 lignes par page.
    private const PER_PAGE = 36;

    private Renderer $renderer;
    private PortfolioImage $portfolioModel;
    private Shop $shopModel;
    private ShopSubscription $subscriptionModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->portfolioModel = new PortfolioImage();
        $this->shopModel = new Shop();
        $this->subscriptionModel = new ShopSubscription();
    }

    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $this->renderPortfolio($shop, $page, null);
    }

    public function upload(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $maxImages = $this->subscriptionModel->getMaxPortfolioImages($shop['id']);

        $files = $_FILES['images'] ?? null;

        if ($files === null || empty($files['name'][0])) {
            $this->renderPortfolio($shop, 1, 'Aucune image sélectionnée.');
            return;
        }

        $destinationFolder = __DIR__ . '/../../public/uploads/portfolio';
        $errors = [];

        $existingCount = count($this->portfolioModel->findByShopId($shop['id']));
        $remainingSlots = max(0, $maxImages - $existingCount);

        if ($remainingSlots === 0) {
            $errors[] = "Tu as atteint la limite de {$maxImages} images de ton abonnement.";
        }

        $fileCount = min(count($files['name']), $remainingSlots);
        $nextPosition = $existingCount;

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $singleFile = [
                'tmp_name' => $files['tmp_name'][$i],
                'size' => $files['size'][$i],
            ];

            $result = FileUploader::upload($singleFile, $destinationFolder);

            if ($result['error'] !== null) {
                $errors[] = $result['error'];
                continue;
            }

            $this->portfolioModel->create([
                'shop_id' => $shop['id'],
                'filename' => $result['filename'],
                'position' => $nextPosition,
            ]);
            $nextPosition++;
        }

        if (count($files['name']) > $remainingSlots && $remainingSlots > 0) {
            $errors[] = "Seules les {$remainingSlots} premières images ont été ajoutées pour respecter la limite de {$maxImages} de ton abonnement.";
        }

        // Les nouvelles images sont ajoutées en fin de liste : on affiche
        // la dernière page pour qu'elles soient visibles immédiatement.
        $newTotal = $nextPosition;
        $lastPage = max(1, (int) ceil($newTotal / self::PER_PAGE));

        $this->renderPortfolio($shop, $lastPage, !empty($errors) ? implode(' ', $errors) : null);
    }

    /**
     * Rendu partagé de la page portfolio (liste + pagination), utilisé
     * par index() et upload() pour éviter de dupliquer le calcul de
     * pagination et le chargement des variables communes à la vue.
     */
    private function renderPortfolio(array $shop, int $page, ?string $error): void
    {
        $perPage = self::PER_PAGE;
        $result = $this->portfolioModel->findByShopIdPaginated($shop['id'], $page, $perPage);

        $this->renderer->render('artist/portfolio', [
            'images' => $result['images'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'pageNumbers' => $this->buildPageNumbers($page, (int) ceil(max(1, $result['total']) / $perPage)),
            'maxImages' => $this->subscriptionModel->getMaxPortfolioImages($shop['id']),
            'error' => $error,
            'pageTitle' => 'Mon portfolio — Toile',
            'pageHeading' => 'Portfolio',
            'pageSubtitle' => "Partage tes réalisations avec tes clients.",
        ], 'layouts/artist');
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

    /**
     * Réordonne les images de portfolio par glisser-déposer (appelé en
     * AJAX depuis artist/portfolio.php). Les ids ne correspondant pas à
     * une image de la boutique sont ignorés (voir PortfolioImage::reorder()).
     */
    public function reorder(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $orderedIds = array_map('intval', $_POST['order'] ?? []);
        $offset = max(0, (int) ($_POST['offset'] ?? 0));

        $this->portfolioModel->reorder($shop['id'], $orderedIds, $offset);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public function updateLabel(int $id): void
    {
        $image = $this->portfolioModel->findById($id);
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($image === null || $shop === null || $image['shop_id'] !== $shop['id']) {
            http_response_code(403);
            echo 'Accès refusé : cette image ne vous appartient pas.';
            exit;
        }

        $label = trim($_POST['label'] ?? '');

        $this->portfolioModel->update($image['id'], ['label' => $label !== '' ? $label : null]);

        header('Location: /my-portfolio');
        exit;
    }

    public function delete(int $id): void
    {
        $image = $this->portfolioModel->findById($id);
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($image === null || $shop === null || $image['shop_id'] !== $shop['id']) {
            http_response_code(403);
            echo 'Accès refusé : cette image ne vous appartient pas.';
            exit;
        }

        $filePath = __DIR__ . '/../../public/uploads/portfolio/' . $image['filename'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->portfolioModel->delete($image['id']);

        header('Location: /my-portfolio');
        exit;
    }
}
