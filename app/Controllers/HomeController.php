<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Shop;
use App\Models\Setting;

class HomeController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private Setting $settingModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->settingModel = new Setting();
    }

    // buildStyleCandidates() a été extraite vers Shop::getStyleTileImages()
    // (réutilisée par ShopController pour les suggestions d'autocomplétion).

    // Page d'accueil
    public function index(): void
    {
        $featuredShops = $this->shopModel->findFeaturedForHomepage(5);

        // Curation centralisée dans Paramètres → Styles à la une (voir
        // AdminController::updateHomepageStylesSettings()) — cap 5 places,
        // le reste des styles reste consultable sur /styles.
        $candidateImages = $this->shopModel->getStyleTileImages();
        $selectedStyles = json_decode($this->settingModel->get('homepage_styles', '') ?: '[]', true) ?: Shop::STYLES;

        $styleTiles = [];
        foreach (array_slice($selectedStyles, 0, 5) as $styleName) {
            if (array_key_exists($styleName, $candidateImages)) {
                $styleTiles[] = ['name' => $styleName, 'image' => $candidateImages[$styleName]];
            }
        }

        $this->renderer->render('home/index', [
            'pageTitle' => 'Accueil — Toile',
            'featuredShops' => $featuredShops,
            'styleTiles' => $styleTiles,
            'hasMoreStyles' => count($candidateImages) > count($styleTiles),
        ]);
    }

    // Liste complète des styles ("Voir plus" depuis la page d'accueil)
    public function styles(): void
    {
        $candidateImages = $this->shopModel->getStyleTileImages();

        // Même ordre que la sélection curée dans Paramètres → Styles à la
        // une, puis le reste des styles à la suite — cohérent avec l'ordre
        // vu en page d'accueil plutôt qu'un ordre arbitraire.
        $orderedNames = json_decode($this->settingModel->get('homepage_styles', '') ?: '[]', true) ?: Shop::STYLES;
        $orderedNames = array_merge($orderedNames, array_keys($candidateImages));

        $styleTiles = [];
        $seen = [];
        foreach ($orderedNames as $name) {
            if (isset($seen[$name]) || !array_key_exists($name, $candidateImages)) {
                continue;
            }
            $seen[$name] = true;
            $styleTiles[] = ['name' => $name, 'image' => $candidateImages[$name]];
        }

        $this->renderer->render('home/styles', [
            'pageTitle' => 'Tous les styles — Toile',
            'styleTiles' => $styleTiles,
        ]);
    }

    // Page "Comment ça marche ?"
    public function howItWorks(): void
    {
        $this->renderer->render('static/how-it-works', [
            'pageTitle' => 'Comment ça marche ? — Toile',
        ]);
    }

    // Page "Aide et FAQ"
    public function faq(): void
    {
        $this->renderer->render('static/faq', [
            'pageTitle' => 'Aide et FAQ — Toile',
        ]);
    }

    // Page "Conditions d'utilisation"
    public function terms(): void
    {
        $this->renderer->render('static/terms', [
            'pageTitle' => "Conditions d'utilisation — Toile",
        ]);
    }

    // Page "Politique de confidentialité"
    public function privacy(): void
    {
        $this->renderer->render('static/privacy', [
            'pageTitle' => 'Politique de confidentialité — Toile',
        ]);
    }

    // Page "Règlement intérieur"
    public function rules(): void
    {
        $this->renderer->render('static/rules', [
            'pageTitle' => 'Règlement intérieur — Toile',
        ]);
    }

    // Page "Qui sommes-nous ?"
    public function about(): void
    {
        $this->renderer->render('static/about', [
            'pageTitle' => 'Qui sommes-nous ? — Toile',
        ]);
    }

    // Page "Conditions générales de vente"
    public function salesTerms(): void
    {
        $this->renderer->render('static/sales-terms', [
            'pageTitle' => 'Conditions générales de vente — Toile',
        ]);
    }

}
