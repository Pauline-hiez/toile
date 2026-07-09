<?php

/**
 * Script cron : tirage au sort mensuel.
 * À exécuter le 1er de chaque mois.
 *
 * Usage : php scripts/cron/raffle_draw.php --secret=MA_CLE_SECRETE
 *
 * Protégé par une clé secrète pour éviter les exécutions non autorisées.
 */

// Vérifie la clé secrète
$options = getopt('', ['secret:']);
if (($options['secret'] ?? '') !== ($_ENV['CRON_SECRET'] ?? '')) {
    echo "Accès refusé.\n";
    exit(1);
}

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

use App\Core\Database;
use App\Core\StripeService;

$pdo = Database::getInstance()->getConnection();
$stripe = new StripeService();

$lastMonth = date('Y-m', strtotime('last month'));
$maxWinners = (int) ($_ENV['RAFFLE_MAX_WINNERS'] ?? 3);

// Récupère toutes les inscriptions du mois dernier
$stmt = $pdo->prepare(
    "SELECT * FROM raffle_entry WHERE month = :month AND status = 'entered'"
);
$stmt->execute(['month' => $lastMonth]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($entries)) {
    echo "Aucune inscription pour {$lastMonth}.\n";
    exit(0);
}

// Mélange aléatoirement et sélectionne les gagnants
shuffle($entries);
$winners = array_slice($entries, 0, $maxWinners);
$winnerIds = array_column($winners, 'id');

echo count($winners) . " gagnant(s) sélectionné(s) pour {$lastMonth}.\n";

foreach ($entries as $entry) {
    $isWinner = in_array($entry['id'], $winnerIds, true);
    $newStatus = $isWinner ? 'selected' : 'not_selected';

    if ($isWinner && !empty($entry['stripe_payment_intent_id'])) {
        // Capture le paiement pour les gagnants
        try {
            $stripe->capturePaymentIntent($entry['stripe_payment_intent_id']);
            echo "✅ Capturé : shop_id={$entry['shop_id']}\n";
        } catch (\Exception $e) {
            echo "❌ Erreur capture shop_id={$entry['shop_id']} : " . $e->getMessage() . "\n";
            $newStatus = 'not_selected';
        }
    } elseif (!$isWinner && !empty($entry['stripe_payment_intent_id'])) {
        // Annule l'autorisation pour les non-gagnants
        try {
            $stripe->cancelPaymentIntent($entry['stripe_payment_intent_id']);
            echo "🚫 Annulé : shop_id={$entry['shop_id']}\n";
        } catch (\Exception $e) {
            echo "❌ Erreur annulation shop_id={$entry['shop_id']} : " . $e->getMessage() . "\n";
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE raffle_entry SET status = :status WHERE id = :id'
    );
    $stmt->execute(['status' => $newStatus, 'id' => $entry['id']]);
}

echo "Tirage terminé.\n";
