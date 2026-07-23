<?php

namespace App\Core;

use PDO;

/**
 * Limitation du nombre de tentatives sur les routes sensibles (login,
 * register, forgot-password) — protège contre le bruteforce/spam. Une
 * ligne par tentative dans rate_limit_attempt ; l'appelant décide de la
 * granularité de l'identifiant (par IP, par email ciblé, ou les deux en
 * parallèle pour couvrir à la fois les attaques par pulvérisation et les
 * attaques ciblées sur un seul compte).
 */
class RateLimiter
{
    private static function pdo(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    /**
     * True si $identifier a déjà atteint $maxAttempts pour $action dans
     * les $windowMinutes dernières minutes.
     */
    public static function tooManyAttempts(string $identifier, string $action, int $maxAttempts, int $windowMinutes): bool
    {
        $stmt = self::pdo()->prepare(
            'SELECT COUNT(*) FROM rate_limit_attempt
             WHERE identifier = :identifier AND action = :action
             AND created_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)'
        );
        $stmt->bindValue('identifier', $identifier);
        $stmt->bindValue('action', $action);
        $stmt->bindValue('minutes', $windowMinutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() >= $maxAttempts;
    }

    /**
     * Enregistre une tentative. Nettoie aussi, ~1 fois sur 100, les
     * tentatives de plus de 24h (pas de cron dédié pour une table qui
     * reste petite — un nettoyage systématique à chaque appel serait
     * une requête de trop pour un gain négligeable).
     */
    public static function hit(string $identifier, string $action): void
    {
        $stmt = self::pdo()->prepare(
            'INSERT INTO rate_limit_attempt (identifier, action) VALUES (:identifier, :action)'
        );
        $stmt->execute(['identifier' => $identifier, 'action' => $action]);

        if (random_int(1, 100) === 1) {
            self::pdo()->exec('DELETE FROM rate_limit_attempt WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
        }
    }

    /** Efface les tentatives d'un identifiant (ex: connexion réussie). */
    public static function clear(string $identifier, string $action): void
    {
        $stmt = self::pdo()->prepare(
            'DELETE FROM rate_limit_attempt WHERE identifier = :identifier AND action = :action'
        );
        $stmt->execute(['identifier' => $identifier, 'action' => $action]);
    }

    /** IP du visiteur (repli neutre en CLI/tests où REMOTE_ADDR est absent). */
    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
