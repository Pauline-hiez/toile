<?php

namespace App\Models;

class Shop extends BaseModel
{
    protected string $table = 'shop';

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shop WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function slugExists(string $slug, ?int $excludeShopId = null): bool
    {
        $sql = 'SELECT id FROM shop WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeShopId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeShopId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() !== false;
    }

    public function generateUniqueSlug(string $name, ?int $excludeShopId = null): string
    {
        $baseSlug = $this->slugify($name);
        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $excludeShopId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shop WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    private function slugify(string $text): string
    {
        // Translittère les accents (é -> e, à -> a...).
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        // Tout en minuscules.
        $text = strtolower($text);

        // Remplace tout ce qui n'est pas une lettre/chiffre par un tiret.
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Retire les tirets en début/fin de chaîne.
        return trim($text, '-');
    }
}
