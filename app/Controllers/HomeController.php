<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Shop;
use App\Models\CategoryRequest;
use App\Models\Setting;

class HomeController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private CategoryRequest $categoryRequestModel;
    private Setting $settingModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->categoryRequestModel = new CategoryRequest();
        $this->settingModel = new Setting();
    }

    /**
     * Styles candidats (5 fixes + validés par l'admin), indexés par nom —
     * source commune à la page d'accueil (sélection curée, cap 5) et à
     * /styles (liste complète).
     */
    private function buildStyleCandidates(): array
    {
        $candidateImages = [];
        foreach (Shop::STYLES as $style) {
            $candidateImages[$style] = isset(Shop::STYLE_TILE_IMAGES[$style]) ? '/assets/images/decor/' . Shop::STYLE_TILE_IMAGES[$style] : null;
        }
        foreach ($this->categoryRequestModel->findApprovedStyleRows() as $request) {
            $candidateImages[$request['name']] = '/uploads/category-requests/' . $request['image'];
        }

        return $candidateImages;
    }

    // Page d'accueil
    public function index(): void
    {
        $featuredShops = $this->shopModel->findFeaturedForHomepage(5);

        // Curation centralisée dans Paramètres → Styles à la une (voir
        // AdminController::updateHomepageStylesSettings()) — cap 5 places,
        // le reste des styles reste consultable sur /styles.
        $candidateImages = $this->buildStyleCandidates();
        $selectedStyles = json_decode($this->settingModel->get('homepage_styles', '') ?: '[]', true) ?: Shop::STYLES;

        $styleTiles = [];
        foreach (array_slice($selectedStyles, 0, 5) as $styleName) {
            if (array_key_exists($styleName, $candidateImages)) {
                $styleTiles[] = ['name' => $styleName, 'image' => $candidateImages[$styleName]];
            }
        }

        $this->renderer->render('home', [
            'pageTitle' => 'Accueil — Toile',
            'featuredShops' => $featuredShops,
            'styleTiles' => $styleTiles,
            'hasMoreStyles' => count($candidateImages) > count($styleTiles),
        ]);
    }

    // Liste complète des styles ("Voir plus" depuis la page d'accueil)
    public function styles(): void
    {
        $candidateImages = $this->buildStyleCandidates();

        $styleTiles = [];
        foreach ($candidateImages as $name => $image) {
            $styleTiles[] = ['name' => $name, 'image' => $image];
        }

        $this->renderer->render('styles', [
            'pageTitle' => 'Tous les styles — Toile',
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

    // Page "Politique de confidentialité"
    public function privacy(): void
    {
        $this->renderer->render('privacy', [
            'pageTitle' => 'Politique de confidentialité — Toile',
        ]);
    }

    // Page "Règlement intérieur"
    public function rules(): void
    {
        $this->renderer->render('rules', [
            'pageTitle' => 'Règlement intérieur — Toile',
        ]);
    }

    // Page "Qui sommes-nous ?"
    public function about(): void
    {
        $this->renderer->render('about', [
            'pageTitle' => 'Qui sommes-nous ? — Toile',
        ]);
    }

    // Page "Conditions générales de vente"
    public function salesTerms(): void
    {
        $this->renderer->render('sales-terms', [
            'pageTitle' => 'Conditions générales de vente — Toile',
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
