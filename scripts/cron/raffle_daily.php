<?php

/**
 * Script cron : tirage au sort hebdomadaire (Page d'accueil).
 * À exécuter chaque lundi.
 * Usage : php scripts/cron/raffle_homepage_draw.php --secret=MA_CLE
 */

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

$pdo = Database::getInstance()->getConnection();
$stripe = new StripeService();

// Lundi de la semaine précédente (les inscriptions de la semaine passée)
$lastMonday = date('Y-m-d', strtotime('last monday'));
$maxWinners = (int) ($_ENV['RAFFLE_HOMEPAGE_WINNERS'] ?? 5);

$stmt = $pdo->prepare(
    "SELECT * FROM raffle_entry
     WHERE type = 'homepage' AND period = :period AND status = 'entered'"
);
$stmt->execute(['period' => $lastMonday]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($entries)) {
    echo "Aucune inscription pour la semaine du {$lastMonday}.\n";
    exit(0);
}

shuffle($entries);
$winners = array_slice($entries, 0, $maxWinners);
$winnerIds = array_column($winners, 'id');

// Date de fin de mise en avant : lundi prochain
$featuredUntil = date('Y-m-d', strtotime('next sunday'));

echo count($winners) . " gagnant(s) pour la semaine du {$lastMonday}.\n";

foreach ($entries as $entry) {
    $isWinner = in_array($entry['id'], $winnerIds, true);
    $newStatus = $isWinner ? 'selected' : 'not_selected';

    if (!empty($entry['stripe_payment_intent_id'])) {
        try {
            if ($isWinner) {
                $stripe->capturePaymentIntent($entry['stripe_payment_intent_id']);
                echo "✅ Capturé : shop_id={$entry['shop_id']}\n";
            } else {
                $stripe->cancelPaymentIntent($entry['stripe_payment_intent_id']);
                echo "🚫 Annulé : shop_id={$entry['shop_id']}\n";
            }
        } catch (\Exception $e) {
            echo "❌ Erreur shop_id={$entry['shop_id']} : " . $e->getMessage() . "\n";
            if ($isWinner) $newStatus = 'not_selected';
        }
    }

    $updateData = ['status' => $newStatus, 'id' => $entry['id']];
    $featuredUntilSql = $isWinner ? ', featured_until = :featured_until' : '';

    $stmt = $pdo->prepare(
        "UPDATE raffle_entry SET status = :status {$featuredUntilSql} WHERE id = :id"
    );

    if ($isWinner) {
        $updateData['featured_until'] = $featuredUntil;
    }

    $stmt->execute($updateData);
}

echo "Tirage homepage terminé.\n";
