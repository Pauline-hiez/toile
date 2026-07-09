<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Core\StripeService;
use App\Models\RaffleEntry;
use App\Models\Shop;

class RaffleController
{
    private Renderer $renderer;
    private RaffleEntry $raffleModel;
    private Shop $shopModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->raffleModel = new RaffleEntry();
        $this->shopModel = new Shop();
    }

    // Page index du tirage au sort
    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $currentMonth = date('Y-m');
        $currentMonday = date('Y-m-d', strtotime('monday this week'));

        $boutiqueEntry = $shop
            ? $this->raffleModel->findByShopTypeAndPeriod($shop['id'], 'boutiques', $currentMonth)
            : null;

        $homepageEntry = $shop
            ? $this->raffleModel->findByShopTypeAndPeriod($shop['id'], 'homepage', $currentMonday)
            : null;

        $this->renderer->render('raffle/index', [
            'shop' => $shop,
            'boutiqueEntry' => $boutiqueEntry,
            'homepageEntry' => $homepageEntry,
            'currentMonth' => $currentMonth,
            'currentMonday' => $currentMonday,
            'rafflePrice' => (int) ($_ENV['RAFFLE_PRICE'] ?? 500),
            'homepagePrice' => (int) ($_ENV['RAFFLE_HOMEPAGE_PRICE'] ?? 700),
            'pageTitle' => 'Tirages au sort — Toile',
        ]);
    }

    // Inscription au tirage au sort
    public function enter(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $type = $_POST['type'] ?? 'boutiques';

        // Détermine la période selon le type
        $period = $type === 'homepage'
            ? date('Y-m-d', strtotime('monday this week'))
            : date('Y-m');

        $price = $type === 'homepage'
            ? (int) ($_ENV['RAFFLE_HOMEPAGE_PRICE'] ?? 700)
            : (int) ($_ENV['RAFFLE_PRICE'] ?? 500);

        // Vérifie qu'il n'y ait pas déjà une inscription
        $existingEntry = $this->raffleModel->findByShopTypeAndPeriod($shop['id'], $type, $period);

        if ($existingEntry !== null) {
            header('Location: /raffle');
            exit;
        }

        $stripe = new StripeService();

        // Crée ou récupère le customer Stripe pour cet utilisateur.
        $userModel = new \App\Models\User();
        $user = $userModel->findById($_SESSION['user_id']);
        $stripeCustomerId = $user['stripe_customer_id'];
        if (empty($stripeCustomerId)) {
            $stripeCustomerId = $stripe->createCustomer($user['email'], $user['username']);
            $userModel->update($user['id'], ['stripe_customer_id' => $stripeCustomerId]);
        }

        $paymentData = $stripe->createPaymentIntent($price, 'eur', [
            'type' => 'raffle_' . $type,
            'shop_id' => $shop['id'],
            'period' => $period,
        ], $stripeCustomerId);

        // Nécessaire pour que le Payment Element affiche la case
        // "Mémoriser cette carte" et les cartes déjà enregistrées.
        $customerSessionClientSecret = $stripe->createCustomerSession($stripeCustomerId);

        $this->raffleModel->create([
            'shop_id' => $shop['id'],
            'type' => $type,
            'period' => $period,
            'stripe_payment_intent_id' => $paymentData['payment_intent_id'],
            'status' => 'entered',
        ]);

        // Stocke le type en session pour la page de confirmation
        $_SESSION['pending_raffle_type'] = $type;

        $this->renderer->render('raffle/payment', [
            'type' => $type,
            'price' => $price,
            'clientSecret' => $paymentData['client_secret'],
            'customerSessionClientSecret' => $customerSessionClientSecret,
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'],
            'pageTitle' => 'Autorisation tirage — Toile',
        ]);
    }

    // Confirmation après autorisation Stripe
    public function confirm(): void
    {
        $type = $_SESSION['pending_raffle_type'] ?? 'boutiques';
        unset($_SESSION['pending_raffle_type']);

        $this->renderer->render('raffle/confirm', [
            'type' => $type,
            'pageTitle' => 'Inscription confirmée — Toile',
        ]);
    }

    // Annule une inscription
    public function cancel(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $type = $_POST['type'] ?? 'boutiques';

        $period = $type === 'homepage'
            ? date('Y-m-d', strtotime('monday this week'))
            : date('Y-m');

        $entry = $this->raffleModel->findByShopTypeAndPeriod($shop['id'], $type, $period);

        if ($entry === null || $entry['status'] !== 'entered') {
            header('Location: /raffle');
            exit;
        }

        if (!empty($entry['stripe_payment_intent_id'])) {
            $stripe = new StripeService();
            try {
                $stripe->cancelPaymentIntent($entry['stripe_payment_intent_id']);
            } catch (\Exception $e) {
                // Supprime quand même l'entrée
            }
        }

        $this->raffleModel->delete($entry['id']);

        header('Location: /raffle');
        exit;
    }
}
