<?php

namespace App\Core;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Refund;
use Stripe\Subscription;
use Stripe\Customer;
use Stripe\CustomerSession;
use Stripe\Account;
use Stripe\AccountLink;

class StripeService
{
    public function __construct()
    {
        // En mode démonstration, aucun appel Stripe ne peut aboutir (réseau
        // sortant bloqué). On échoue immédiatement plutôt que d'attendre le
        // timeout du SDK : l'exception est interceptée dans index.php pour
        // afficher une page « indisponible en démo ».
        if (Demo::isEnabled()) {
            throw new DemoModeException('Stripe désactivé en mode démonstration.');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    /**
     * $connectedAccountId + $applicationFeeAmount : pour reverser
     * automatiquement la part de l'artiste via un paiement à destination
     * (voir StripeService::createConnectedAccount()) — laissés à null
     * pour tout paiement ne concernant pas une boutique connectée
     * (tirage au sort, abonnement, ou boutique pas encore connectée).
     */
    public function createPaymentIntent(int $amount, string $currency = 'eur', array $metadata = [], ?string $customerId = null, string $captureMethod = 'manual', ?string $connectedAccountId = null, ?int $applicationFeeAmount = null): array
    {
        $params = [
            'amount' => $amount,
            'currency' => $currency,
            'capture_method' => $captureMethod,
            'metadata' => $metadata,
            // Restreint à la carte pour éviter que Stripe Link ne remplace
            // la case "mémoriser cette carte" par son propre encart e-mail.
            'payment_method_types' => ['card'],
            // Affiche le bouton "Mémoriser la carte" dans Stripe
            'setup_future_usage' => 'off_session',
        ];

        if ($customerId !== null) {
            $params['customer'] = $customerId;
        }

        if ($connectedAccountId !== null && $applicationFeeAmount !== null) {
            $params['transfer_data'] = ['destination' => $connectedAccountId];
            $params['application_fee_amount'] = $applicationFeeAmount;
        }

        $paymentIntent = PaymentIntent::create($params);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ];
    }

    /**
     * Crée un compte Stripe Connect Express pour un artiste — reçoit sa
     * part des commandes via des paiements à destination une fois
     * l'inscription (KYC) terminée côté Stripe.
     */
    public function createConnectedAccount(string $email): string
    {
        $account = Account::create([
            'type' => 'express',
            'country' => 'FR',
            'email' => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);

        return $account->id;
    }

    /**
     * Lien d'inscription Stripe hébergé (KYC + coordonnées bancaires) —
     * à usage unique et de courte durée, à régénérer si l'artiste ne le
     * termine pas à temps (voir $refreshUrl).
     */
    public function createAccountOnboardingLink(string $accountId, string $refreshUrl, string $returnUrl): string
    {
        $link = AccountLink::create([
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);

        return $link->url;
    }

    // true une fois l'inscription Stripe Connect suffisamment complète
    // pour recevoir des reversements.
    public function getAccountPayoutsEnabled(string $accountId): bool
    {
        $account = Account::retrieve($accountId);

        return (bool) $account->payouts_enabled;
    }

    // Lien vers le mini-dashboard Stripe Express de l'artiste (une fois connecté).
    public function createAccountLoginLink(string $accountId): string
    {
        $link = Account::createLoginLink($accountId);

        return $link->url;
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
     * Crée un nouveau Price pour le même produit qu'un Price existant,
     * avec un nouveau montant. Les Price Stripe sont immuables (on ne
     * peut pas changer le montant d'un Price déjà créé) — c'est la seule
     * façon de faire évoluer le tarif d'un abonnement (voir aussi
     * migrateSubscriptionToPrice() pour basculer les abonnés déjà actifs
     * vers ce nouveau Price).
     */
    public function createPriceForSamePlan(string $existingPriceId, int $amount, string $currency = 'eur'): string
    {
        $existingPrice = Price::retrieve($existingPriceId);

        $newPrice = Price::create([
            'product' => $existingPrice->product,
            'unit_amount' => $amount,
            'currency' => $currency,
            'recurring' => ['interval' => 'month'],
        ]);

        return $newPrice->id;
    }

    /**
     * Bascule un abonnement Stripe actif vers un nouveau Price.
     * proration_behavior 'none' : le nouveau montant s'applique à partir
     * du prochain renouvellement, pas de facturation/remboursement
     * immédiat en plein milieu du cycle en cours.
     */
    public function migrateSubscriptionToPrice(string $stripeSubscriptionId, string $newPriceId): void
    {
        $subscription = Subscription::retrieve($stripeSubscriptionId);
        $itemId = $subscription->items->data[0]->id;

        Subscription::update($stripeSubscriptionId, [
            'items' => [['id' => $itemId, 'price' => $newPriceId]],
            'proration_behavior' => 'none',
        ]);
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

        // Depuis les versions récentes de l'API Stripe, current_period_start/
        // end ne sont plus sur l'abonnement lui-même (toujours null à cet
        // endroit) mais sur sa ligne (items.data[0]) — les lire au mauvais
        // endroit donnait null, et date('Y-m-d H:i:s', null) retombe sur
        // l'instant présent pour les deux, rendant l'abonnement expiré
        // dès sa création.
        $item = $subscription->items->data[0];

        return [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'current_period_start' => $item->current_period_start,
            'current_period_end' => $item->current_period_end,
        ];
    }

    /**
     * Relit la date de fin de période courante d'un abonnement existant
     * (voir StripeWebhookController::handlePaymentSucceeded() — après un
     * renouvellement, pour prolonger la période en base).
     */
    public function getSubscriptionCurrentPeriodEnd(string $subscriptionId): int
    {
        $subscription = Subscription::retrieve($subscriptionId);
        return $subscription->items->data[0]->current_period_end;
    }

    /**
     * Récupère l'id d'une carte attachée au customer (pour l'abonnement
     * qui vient d'être collectée via SetupIntent, pas forcément déjà
     * marquée comme défaut dans invoice_settings).
     */
    private function getDefaultPaymentMethodId(string $customerId): ?string
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
