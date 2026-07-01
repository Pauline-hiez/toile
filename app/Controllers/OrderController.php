<?php

namespace App\Controllers;

use App\Core\FileUploader;
use App\Core\Renderer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\Shop;

class OrderController
{
    private Renderer $renderer;
    private Order $orderModel;
    private Service $serviceModel;
    private ServiceOption $optionModel;
    private Shop $shopModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->orderModel = new Order();
        $this->serviceModel = new Service();
        $this->optionModel = new ServiceOption();
        $this->shopModel = new Shop();
    }

    public function create(int $serviceId): void
    {
        $service = $this->serviceModel->findById($serviceId);

        if ($service === null || !$service['is_active']) {
            http_response_code(404);
            echo 'Prestation introuvable.';
            exit;
        }

        $shop = $this->shopModel->findById($service['shop_id']);
        $options = $this->optionModel->findByServiceId($service['id']);

        $this->renderer->render('order/create', [
            'service' => $service,
            'shop' => $shop,
            'options' => $options,
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $service = $this->serviceModel->findById($serviceId);

        if ($service === null || !$service['is_active']) {
            http_response_code(404);
            echo 'Prestation introuvable.';
            exit;
        }

        $shop = $this->shopModel->findById($service['shop_id']);
        $options = $this->optionModel->findByServiceId($service['id']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isQuote = isset($_POST['is_quote']);

        $selectedOptionIds = array_map('intval', $_POST['options'] ?? []);

        $errors = [];

        if (mb_strlen($title) < 3) {
            $errors['title'] = 'Le titre doit faire au moins 3 caractères.';
        }

        if (mb_strlen($description) < 10) {
            $errors['description'] = 'La description doit faire au moins 10 caractères.';
        }

        if (!empty($errors)) {
            $this->renderer->render('order/create', [
                'service' => $service,
                'shop' => $shop,
                'options' => $options,
                'errors' => $errors,
            ]);
            return;
        }

        $totalPrice = $service['base_price'];

        foreach ($options as $option) {
            if (in_array($option['id'], $selectedOptionIds, true)) {
                $totalPrice += $option['extra_price'];
            }
        }

        $referenceFile = null;

        if (isset($_FILES['reference']) && $_FILES['reference']['error'] === UPLOAD_ERR_OK) {
            $result = FileUploader::upload(
                $_FILES['reference'],
                __DIR__ . '/../../public/uploads/references'
            );

            if ($result['error'] !== null) {
                $errors['reference'] = $result['error'];

                $this->renderer->render('order/create', [
                    'service' => $service,
                    'shop' => $shop,
                    'options' => $options,
                    'errors' => $errors,
                ]);
                return;
            }

            $referenceFile = $result['filename'];
        }

        $orderId = $this->orderModel->create([
            'client_id' => $_SESSION['user_id'],
            'shop_id' => $shop['id'],
            'service_id' => $service['id'],
            'title' => $title,
            'description' => $description,
            'total_price' => $totalPrice,
            'status' => $isQuote ? 'quote_requested' : 'pending',
            'delivery_file' => $referenceFile,
        ]);

        header('Location: /commandes/' . $orderId);
        exit;
    }

    public function myOrders(): void
    {
        $orders = $this->orderModel->findByClientId($_SESSION['user_id']);

        $this->renderer->render('order/my-orders', [
            'orders' => $orders,
        ]);
    }

    public function receivedOrders(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        $orders = $this->orderModel->findByShopId($shop['id']);

        $this->renderer->render('order/received-orders', [
            'orders' => $orders,
        ]);
    }
}
