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

        // "Commission" est le palier gratuit par défaut, pas une offre à
        // souscrire via Stripe (elle n'a pas de stripe_price_id) — on ne
        // la propose donc pas dans la liste des plans payants.
        $plans = array_values(array_filter(
            $this->planModel->findAll(),
            fn($plan) => $plan['name'] !== 'Commission'
        ));

        $activeSubscription = $shop
            ? $this->subscriptionModel->findActiveByShopId($shop['id'])
            : null;

        // Une boutique sur le palier gratuit a bien une ligne shop_subscription
        // active, mais ce n'est pas un abonnement payant à afficher/annuler.
        $currentSubscription = ($activeSubscription !== null && $activeSubscription['plan_name'] !== 'Commission')
            ? $activeSubscription
            : null;

        $this->renderer->render('subscription/index', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
            'shop' => $shop,
            'pageTitle' => 'Mon abonnement — Toile',
        ]);
    }

    // Confirme le choix du palier gratuit "Commission" — ouvre la boutique
    // sans passer par Stripe (pas de facturation pour ce palier).
    public function confirmFree(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            header('Location: /my-shop');
            exit;
        }

        $freePlan = $this->planModel->findByName('Commission');
        if ($freePlan !== null) {
            $this->subscriptionModel->assignFreePlan($shop['id'], $freePlan['id']);
        }

        $this->shopModel->update($shop['id'], [
            'plan_selected' => 1,
            'is_open' => 1,
        ]);

        header('Location: /my-shop');
        exit;
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

        // Nécessaire pour que le Payment Element affiche la case
        // "Mémoriser cette carte" et les cartes déjà enregistrées.
        $customerSessionClientSecret = $stripe->createCustomerSession($stripeCustomerId);

        // Stocke le plan_id en session pour l'utiliser après confirmation.
        $_SESSION['pending_subscription_plan_id'] = $planId;
        $_SESSION['pending_stripe_customer_id'] = $stripeCustomerId;

        $this->renderer->render('subscription/payment', [
            'plan' => $plan,
            'clientSecret' => $clientSecret,
            'customerSessionClientSecret' => $customerSessionClientSecret,
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

        // La boutique s'ouvre dès qu'un abonnement (gratuit ou payant) a
        // été choisi pour la première fois.
        $this->shopModel->update($shop['id'], [
            'plan_selected' => 1,
            'is_open' => 1,
        ]);

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

        // Rien à annuler si pas d'abonnement, ou déjà sur le palier gratuit
        // (pas de stripe_subscription_id à annuler dans ce cas).
        if ($subscription === null || $subscription['plan_name'] === 'Commission') {
            header('Location: /my-subscription');
            exit;
        }

        $stripe = new StripeService();
        $stripe->cancelSubscription($subscription['stripe_subscription_id']);

        // Repasse la boutique sur le palier gratuit "Commission" plutôt que
        // de laisser une ligne "cancelled" sur un plan payant — chaque
        // boutique a toujours un palier actif, gratuit ou payant.
        $freePlan = $this->planModel->findByName('Commission');
        if ($freePlan !== null) {
            $this->subscriptionModel->assignFreePlan($shop['id'], $freePlan['id']);
        } else {
            $this->subscriptionModel->update($subscription['id'], [
                'status' => 'cancelled',
            ]);
        }

        header('Location: /my-subscription');
        exit;
    }
}
