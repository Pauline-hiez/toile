<?php

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$options = getopt('', ['secret:']);
if (($options['secret'] ?? '') !== ($_ENV['CRON_SECRET'] ?? '')) {
    echo "Accès refusé.\n";
    exit(1);
}

use App\Core\Database;
use App\Core\StripeService;
use App\Models\Notification;

$pdo = Database::getInstance()->getConnection();
$stripe = new StripeService();
$notificationModel = new Notification();

$stmt = $pdo->prepare(
    "SELECT ss.*, s.user_id, s.name AS shop_name
     FROM shop_subscription ss
     INNER JOIN shop s ON s.id = ss.shop_id
     WHERE ss.status IN ('active', 'past_due')"
);
$stmt->execute();
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($subscriptions)) {
    echo "Aucun abonnement actif à vérifier.\n";
    exit(0);
}

echo count($subscriptions) . " abonnement(s) à vérifier.\n";

foreach ($subscriptions as $subscription) {
    try {
        $stripeSubscription = \Stripe\Subscription::retrieve(
            $subscription['stripe_subscription_id']
        );

        $stripeStatus = $stripeSubscription->status;
        $currentPeriodEnd = date('Y-m-d H:i:s', $stripeSubscription->current_period_end);

        echo "Abonnement {$subscription['stripe_subscription_id']} : Stripe={$stripeStatus}\n";

        $newStatus = match ($stripeStatus) {
            'active', 'trialing' => 'active',
            'past_due' => 'past_due',
            'canceled', 'unpaid', 'incomplete_expired' => 'cancelled',
            default => $subscription['status'],
        };

        if ($newStatus !== $subscription['status']) {
            $stmt = $pdo->prepare(
                'UPDATE shop_subscription
                 SET status = :status, current_period_end = :period_end
                 WHERE id = :id'
            );
            $stmt->execute([
                'status' => $newStatus,
                'period_end' => $currentPeriodEnd,
                'id' => $subscription['id'],
            ]);

            echo "→ Statut mis à jour : {$subscription['status']} → {$newStatus}\n";

            if ($newStatus === 'cancelled') {
                $notificationModel->notify(
                    $subscription['user_id'],
                    'subscription_cancelled',
                    'Ton abonnement a expiré. Tu es repassé·e en formule Commission (10%).',
                    '/my-subscription'
                );
            } elseif ($newStatus === 'past_due') {
                $notificationModel->notify(
                    $subscription['user_id'],
                    'subscription_past_due',
                    'Le renouvellement de ton abonnement a échoué. Mets à jour ton moyen de paiement.',
                    '/profile/payment-methods'
                );
            }
        } else {
            $stmt = $pdo->prepare(
                'UPDATE shop_subscription
                 SET current_period_end = :period_end
                 WHERE id = :id'
            );
            $stmt->execute([
                'period_end' => $currentPeriodEnd,
                'id' => $subscription['id'],
            ]);

            echo "→ Date de fin mise à jour : {$currentPeriodEnd}\n";
        }
    } catch (\Exception $e) {
        echo "❌ Erreur : " . $e->getMessage() . "\n";
    }
}

echo "Vérification terminée.\n";
