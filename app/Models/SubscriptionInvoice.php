<?php

namespace App\Models;

class SubscriptionInvoice extends BaseModel
{
    protected string $table = 'subscription_invoice';

    public function findByStripeInvoiceId(string $stripeInvoiceId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscription_invoice WHERE stripe_invoice_id = :id');
        $stmt->execute(['id' => $stripeInvoiceId]);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Historique des factures d'une boutique, plus récentes en premier.
     */
    public function findByShopId(int $shopId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM subscription_invoice WHERE shop_id = :shop_id ORDER BY paid_at DESC'
        );
        $stmt->execute(['shop_id' => $shopId]);

        return $stmt->fetchAll();
    }

    /**
     * Facture avec les infos de la boutique/artiste jointes, pour l'affichage
     * du PDF et la vérification de propriété (téléchargement).
     */
    public function findByIdWithShop(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT si.*, s.name AS shop_name, s.slug AS shop_slug, s.user_id AS shop_owner_id,
                    u.username AS owner_username, u.email AS owner_email,
                    u.address_line1 AS owner_address_line1, u.address_line2 AS owner_address_line2,
                    u.city AS owner_city, u.postal_code AS owner_postal_code, u.country AS owner_country
             FROM subscription_invoice si
             INNER JOIN shop s ON s.id = si.shop_id
             INNER JOIN users u ON u.id = s.user_id
             WHERE si.id = :id'
        );
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result ?: null;
    }
}
