<?php

/**
 * Script cron : tirage au sort mensuel (Vitrine boutiques).
 * À exécuter le 1er de chaque mois.
 * Usage : php scripts/cron/raffle_draw.php --secret=MA_CLE
 */

$options = getopt('', ['secret:']);
if (($options['secret'] ?? '') !== ($_ENV['CRON_SECRET'] ?? '')) {
    // Chargement du .env d'abord pour les comparaisons suivantes
    require __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
}

require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

if (($options['secret'] ?? '') !== ($_ENV['CRON_SECRET'] ?? '')) {
    echo "Accès refusé.\n";
    exit(1);
}

use App\Core\Database;
use App\Core\StripeService;

$pdo = Database::getInstance()->getConnection();
$stripe = new StripeService();

$lastMonth = date('Y-m', strtotime('last month'));
$maxWinners = (int) ($_ENV['RAFFLE_MAX_WINNERS'] ?? 10);

$stmt = $pdo->prepare(
    "SELECT * FROM raffle_entry
     WHERE type = 'boutiques' AND period = :period AND status = 'entered'"
);
$stmt->execute(['period' => $lastMonth]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($entries)) {
    echo "Aucune inscription pour {$lastMonth}.\n";
    exit(0);
}

shuffle($entries);
$winners = array_slice($entries, 0, $maxWinners);
$winnerIds = array_column($winners, 'id');

echo count($winners) . " gagnant(s) pour {$lastMonth}.\n";

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

    $stmt = $pdo->prepare('UPDATE raffle_entry SET status = :status WHERE id = :id');
    $stmt->execute(['status' => $newStatus, 'id' => $entry['id']]);
}

echo "Tirage boutiques terminé.\n";
