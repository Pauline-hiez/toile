<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Shop;

class HomeController
{
    private Renderer $renderer;
    private Shop $shopModel;

    // Illustrations fixes des tuiles de style ("Explorez l'univers de la
    // création") — une image choisie par style plutôt qu'une image de
    // portfolio piochée dynamiquement (pouvait se répéter d'une tuile à
    // l'autre quand une boutique cochait plusieurs styles à la fois).
    private const STYLE_TILE_IMAGES = [
        'anime' => 'anime.png',
        'réaliste' => 'realiste.png',
        'chibi' => 'chibi.png',
        'pixel art' => 'pixel-art.png',
        'concept art' => 'concept-art.png',
    ];

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
    }

    // Page d'accueil
    public function index(): void
    {
        $featuredShops = $this->shopModel->findFeaturedForHomepage(5);

        $styleTiles = [];
        foreach (Shop::STYLES as $style) {
            $styleTiles[] = [
                'name' => $style,
                'image' => self::STYLE_TILE_IMAGES[$style] ?? null,
            ];
        }

        $this->renderer->render('home', [
            'pageTitle' => 'Accueil — Toile',
            'featuredShops' => $featuredShops,
            'styleTiles' => $styleTiles,
        ]);
    }

    // Page "Comment ça marche ?"
    public function howItWorks(): void
    {
        $this->renderer->render('how-it-works', [
            'pageTitle' => 'Comment ça marche ? — Toile',
        ]);
    }

    // Page "Aide et FAQ"
    public function faq(): void
    {
        $this->renderer->render('faq', [
            'pageTitle' => 'Aide et FAQ — Toile',
        ]);
    }

    // Page "Conditions d'utilisation"
    public function terms(): void
    {
        $this->renderer->render('terms', [
            'pageTitle' => "Conditions d'utilisation — Toile",
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
