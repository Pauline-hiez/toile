<?php

namespace App\Core;

/**
 * Numérotation séquentielle des factures, par série et par année civile
 * (ex: CMD-2026-0001, ABO-2026-0001). Chaque série a sa propre séquence
 * continue, ce qui est admis dès lors que chaque série est chronologique
 * et sans trou (art. 242 nonies A CGI).
 *
 * L'incrément passe par un INSERT ... ON DUPLICATE KEY UPDATE avec
 * LAST_INSERT_ID() : MySQL rend l'opération atomique (verrou de ligne le
 * temps de la requête), donc deux appels concurrents (capture d'une
 * commande + webhook d'abonnement, par ex.) ne peuvent pas obtenir le
 * même numéro ni créer de trou.
 */
class InvoiceNumber
{
    /**
     * Réserve et renvoie le prochain numéro de la série pour l'année en
     * cours, formaté (ex: "CMD-2026-0007").
     */
    public static function next(string $series): string
    {
        $pdo = Database::getInstance()->getConnection();
        $year = (int) date('Y');

        // last_number passe de N à N+1 de façon atomique ; LAST_INSERT_ID()
        // renvoie la valeur incrémentée (à la première insertion, 1).
        $stmt = $pdo->prepare(
            'INSERT INTO invoice_counter (series, year, last_number)
             VALUES (:series, :year, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)'
        );
        $stmt->execute(['series' => $series, 'year' => $year]);

        $number = (int) $pdo->lastInsertId();

        return sprintf('%s-%d-%05d', $series, $year, $number);
    }
}
