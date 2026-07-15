<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\ServiceBase;
use App\Models\Shop;
use App\Models\ShopSubscription;

class ServiceController
{
    private Renderer $renderer;
    private Service $serviceModel;
    private ServiceOption $optionModel;
    private ServiceBase $baseModel;
    private Shop $shopModel;
    private ShopSubscription $subscriptionModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->serviceModel = new Service();
        $this->optionModel = new ServiceOption();
        $this->baseModel = new ServiceBase();
        $this->shopModel = new Shop();
        $this->subscriptionModel = new ShopSubscription();
    }

    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $services = $this->serviceModel->findByShopId($shop['id']);
        $options = $this->optionModel->findByShopId($shop['id']);
        $bases = $this->baseModel->findByShopId($shop['id']);

        // Stats plus parlantes que de simples comptages de lignes techniques :
        // le nombre de catégories distinctes (pas chaque choix individuel),
        // et le nombre de prestations qui ont au moins une option payante
        // (pas le nombre total d'options).
        $categoryCount = count(array_unique(array_column($bases, 'category')));
        $servicesWithOptionsCount = count(array_unique(array_column($options, 'service_id')));

        $this->renderer->render('artist/services', [
            'services' => $services,
            'options' => $options,
            'bases' => $bases,
            'categoryCount' => $categoryCount,
            'servicesWithOptionsCount' => $servicesWithOptionsCount,
            'shopSlug' => $shop['slug'] ?? null,
            'tab' => $_GET['tab'] ?? 'services',
            'pageTitle' => 'Mes prestations — Toile',
            'pageHeading' => 'Mes prestations',
            'pageSubtitle' => "Gère les prestations et options que tu proposes.",
        ], 'layouts/artist');
    }

    public function create(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $maxOptions = $shop !== null ? $this->subscriptionModel->getMaxOptionsPerService($shop['id']) : 2;

        $this->renderer->render('artist/service-form', [
            'service' => null,
            'options' => [],
            'bases' => [],
            'maxOptions' => $maxOptions,
            'errors' => [],
            'pageTitle' => 'Nouvelle prestation — Toile',
            'pageHeading' => 'Nouvelle prestation',
            'pageSubtitle' => "Renseigne les informations de ta prestation.",
        ], 'layouts/artist');
    }

    public function edit(int $id): void
    {
        $service = $this->getOwnedServiceOrFail($id);
        $options = $this->optionModel->findByServiceId($service['id']);
        $bases = $this->baseModel->findByServiceId($service['id']);
        $maxOptions = $this->subscriptionModel->getMaxOptionsPerService($service['shop_id']);

        $this->renderer->render('artist/service-form', [
            'service' => $service,
            'options' => $options,
            'bases' => $bases,
            'maxOptions' => $maxOptions,
            'errors' => [],
            'pageTitle' => 'Modifier la prestation — Toile',
            'pageHeading' => 'Modifier la prestation',
            'pageSubtitle' => "Mets à jour les informations de ta prestation.",
        ], 'layouts/artist');
    }

    public function save(): void
    {
        $serviceId = !empty($_POST['id']) ? (int) $_POST['id'] : null;

        $existingService = $serviceId !== null
            ? $this->getOwnedServiceOrFail($serviceId)
            : null;

        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $maxOptions = $shop !== null ? $this->subscriptionModel->getMaxOptionsPerService($shop['id']) : 2;

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

        // Image de la prestation — conserve l'existante si aucun nouveau
        // fichier n'est envoyé (même pattern que ShopController::save()
        // pour la bannière de boutique).
        $imageFilename = $existingService['image'] ?? null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = \App\Core\FileUploader::upload(
                $_FILES['image'],
                __DIR__ . '/../../public/uploads/services'
            );

            if ($uploadResult['error'] !== null) {
                $errors['image'] = $uploadResult['error'];
            } else {
                $imageFilename = $uploadResult['filename'];
            }
        }

        if (!empty($errors)) {
            $options = $serviceId !== null ? $this->optionModel->findByServiceId($serviceId) : [];
            $bases = $serviceId !== null ? $this->baseModel->findByServiceId($serviceId) : [];

            $this->renderer->render('artist/service-form', [
                'service' => $existingService ?? $_POST,
                'options' => $options,
                'bases' => $bases,
                'maxOptions' => $maxOptions,
                'errors' => $errors,
                'pageTitle' => 'Prestation — Toile',
                'pageHeading' => $existingService !== null ? 'Modifier la prestation' : 'Nouvelle prestation',
                'pageSubtitle' => "Corrige les champs en erreur ci-dessous.",
            ], 'layouts/artist');
            return;
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'image' => $imageFilename,
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

        // Plafonné au nombre d'options autorisé par l'abonnement de la
        // boutique (voir ShopSubscription::getMaxOptionsPerService()).
        $optionLabels = array_slice($_POST['option_label'] ?? [], 0, $maxOptions);
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

        // Éléments de base : une ligne par catégorie, choix séparés par
        // des virgules (ex. catégorie "Style" → "Réaliste, Illustration"),
        // sans prix — même pattern delete+recreate que les options.
        $this->baseModel->deleteByServiceId($serviceId);

        // Plafonné au même nombre de catégories que d'options autorisées
        // par l'abonnement de la boutique.
        $baseCategories = array_slice($_POST['base_category'] ?? [], 0, $maxOptions);
        $baseChoicesList = $_POST['base_choices'] ?? [];

        foreach ($baseCategories as $index => $category) {
            $category = trim($category);
            $choices = explode(',', $baseChoicesList[$index] ?? '');

            if ($category === '') {
                continue;
            }

            foreach ($choices as $choice) {
                $choice = trim($choice);

                if ($choice === '') {
                    continue;
                }

                $this->baseModel->create([
                    'service_id' => $serviceId,
                    'category' => $category,
                    'label' => $choice,
                ]);
            }
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

    public function deleteOption(int $id): void
    {
        $option = $this->optionModel->findById($id);

        if ($option === null) {
            http_response_code(404);
            echo 'Option introuvable.';
            exit;
        }

        // Vérifie que l'option appartient bien à une prestation de la boutique de l'artiste connecté.
        $this->getOwnedServiceOrFail($option['service_id']);

        $this->optionModel->delete($option['id']);

        header('Location: /my-services?tab=options');
        exit;
    }

    public function deleteBase(int $id): void
    {
        $base = $this->baseModel->findById($id);

        if ($base === null) {
            http_response_code(404);
            echo 'Élément de base introuvable.';
            exit;
        }

        // Vérifie que l'élément de base appartient bien à une prestation de la boutique de l'artiste connecté.
        $this->getOwnedServiceOrFail($base['service_id']);

        $this->baseModel->delete($base['id']);

        header('Location: /my-services?tab=bases');
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
