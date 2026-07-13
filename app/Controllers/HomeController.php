<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Shop;

class HomeController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private Favorite $favoriteModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->favoriteModel = new Favorite();
    }

    // Page d'accueil
    public function index(): void
    {
        $featuredShops = $this->shopModel->findFeaturedForHomepage(5);

        $favoriteShopIds = [];
        $unreadCount = 0;
        if (isset($_SESSION['user_id'])) {
            foreach ($featuredShops as $shop) {
                if ($this->favoriteModel->isFavorite($_SESSION['user_id'], $shop['id'])) {
                    $favoriteShopIds[$shop['id']] = true;
                }
            }

            $unreadCount = (new Notification())->countUnread($_SESSION['user_id']);
        }

        $this->renderer->render('home', [
            'pageTitle' => 'Accueil — Toile',
            'featuredShops' => $featuredShops,
            'favoriteShopIds' => $favoriteShopIds,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function testDb(): void
    {
        try {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            echo 'Connexion à la base de données réussie ✅';
        } catch (\Throwable $e) {
            echo 'Erreur de connexion ❌ : ' . htmlspecialchars($e->getMessage());
        }
    }

    public function testStripe(): void
    {
        try {
            $stripe = new \App\Core\StripeService();
            $result = $stripe->createPaymentIntent(100, 'eur', ['test' => 'ok']);
            echo 'Stripe OK ✅ — PaymentIntent créé : ' . $result['payment_intent_id'];
        } catch (\Exception $e) {
            echo 'Stripe ERROR ❌ : ' . htmlspecialchars($e->getMessage());
        }
    }
}
