<?php

namespace App\Core;

/**
 * Formatage "il y a X min/h/j" pour les notifications — au-delà d'un
 * mois, on retombe sur une date absolue via FrenchDate.
 */
class RelativeTime
{
    public static function format(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return "à l'instant";
        }

        if ($diff < 3600) {
            $minutes = (int) floor($diff / 60);
            return 'il y a ' . $minutes . ' min';
        }

        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return 'il y a ' . $hours . ' h';
        }

        if ($diff < 86400 * 30) {
            $days = (int) floor($diff / 86400);
            return 'il y a ' . $days . ' j';
        }

        return FrenchDate::format('d MMM y', $timestamp);
    }
}
