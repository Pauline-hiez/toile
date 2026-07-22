<?php

namespace App\Core;

/**
 * Formatage de dates en français (les noms de mois de la fonction
 * date() de PHP sont toujours en anglais, quel que soit le contenu
 * affiché ailleurs sur le site). Utilise l'extension intl.
 *
 * Le pattern suit la syntaxe ICU (pas les lettres de date() PHP) :
 * ex: 'd MMM y' -> "10 juil. 2026", 'd MMMM y' -> "10 juillet 2026".
 */
class FrenchDate
{
    public static function format(string $pattern, int|string $timestamp): string
    {
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            null,
            $pattern
        );

        return $formatter->format($timestamp);
    }
}
