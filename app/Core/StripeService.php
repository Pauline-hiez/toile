<?php

namespace App\Core;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    // Crée un PaymentIntent en mode autorisation différée appelé à la création d'une commande
    /**
     * @param int    $amount   Montant en centimes (ex: 3500 = 35,00 €).
     * @param string $currency Code de devise ISO (ex: 'eur').
     * @param array  $metadata Données supplémentaires (order_id, etc.).
     *
     * @return array ['client_secret' => string, 'payment_intent_id' => string]
     */
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

    // Capture un PaymentIntent lors de l'acceptation de la commande par l'artiste
    public function capturePaymentIntent(string $paymentIntentId): bool
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentIntent->capture();

        return true;
    }

    // Annule un PaymentIntent (aucun débit) lorsque l'artiste refuse une commande
    public function cancelPaymentIntent(string $paymentIntentId): bool
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $paymentIntent->cancel();

        return true;
    }

    // Rembourse un PaymentIntent déjà capturé si annulation après acceptation
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
}
