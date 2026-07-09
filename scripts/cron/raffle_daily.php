<?php

/**
 * Script cron : sélection quotidienne de la boutique mise en avant.
 * À exécuter chaque jour à minuit.
 *
 * Usage : php scripts/cron/raffle_daily.php --secret=MA_CLE_SECRETE
 */

$options = getopt('', ['secret:']);
if (($options['secret'] ?? '') !== ($_ENV['CRON_SECRET'] ?? '')) {
    echo "Accès refusé.\n";
    exit(1);
}

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

use App\Core\Database;

$pdo = Database::getInstance()->getConnection();
$currentMonth = date('Y-m');

// Réinitialise la mise en avant du jour précédent
$stmt = $pdo->prepare(
    'UPDATE raffle_entry SET featured_today = 0 WHERE month = :month'
);
$stmt->execute(['month' => $currentMonth]);

// Récupère toutes les boutiques sélectionnées ce mois
$stmt = $pdo->prepare(
    "SELECT id FROM raffle_entry
     WHERE month = :month AND status = 'selected'"
);
$stmt->execute(['month' => $currentMonth]);
$selected = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($selected)) {
    echo "Aucune boutique sélectionnée pour {$currentMonth}.\n";
    exit(0);
}

// Tire au sort une boutique parmi les sélectionnées
$featured = $selected[array_rand($selected)];

$stmt = $pdo->prepare(
    'UPDATE raffle_entry SET featured_today = 1 WHERE id = :id'
);
$stmt->execute(['id' => $featured['id']]);

echo "Boutique mise en avant : entry_id={$featured['id']}\n";
