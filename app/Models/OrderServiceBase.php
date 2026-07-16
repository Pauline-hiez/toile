<?php

namespace App\Models;

/**
 * Choix de base (service_base) sélectionnés par le client sur une
 * commande, un par catégorie. Catégorie et libellé sont dupliqués
 * (snapshot) au moment de la commande pour rester lisibles même si
 * l'artiste modifie/supprime l'élément ensuite.
 */
class OrderServiceBase extends BaseModel
{
    protected string $table = 'order_service_base';

    /**
     * @param array<string, int> $selectedByCategory Catégorie => id du
     *                                                 service_base choisi.
     * @param array $availableBases Éléments disponibles pour cette
     *                               prestation (findByServiceId), pour
     *                               retrouver catégorie + libellé.
     */
    public function createForOrder(int $orderId, array $selectedByCategory, array $availableBases): void
    {
        $basesById = array_column($availableBases, null, 'id');

        foreach ($selectedByCategory as $baseId) {
            if (!isset($basesById[$baseId])) {
                continue;
            }

            $this->create([
                'order_id' => $orderId,
                'service_base_id' => $baseId,
                'category' => $basesById[$baseId]['category'],
                'label' => $basesById[$baseId]['label'],
            ]);
        }
    }

    public function findByOrderId(int $orderId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM order_service_base WHERE order_id = :order_id ORDER BY category, id');
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll();
    }
}
