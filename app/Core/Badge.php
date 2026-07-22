<?php

namespace App\Core;

/**
 * Classes Tailwind pour les badges de statut (admin ET espace artiste).
 * Centralise la longue chaîne d'utilitaires pour éviter de la dupliquer
 * dans chaque modèle/vue — seul le nom court de la variante y circule.
 */
class Badge
{
    private const BASE = 'inline-flex items-center px-3 py-1 rounded-full border border-current text-[0.75rem] font-medium';

    private const VARIANTS = [
        'success' => 'bg-success-bg text-success',
        'warning' => 'bg-title-bg text-title',
        'danger' => 'bg-danger-bg text-danger',
        'info' => 'bg-info-bg text-info',
        'neutral' => 'bg-border text-muted',
    ];

    public static function classes(string $variant): string
    {
        $colors = self::VARIANTS[$variant] ?? self::VARIANTS['neutral'];

        return self::BASE . ' ' . $colors;
    }
}
