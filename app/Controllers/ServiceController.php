<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\Shop;

class ServiceController
{
    private Renderer $renderer;
    private Service $serviceModel;
    private ServiceOption $optionModel;
    private Shop $shopModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->serviceModel = new Service();
        $this->optionModel = new ServiceOption();
        $this->shopModel = new Shop();
    }

    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $services = $this->serviceModel->findByShopId($shop['id']);

        $this->renderer->render('service/index', [
            'services' => $services,
        ]);
    }

    public function create(): void
    {
        $this->renderer->render('service/form', [
            'service' => null,
            'options' => [],
            'errors' => [],
        ]);
    }

    public function edit(int $id): void
    {
        $service = $this->getOwnedServiceOrFail($id);
        $options = $this->optionModel->findByServiceId($service['id']);

        $this->renderer->render('service/form', [
            'service' => $service,
            'options' => $options,
            'errors' => [],
        ]);
    }

    public function save(): void
    {
        $serviceId = !empty($_POST['id']) ? (int) $_POST['id'] : null;

        $existingService = $serviceId !== null
            ? $this->getOwnedServiceOrFail($serviceId)
            : null;

        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $basePriceEuros = (float) ($_POST['base_price'] ?? 0);
        $deliveryDays = (int) ($_POST['delivery_days'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];

        if (mb_strlen($title) < 3) {
            $errors['title'] = 'Le titre doit faire au moins 3 caractères.';
        }

        if ($basePriceEuros <= 0) {
            $errors['base_price'] = 'Le prix doit être supérieur à 0.';
        }

        if ($deliveryDays <= 0) {
            $errors['delivery_days'] = 'Le délai doit être supérieur à 0.';
        }

        if (!empty($errors)) {
            $options = $serviceId !== null ? $this->optionModel->findByServiceId($serviceId) : [];

            $this->renderer->render('service/form', [
                'service' => $existingService ?? $_POST,
                'options' => $options,
                'errors' => $errors,
            ]);
            return;
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'base_price' => (int) round($basePriceEuros * 100),
            'delivery_days' => $deliveryDays,
            'is_active' => $isActive,
        ];

        if ($existingService === null) {
            $data['shop_id'] = $shop['id'];
            $serviceId = $this->serviceModel->create($data);
        } else {
            $this->serviceModel->update($existingService['id'], $data);
        }

        $this->optionModel->deleteByServiceId($serviceId);

        $optionLabels = $_POST['option_label'] ?? [];
        $optionPrices = $_POST['option_price'] ?? [];

        foreach ($optionLabels as $index => $label) {
            $label = trim($label);

            if ($label === '') {
                continue;
            }

            $this->optionModel->create([
                'service_id' => $serviceId,
                'label' => $label,
                'extra_price' => (int) round((float) ($optionPrices[$index] ?? 0) * 100),
            ]);
        }

        header('Location: /my-services');
        exit;
    }

    public function delete(int $id): void
    {
        $service = $this->getOwnedServiceOrFail($id);

        $this->serviceModel->delete($service['id']);

        header('Location: /my-services');
        exit;
    }

    private function getOwnedServiceOrFail(int $serviceId): array
    {
        $service = $this->serviceModel->findById($serviceId);
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($service === null || $shop === null || $service['shop_id'] !== $shop['id']) {
            http_response_code(403);
            echo 'Accès refusé : cette prestation ne vous appartient pas.';
            exit;
        }

        return $service;
    }
}
