<?php

namespace App\Core;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Subscription;
use Stripe\Customer;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    public function createPaymentIntent(int $amount, string $currency = 'eur', array $metadata = []): array
    {
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'capture_method' => 'manual',
            'metadata' => $metadata,
        ]);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ];
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
            'default_payment_method' => $this->getDefaultPaymentMethod($customerId),
        ]);

        return [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'current_period_start' => $subscription->current_period_start,
            'current_period_end' => $subscription->current_period_end,
        ];
    }

    /**
     * Récupère la méthode de paiement par défaut d'un customer.
     */
    private function getDefaultPaymentMethod(string $customerId): ?string
    {
        $paymentMethods = \Stripe\PaymentMethod::all([
            'customer' => $customerId,
            'type' => 'card',
        ]);

        return $paymentMethods->data[0]->id ?? null;
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        $subscription = Subscription::retrieve($subscriptionId);
        $subscription->cancel();
        return true;
    }
}
