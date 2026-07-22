<?php

namespace App\Models;

class PortfolioImage extends BaseModel
{
    protected string $table = 'portfolio_image';

    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM portfolio_image WHERE shop_id = :shop_id ORDER BY position ASC, id ASC'
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }

    /**
     * Page d'images de portfolio (grille 6x6 sur /my-portfolio).
     *
     * @return array{images: array, total: int}
     */
    public function findByShopIdPaginated(int $shopId, int $page, int $perPage): array
    {
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM portfolio_image WHERE shop_id = :shop_id');
        $countStmt->execute(['shop_id' => $shopId]);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            'SELECT * FROM portfolio_image WHERE shop_id = :shop_id ORDER BY position ASC, id ASC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue('shop_id', $shopId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return ['images' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Réordonne les images de portfolio d'une boutique selon l'ordre
     * fourni (glisser-déposer côté artiste) — la position de chaque
     * image devient $offset + son index dans $orderedIds ($offset
     * correspondant au début de la page affichée, pour ne pas écraser
     * la position des images des autres pages). Le filtre shop_id dans
     * le WHERE garantit qu'on ne modifie que les images de la boutique
     * concernée, même si $orderedIds contenait un id étranger.
     */
    public function reorder(int $shopId, array $orderedIds, int $offset = 0): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE portfolio_image SET position = :position WHERE id = :id AND shop_id = :shop_id'
        );

        foreach ($orderedIds as $index => $id) {
            $stmt->execute(['position' => $offset + $index, 'id' => $id, 'shop_id' => $shopId]);
        }
    }
}
