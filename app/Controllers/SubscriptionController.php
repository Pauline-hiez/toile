<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Core\StripeService;
use App\Models\ShopSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Shop;
use App\Models\User;

class SubscriptionController
{
    private Renderer $renderer;
    private ShopSubscription $subscriptionModel;
    private SubscriptionPlan $planModel;
    private Shop $shopModel;
    private User $userModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->subscriptionModel = new ShopSubscription();
        $this->planModel = new SubscriptionPlan();
        $this->shopModel = new Shop();
        $this->userModel = new User();
    }

    // Choix d'abonnement 
    public function index(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $plans = $this->planModel->findAll();
        $currentSubscription = $shop
            ? $this->subscriptionModel->findActiveByShopId($shop['id'])
            : null;

        $this->renderer->render('subscription/index', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
            'shop' => $shop,
            'pageTitle' => 'Mon abonnement — Toile',
        ]);
    }

    // Lance la souscription d'un plan
    public function subscribe(): void
    {
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $plan = $this->planModel->findById($planId);

        if ($plan === null || empty($plan['stripe_price_id'])) {
            http_response_code(404);
            echo 'Plan introuvable.';
            exit;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        $stripe = new StripeService();

        // Crée ou récupère le customer Stripe.
        $stripeCustomerId = $user['stripe_customer_id'];
        if (empty($stripeCustomerId)) {
            $stripeCustomerId = $stripe->createCustomer($user['email'], $user['username']);
            $this->userModel->update($user['id'], ['stripe_customer_id' => $stripeCustomerId]);
        }

        // Crée un SetupIntent pour collecter la carte.
        $clientSecret = $stripe->createSetupIntent($stripeCustomerId);

        // Stocke le plan_id en session pour l'utiliser après confirmation.
        $_SESSION['pending_subscription_plan_id'] = $planId;
        $_SESSION['pending_stripe_customer_id'] = $stripeCustomerId;

        $this->renderer->render('subscription/payment', [
            'plan' => $plan,
            'clientSecret' => $clientSecret,
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'],
            'pageTitle' => 'Paiement abonnement — Toile',
        ]);
    }

    // Page de confirmation après paiement
    public function confirm(): void
    {
        $planId = $_SESSION['pending_subscription_plan_id'] ?? null;
        $stripeCustomerId = $_SESSION['pending_stripe_customer_id'] ?? null;

        if ($planId === null || $stripeCustomerId === null) {
            header('Location: /my-subscription');
            exit;
        }

        $plan = $this->planModel->findById($planId);
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $stripe = new StripeService();

        // Crée l'abonnement maintenant que la carte est attachée.
        $subscriptionData = $stripe->createSubscription($stripeCustomerId, $plan['stripe_price_id']);

        $existingSubscription = $this->subscriptionModel->findByShopId($shop['id']);

        $subscriptionRecord = [
            'shop_id' => $shop['id'],
            'plan_id' => $plan['id'],
            'stripe_subscription_id' => $subscriptionData['subscription_id'],
            'status' => 'active',
            'current_period_start' => date('Y-m-d H:i:s', $subscriptionData['current_period_start']),
            'current_period_end' => date('Y-m-d H:i:s', $subscriptionData['current_period_end']),
        ];

        if ($existingSubscription !== null) {
            $this->subscriptionModel->update($existingSubscription['id'], $subscriptionRecord);
        } else {
            $this->subscriptionModel->create($subscriptionRecord);
        }

        unset($_SESSION['pending_subscription_plan_id']);
        unset($_SESSION['pending_stripe_customer_id']);

        $this->renderer->render('subscription/confirm', [
            'pageTitle' => 'Abonnement activé — Toile',
        ]);
    }

    // Annule l'abonnement actif
    public function cancel(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $subscription = $this->subscriptionModel->findActiveByShopId($shop['id']);

        if ($subscription === null) {
            header('Location: /my-subscription');
            exit;
        }

        $stripe = new StripeService();
        $stripe->cancelSubscription($subscription['stripe_subscription_id']);

        $this->subscriptionModel->update($subscription['id'], [
            'status' => 'cancelled',
        ]);

        header('Location: /my-subscription');
        exit;
    }
}
