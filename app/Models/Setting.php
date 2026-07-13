<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Paramètres du site, stockés en clé/valeur. Pas de BaseModel : ce
 * n'est pas une entité avec un id auto-incrémenté, juste un magasin
 * clé/valeur. Mise en cache statique pour éviter de relire la table
 * à chaque appel dans la même requête.
 */
class Setting
{
    private static ?array $cache = null;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $all = $this->loadAll();
        return $all[$key] ?? $default;
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return $this->loadAll();
    }

    public function set(string $key, ?string $value): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO setting (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute(['key' => $key, 'value' => $value]);
        self::$cache = null;
    }

    /** @param array<string, string|null> $pairs */
    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->set($key, $value);
        }
    }

    /** @return array<string, string> */
    private function loadAll(): array
    {
        if (self::$cache === null) {
            $stmt = $this->pdo->query('SELECT `key`, `value` FROM setting');
            self::$cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        return self::$cache;
    }
}
