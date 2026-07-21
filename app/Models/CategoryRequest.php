<?php

namespace App\Models;

class CategoryRequest extends BaseModel
{
    protected string $table = 'category_request';

    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM category_request WHERE shop_id = :shop_id ORDER BY created_at DESC'
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }

    public function findPending(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT category_request.*, shop.name AS shop_name, shop.slug AS shop_slug
            FROM category_request
            INNER JOIN shop ON shop.id = category_request.shop_id
            WHERE category_request.status = \'pending\'
            ORDER BY category_request.created_at ASC'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Noms distincts des styles approuvés, à fusionner avec Shop::STYLES.
     */
    public function findApprovedStyleNames(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT name FROM category_request WHERE category_type = 'style' AND status = 'approved'"
        );
        $stmt->execute();

        return array_column($stmt->fetchAll(), 'name');
    }

    /**
     * Noms distincts des types approuvés, à fusionner avec Shop::TYPES.
     */
    public function findApprovedTypeNames(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT name FROM category_request WHERE category_type = 'type' AND status = 'approved'"
        );
        $stmt->execute();

        return array_column($stmt->fetchAll(), 'name');
    }

    /**
     * Styles approuvés (nom + visuel), candidats sélectionnables pour la
     * page d'accueil (voir AdminController::settings() / updateHomepageStylesSettings()).
     */
    public function findApprovedStyleRows(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM category_request WHERE category_type = 'style' AND status = 'approved' ORDER BY created_at ASC"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function approve(int $id): void
    {
        $this->update($id, ['status' => 'approved']);
    }

    public function reject(int $id): void
    {
        $this->update($id, ['status' => 'rejected']);
    }
}
