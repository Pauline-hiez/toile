<?php

namespace App\Core;

/**
 * Construction de la liste de numéros de page pour le composant
 * components/pagination.php (ex: [1, '...', 4, 5, 6, '...', 12]) —
 * extrait d'AdminController pour être réutilisable ailleurs (ex:
 * historique tirage au sort côté artiste, voir RaffleController).
 */
class Paginator
{
    /** @return array<int, int|string> */
    public static function buildPageNumbers(int $currentPage, int $totalPages): array
    {
        $totalPages = max(1, $totalPages);
        $pages = [];

        for ($p = 1; $p <= $totalPages; $p++) {
            if ($p === 1 || $p === $totalPages || abs($p - $currentPage) <= 1) {
                $pages[] = $p;
            } elseif (end($pages) !== '...') {
                $pages[] = '...';
            }
        }

        return $pages;
    }
}
