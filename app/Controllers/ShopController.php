<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Shop;

class ShopController
{
    private Renderer $renderer;
    private Shop $shopModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
    }

    public function manage(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $this->renderer->render('shop/manage', [
            'shop' => $shop,
            'errors' => [],
            'success' => null,
        ]);
    }

    public function save(): void
    {
        $existingShop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $isOpen = isset($_POST['is_open']) ? 1 : 0;

        // Styles envoyés comme plusieurs cases à cocher du même nom "styles[]".
        $styles = $_POST['styles'] ?? [];

        $errors = [];

        if (mb_strlen($name) < 3) {
            $errors['name'] = 'Le nom de la boutique doit faire au moins 3 caractères.';
        }

        if (!empty($errors)) {
            $this->renderer->render('shop/manage', [
                'shop' => $existingShop,
                'errors' => $errors,
                'success' => null,
            ]);
            return;
        }

        $data = [
            'name' => $name,
            'bio' => $bio,
            'is_open' => $isOpen,
            'styles' => json_encode($styles),
        ];

        if ($existingShop === null) {
            // Création : on génère un nouveau slug.
            $data['slug'] = $this->shopModel->generateUniqueSlug($name);
            $data['user_id'] = $_SESSION['user_id'];

            $this->shopModel->create($data);
        } else {
            if ($name !== $existingShop['name']) {
                $data['slug'] = $this->shopModel->generateUniqueSlug($name, $existingShop['id']);
            }

            $this->shopModel->update($existingShop['id'], $data);
        }

        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $this->renderer->render('shop/manage', [
            'shop' => $shop,
            'errors' => [],
            'success' => 'Boutique enregistrée avec succès.',
        ]);
    }
}
