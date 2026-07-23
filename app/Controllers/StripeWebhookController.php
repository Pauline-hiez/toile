<?php

namespace App\Controllers;

use App\Models\Notification;
use App\Models\ShopSubscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

/**
 * Reçoit les événements Stripe en temps réel (renouvellements, échecs de
 * paiement, annulations) pour resynchroniser shop_subscription — jusqu'ici
 * seul scripts/cron/subscription_check.php (sondage, non planifié) faisait
 * ce travail. Route publique (voir routes.php), exemptée de CSRF et du
 * mode maintenance (Stripe n'a ni jeton CSRF ni notion de "site en
 * maintenance") — voir CsrfMiddleware et public/index.php.
 */
class StripeWebhookController
{
    private ShopSubscription $subscriptionModel;
    private SubscriptionPlan $planModel;
    private SubscriptionInvoice $invoiceModel;
    private Notification $notificationModel;

    public function __construct()
    {
        $this->subscriptionModel = new ShopSubscription();
        $this->planModel = new SubscriptionPlan();
        $this->invoiceModel = new SubscriptionInvoice();
        $this->notificationModel = new Notification();
    }

    public function handle(): void
    {
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $_ENV['STRIPE_WEBHOOK_SECRET']);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            http_response_code(400);
            echo 'Payload ou signature invalide.';
            exit;
        }

        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
        }

        http_response_code(200);
        echo 'ok';
    }

    /**
     * Renouvellement réussi — remet le statut à jour et prolonge la
     * période courante (nouvelle date lue sur la ligne de l'abonnement,
     * voir StripeService::createSubscription() pour le même détail d'API).
     */
    private function handlePaymentSucceeded(object $invoice): void
    {
        $stripeSubscriptionId = $invoice->subscription ?? null;
        if ($stripeSubscriptionId === null) {
            return;
        }

        $subscription = $this->subscriptionModel->findByStripeSubscriptionId($stripeSubscriptionId);
        if ($subscription === null) {
            return;
        }

        $periodEnd = (new \App\Core\StripeService())->getSubscriptionCurrentPeriodEnd($stripeSubscriptionId);

        $this->subscriptionModel->update($subscription['id'], [
            'status' => 'active',
            'current_period_end' => date('Y-m-d H:i:s', $periodEnd),
        ]);

        $this->recordInvoice($invoice, $subscription);
    }

    /**
     * Trace ce paiement pour permettre la génération d'une facture PDF
     * côté artiste/admin — dédoublonné sur stripe_invoice_id, Stripe
     * pouvant renvoyer le même événement plusieurs fois (retries de
     * webhook). La période de service vient de la ligne de facture
     * (invoice.lines.data[0].period), PAS de invoice.period_start/end qui
     * ne délimitent que le rattachement d'articles à la facture (voir la
     * docstring de la propriété dans le SDK stripe-php).
     */
    private function recordInvoice(object $invoice, array $subscription): void
    {
        if ($this->invoiceModel->findByStripeInvoiceId($invoice->id) !== null) {
            return;
        }

        $line = $invoice->lines->data[0] ?? null;
        if ($line === null) {
            return;
        }

        $plan = $this->planModel->findById($subscription['plan_id']);

        $this->invoiceModel->create([
            'shop_id' => $subscription['shop_id'],
            'plan_name' => $plan['name'] ?? 'Abonnement',
            'amount' => $invoice->amount_paid,
            'stripe_invoice_id' => $invoice->id,
            'period_start' => date('Y-m-d H:i:s', $line->period->start),
            'period_end' => date('Y-m-d H:i:s', $line->period->end),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // Échec de prélèvement — passe la boutique en 'past_due' et prévient l'artiste.
    private function handlePaymentFailed(object $invoice): void
    {
        $stripeSubscriptionId = $invoice->subscription ?? null;
        if ($stripeSubscriptionId === null) {
            return;
        }

        $subscription = $this->subscriptionModel->findByStripeSubscriptionId($stripeSubscriptionId);
        if ($subscription === null || $subscription['status'] === 'past_due') {
            return;
        }

        $this->subscriptionModel->update($subscription['id'], ['status' => 'past_due']);

        $this->notificationModel->notify(
            $subscription['user_id'],
            'subscription_past_due',
            'Le renouvellement de ton abonnement a échoué. Mets à jour ton moyen de paiement.',
            '/profile/payment-methods'
        );
    }

    /**
     * Abonnement supprimé côté Stripe (annulation définitive après échecs
     * répétés, ou suppression manuelle) — repasse la boutique sur le
     * palier gratuit "Commission", comme SubscriptionController::cancel().
     * Chaque boutique doit toujours avoir un palier actif.
     */
    private function handleSubscriptionDeleted(object $stripeSubscription): void
    {
        $subscription = $this->subscriptionModel->findByStripeSubscriptionId($stripeSubscription->id);
        if ($subscription === null) {
            return;
        }

        $freePlan = $this->planModel->findByName('Commission');
        if ($freePlan !== null) {
            $this->subscriptionModel->assignFreePlan($subscription['shop_id'], $freePlan['id']);
        }

        $this->notificationModel->notify(
            $subscription['user_id'],
            'subscription_cancelled',
            'Ton abonnement a expiré. Tu es repassé·e en formule Commission (10%).',
            '/my-subscription'
        );
    }

    // Autres mises à jour (reprise après impayé, etc.) — resynchronise le statut et la période.
    private function handleSubscriptionUpdated(object $stripeSubscription): void
    {
        $subscription = $this->subscriptionModel->findByStripeSubscriptionId($stripeSubscription->id);
        if ($subscription === null) {
            return;
        }

        $newStatus = match ($stripeSubscription->status) {
            'active', 'trialing' => 'active',
            'past_due' => 'past_due',
            'canceled', 'unpaid', 'incomplete_expired' => 'cancelled',
            default => $subscription['status'],
        };

        if ($newStatus === 'cancelled') {
            $this->handleSubscriptionDeleted($stripeSubscription);
            return;
        }

        $currentPeriodEnd = date('Y-m-d H:i:s', $stripeSubscription->items->data[0]->current_period_end);

        $this->subscriptionModel->update($subscription['id'], [
            'status' => $newStatus,
            'current_period_end' => $currentPeriodEnd,
        ]);
    }
}
