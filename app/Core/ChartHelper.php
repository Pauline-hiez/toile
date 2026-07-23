<?php

namespace App\Core;

/**
 * Construction de séries temporelles jour par jour pour les graphiques
 * (voir public/assets/js/admin-chart.js) — extrait d'AdminController pour
 * être réutilisable ailleurs (ex: statistiques boutique côté artiste,
 * voir ShopController::manage()).
 */
class ChartHelper
{
    /**
     * Série temporelle jour par jour à partir d'une requête SQL fournie
     * (doit retourner les colonnes day/total, avec un paramètre nommé
     * :days, et tout paramètre supplémentaire listé dans $params) —
     * comble les jours sans donnée à 0, pour que le graphique ait
     * toujours une courbe continue. $asFloat pour les montants
     * (revenus/commissions), int par défaut (compteurs).
     *
     * @param array<string, mixed> $params Paramètres nommés additionnels (ex: shop_id).
     */
    public static function dailySeries(string $sql, int $days, bool $asFloat = false, array $params = []): array
    {
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('days', $days - 1, \PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $countsByDay = array_column($stmt->fetchAll(), 'total', 'day');

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($date));
            $rawValue = $countsByDay[$date] ?? 0;
            $values[] = $asFloat ? round((float) $rawValue, 2) : (int) $rawValue;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Additionne plusieurs séries temporelles (même format labels/values,
     * mêmes jours) point par point.
     */
    public static function sumSeries(array $series): array
    {
        $labels = $series[0]['labels'] ?? [];
        $values = array_fill(0, count($labels), 0.0);

        foreach ($series as $s) {
            foreach ($s['values'] as $i => $v) {
                $values[$i] += $v;
            }
        }

        return ['labels' => $labels, 'values' => array_map(fn($v) => round($v, 2), $values)];
    }
}
