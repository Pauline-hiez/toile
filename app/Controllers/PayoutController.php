<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Core\StripeService;
use App\Models\Shop;
use App\Models\User;

class PayoutController
{
    private Renderer $renderer;
    private Shop $shopModel;
    private User $userModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->shopModel = new Shop();
        $this->userModel = new User();
    }

    // Page d'état des paiements (GET /my-payouts)
    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        $this->renderer->render('artist/payouts', [
            'shop' => $shop,
            'pageTitle' => 'Mes paiements — Toile',
            'pageHeading' => 'Mes paiements',
            'pageSubtitle' => "Connecte ton compte bancaire pour recevoir directement ta part de chaque commande.",
        ], 'layouts/artist');
    }

    // Lance (ou reprend) l'inscription Stripe Connect (POST /my-payouts/connect)
    public function connect(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            header('Location: /my-shop');
            exit;
        }

        $this->redirectToOnboarding($shop);
    }

    // Reprend l'inscription si le lien précédent a expiré (GET /my-payouts/refresh)
    public function refresh(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            header('Location: /my-shop');
            exit;
        }

        $this->redirectToOnboarding($shop);
    }

    // Retour depuis l'inscription Stripe hébergée (GET /my-payouts/return)
    public function returnFromOnboarding(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null || empty($shop['stripe_account_id'])) {
            header('Location: /my-payouts');
            exit;
        }

        $stripe = new StripeService();
        $payoutsEnabled = $stripe->getAccountPayoutsEnabled($shop['stripe_account_id']);

        $this->shopModel->update($shop['id'], [
            'stripe_payouts_enabled' => $payoutsEnabled ? 1 : 0,
        ]);

        header('Location: /my-payouts');
        exit;
    }

    /**
     * Crée le compte Connect si besoin puis redirige vers le lien
     * d'inscription Stripe hébergé — factorisé entre connect() et
     * refresh() qui font strictement la même chose.
     */
    private function redirectToOnboarding(array $shop): void
    {
        $stripe = new StripeService();

        $accountId = $shop['stripe_account_id'];
        if (empty($accountId)) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            $accountId = $stripe->createConnectedAccount($user['email']);
            $this->shopModel->update($shop['id'], ['stripe_account_id' => $accountId]);
        }

        $appUrl = $_ENV['APP_URL'] ?? 'http://toile.test';
        $onboardingUrl = $stripe->createAccountOnboardingLink(
            $accountId,
            $appUrl . '/my-payouts/refresh',
            $appUrl . '/my-payouts/return'
        );

        header('Location: ' . $onboardingUrl);
        exit;
    }
}
