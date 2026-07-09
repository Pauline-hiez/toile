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

    // Page de tirage au sort
    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $currentMonth = date('Y-m');
        $currentEntry = $shop
            ? $this->raffleModel->findByShopAndMonth($shop['id'], $currentMonth)
            : null;

        $this->renderer->render('raffle/index', [
            'shop' => $shop,
            'currentEntry' => $currentEntry,
            'currentMonth' => $currentMonth,
            'rafflePrice' => (int) ($_ENV['RAFFLE_PRICE'] ?? 500),
            'maxWinners' => (int) ($_ENV['RAFFLE_MAX_WINNERS'] ?? 3),
            'pageTitle' => 'Tirage au sort - Toile',
        ]);
    }

    // Inscription au tirage au sort 
    public function enter(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $currentMonth = date('Y-m');

        // Vérifie qu'il qu'il n'y a qu'une inscription par artiste dans le mois
        $existingEntry = $this->raffleModel->findByShopAndMonth($shop['id'], $currentMonth);

        if ($existingEntry !== null) {
            header('Location: /raffle');
            exit;
        }

        $rafflePrice = (int) ($_ENV['RAFFLE_PRICE'] ?? 500);
        $stripe = new StripeService();

        // Crée une autorisation Stripe sans débit - Capture si sélectionné
        $paymentData = $stripe->createPaymentIntent($rafflePrice, 'eur', [
            'type' => 'raffle',
            'shop_id' => $shop['id'],
            'month' => $currentMonth,
        ]);

        $this->raffleModel->create([
            'shop_id' => $shop['id'],
            'month' => $currentMonth,
            'stripe_payment_intent_id' => $paymentData['payment_intent_id'],
            'status' => 'entered',
        ]);

        // Affiche la page de paiement pour autoriser la carte
        $this->renderer->render('raffle/payment', [
            'rafflePrice' => $rafflePrice,
            'clientSecret' => $paymentData['client_secret'],
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'],
            'pageTitle' => 'Autorisation tirage - Toile',
        ]);
    }

    // Confirmation après autorisation Stripe
    public function confirm(): void
    {
        $this->renderer->render('raffle/confirm', [
            'pageTitle' => 'Inscription confirmée - Toile'
        ]);
    }

    // Annule une inscription
    public function cancel(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $currentMonth = date('Y-m');
        $entry = $this->raffleModel->findByShopAndMonth($shop['id'], $currentMonth);

        if ($entry === null || $entry['status'] !== 'entered') {
            header('Location: /raffle');
            exit;
        }

        // Annule l'autorisation Stripe si elle existe
        if (!empty($entry['stripe_payment_intent_id'])) {
            $stripe = new StripeService();
            try {
                $stripe->cancelPaymentIntent($entry['stripe_payment_intent_id']);
            } catch (\Exception $e) {
                // Supprime quand même l'entrée même si Stripe échoue
            }
        }

        // Supprime l'entrée en BDD
        $this->raffleModel->delete($entry['id']);

        header('Location: /raffle');
        exit;
    }
}
