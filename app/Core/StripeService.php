<?php

namespace App\Core;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Subscription;
use Stripe\Customer;
use Stripe\CustomerSession;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    public function createPaymentIntent(int $amount, string $currency = 'eur', array $metadata = [], ?string $customerId = null): array
    {
        $params = [
            'amount' => $amount,
            'currency' => $currency,
            'capture_method' => 'manual',
            'metadata' => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
            // Affiche le bouton "Mémoriser la carte" dans Stripe
            'setup_future_usage' => 'off_session',
        ];

        if ($customerId !== null) {
            $params['customer'] = $customerId;
        }

        $paymentIntent = PaymentIntent::create($params);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ];
    }

    /**
     * Crée une CustomerSession pour permettre au Payment Element d'afficher
     * la case "Mémoriser cette carte" et les cartes déjà enregistrées.
     */
    public function createCustomerSession(string $customerId): string
    {
        $customerSession = CustomerSession::create([
            'customer' => $customerId,
            'components' => [
                'payment_element' => [
                    'enabled' => true,
                    'features' => [
                        'payment_method_redisplay' => 'enabled',
                        'payment_method_save' => 'enabled',
                        'payment_method_save_usage' => 'off_session',
                        'payment_method_remove' => 'enabled',
                    ],
                ],
            ],
        ]);

        return $customerSession->client_secret;
    }

    public function capturePaymentIntent(string $paymentIntentId): bool
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentIntent->capture();
        return true;
    }

    public function cancelPaymentIntent(string $paymentIntentId): bool
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentIntent->cancel();
        return true;
    }

    public function refundPaymentIntent(string $paymentIntentId, ?int $amount = null): bool
    {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amount !== null) {
            $params['amount'] = $amount;
        }
        Refund::create($params);
        return true;
    }

    public function getPaymentIntentStatus(string $paymentIntentId): string
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        return $paymentIntent->status;
    }

    public function createCustomer(string $email, string $name): string
    {
        $customer = Customer::create([
            'email' => $email,
            'name' => $name,
        ]);
        return $customer->id;
    }

    /**
     * Crée un SetupIntent pour collecter une carte avant l'abonnement.
     */
    public function createSetupIntent(string $customerId): string
    {
        $setupIntent = \Stripe\SetupIntent::create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
        ]);

        return $setupIntent->client_secret;
    }

    /**
     * Crée un abonnement Stripe Billing après collecte de la carte.
     */
    public function createSubscription(string $customerId, string $stripePriceId): array
    {
        $subscription = Subscription::create([
            'customer' => $customerId,
            'items' => [['price' => $stripePriceId]],
            'default_payment_method' => $this->getDefaultPaymentMethodId($customerId),
        ]);

        return [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'current_period_start' => $subscription->current_period_start,
            'current_period_end' => $subscription->current_period_end,
        ];
    }

    /**
     * Récupère l'id de la méthode de paiement par défaut d'un customer.
     */
    private function getDefaultPaymentMethodId(string $customerId): ?string
    {
        return $this->getDefaultPaymentMethod($customerId)['id'] ?? null;
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        $subscription = Subscription::retrieve($subscriptionId);
        $subscription->cancel();
        return true;
    }

    // Liste des cartes enregistrées d'un utilisateur
    public function listPaymentMethods(string $customerId): array
    {
        $paymentMethods = \Stripe\PaymentMethod::all([
            'customer' => $customerId,
            'type' => 'card',
        ]);
        return $paymentMethods->data;
    }

    // Supprime une carte d'un utilisateur
    public function detachPaymentMethod(string $paymentMethodId): bool
    {
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->detach();

        return true;
    }

    // Défini la méthode par défaut d'un utilisateur
    public function setDefaultPaymentMethod(string $customerId, string $paymentMethodId): bool
    {
        \Stripe\Customer::update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);
        return true;
    }

    // Récupère la méthode par défaut d'un utilisateur
    public function getDefaultPaymentMethod(string $customerId): ?array
    {
        $customer = \Stripe\Customer::retrieve($customerId);
        $defaultId = $customer->invoice_settings->default_payment_method;

        if (empty($defaultId)) {
            return null;
        }

        // Retourne un tableau avec les infos utiles pour l'affichage.
        $method = \Stripe\PaymentMethod::retrieve($defaultId);

        return [
            'id' => $method->id,
            'brand' => $method->card->brand,
            'last4' => $method->card->last4,
            'exp_month' => $method->card->exp_month,
            'exp_year' => $method->card->exp_year,
        ];
    }
}
