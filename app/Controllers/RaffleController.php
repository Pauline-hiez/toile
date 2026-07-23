<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Core\StripeService;
use App\Models\RaffleEntry;
use App\Models\Shop;
use App\Models\Setting;

class RaffleController
{
    private Renderer $renderer;
    private RaffleEntry $raffleModel;
    private Shop $shopModel;
    private Setting $settingModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->raffleModel = new RaffleEntry();
        $this->shopModel = new Shop();
        $this->settingModel = new Setting();
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
            'rafflePrice' => (int) $this->settingModel->get('raffle_price', $_ENV['RAFFLE_PRICE'] ?? '300'),
            'homepagePrice' => (int) $this->settingModel->get('raffle_homepage_price', $_ENV['RAFFLE_HOMEPAGE_PRICE'] ?? '500'),
            'boutiqueEntriesCount' => $this->raffleModel->countByTypeAndPeriod('boutiques', $currentMonth),
            'homepageEntriesCount' => $this->raffleModel->countByTypeAndPeriod('homepage', $currentMonday),
            'nextBoutiquesDraw' => date('Y-m-d 00:00:00', strtotime('first day of next month')),
            'nextHomepageDraw' => date('Y-m-d 00:00:00', strtotime('next monday')),
            'recentWinners' => $this->raffleModel->findRecentWinners(6),
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
            ? (int) $this->settingModel->get('raffle_homepage_price', $_ENV['RAFFLE_HOMEPAGE_PRICE'] ?? '500')
            : (int) $this->settingModel->get('raffle_price', $_ENV['RAFFLE_PRICE'] ?? '300');

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

        // Contrairement aux commandes (autorisation différée, capturée
        // seulement si acceptée), le ticket de tirage est prélevé
        // immédiatement à l'achat, gagnant ou non — capture automatique.
        $paymentData = $stripe->createPaymentIntent($price, 'eur', [
            'type' => 'raffle_' . $type,
            'shop_id' => $shop['id'],
            'period' => $period,
        ], $stripeCustomerId, 'automatic');

        // Nécessaire pour que le Payment Element affiche la case
        // "Mémoriser cette carte" et les cartes déjà enregistrées.
        $customerSessionClientSecret = $stripe->createCustomerSession($stripeCustomerId);

        // L'inscription n'est écrite en base qu'après paiement Stripe
        // réussi (voir confirm()) — sinon un artiste qui abandonne la
        // page de paiement se retrouverait quand même inscrit au tirage
        // sans jamais avoir payé son ticket.
        $_SESSION['pending_raffle'] = [
            'shop_id' => $shop['id'],
            'type' => $type,
            'period' => $period,
            'stripe_payment_intent_id' => $paymentData['payment_intent_id'],
            'amount_paid' => $price,
            'status' => 'entered',
        ];

        $this->renderer->render('raffle/payment', [
            'type' => $type,
            'price' => $price,
            'clientSecret' => $paymentData['client_secret'],
            'customerSessionClientSecret' => $customerSessionClientSecret,
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'],
            'pageTitle' => 'Paiement du ticket — Toile',
        ]);
    }

    // Confirmation après paiement Stripe
    public function confirm(): void
    {
        $pendingRaffle = $_SESSION['pending_raffle'] ?? null;

        if ($pendingRaffle === null) {
            header('Location: /raffle');
            exit;
        }

        $stripe = new StripeService();
        $status = $stripe->getPaymentIntentStatus($pendingRaffle['stripe_payment_intent_id']);

        if ($status !== 'succeeded') {
            unset($_SESSION['pending_raffle']);
            header('Location: /raffle?payment=failed');
            exit;
        }

        $this->raffleModel->create($pendingRaffle);

        unset($_SESSION['pending_raffle']);

        $this->renderer->render('raffle/confirm', [
            'type' => $pendingRaffle['type'],
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
            // Le ticket est prélevé immédiatement à l'achat (capture
            // automatique) — annuler l'inscription doit donc rembourser,
            // pas seulement annuler une autorisation non capturée.
            $stripe = new StripeService();
            try {
                $stripe->refundPaymentIntent($entry['stripe_payment_intent_id']);
            } catch (\Exception $e) {
                // Supprime quand même l'entrée
            }
        }

        $this->raffleModel->delete($entry['id']);

        header('Location: /raffle');
        exit;
    }
}
