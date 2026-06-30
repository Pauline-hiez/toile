<?php

namespace App\Controllers;

use App\Core\FileUploader;
use App\Core\Renderer;
use App\Models\PortfolioImage;
use App\Models\Shop;

class PortfolioController
{
    private Renderer $renderer;
    private PortfolioImage $portfolioModel;
    private Shop $shopModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->portfolioModel = new PortfolioImage();
        $this->shopModel = new Shop();
    }

    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $images = $this->portfolioModel->findByShopId($shop['id']);

        $this->renderer->render('portfolio/index', [
            'images' => $images,
            'error' => null,
        ]);
    }

    public function upload(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $files = $_FILES['images'] ?? null;

        if ($files === null || empty($files['name'][0])) {
            $this->renderer->render('portfolio/index', [
                'images' => $this->portfolioModel->findByShopId($shop['id']),
                'error' => 'Aucune image sélectionnée.',
            ]);
            return;
        }

        $destinationFolder = __DIR__ . '/../../public/uploads/portfolio';
        $errors = [];

        $fileCount = count($files['name']);

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
            ]);
        }

        $this->renderer->render('portfolio/index', [
            'images' => $this->portfolioModel->findByShopId($shop['id']),
            'error' => !empty($errors) ? implode(' ', $errors) : null,
        ]);
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
