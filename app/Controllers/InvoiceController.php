<?php

namespace App\Controllers;

use App\Core\PdfService;
use App\Core\Renderer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SubscriptionInvoice;
use App\Models\User;

/**
 * Génération des factures PDF (commandes et abonnements), téléchargeables
 * depuis l'espace artiste et l'admin — voir order/show.php (facture de
 * commande) et artist/shop.php + admin/subscriptions.php (factures
 * d'abonnement, alimentées par StripeWebhookController).
 */
class InvoiceController
{
    // Une commande n'a de facture que si le paiement a réellement été
    // capturé — voir OrderController::transition() (cas 'accepted'), seul
    // endroit où commission_rate/commission_amount sont renseignés.
    private const PAID_ORDER_STATUSES = ['accepted', 'in_progress', 'delivered', 'completed'];

    private Renderer $renderer;
    private Order $orderModel;
    private User $userModel;
    private SubscriptionInvoice $invoiceModel;
    private Setting $settingModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->invoiceModel = new SubscriptionInvoice();
        $this->settingModel = new Setting();
    }

    public function orderInvoice(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        $userId = $_SESSION['user_id'];
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        // Réservé à l'artiste propriétaire et à l'admin — le client a son
        // propre suivi de commande, pas de facture (voir la réponse à la
        // question de cadrage : espace artiste + admin uniquement).
        if (!$isAdmin && $order['shop_owner_id'] !== $userId) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        if (!in_array($order['status'], self::PAID_ORDER_STATUSES, true)) {
            http_response_code(404);
            echo "Cette commande n'a pas encore de facture disponible (paiement non finalisé).";
            exit;
        }

        $artist = $this->userModel->findById($order['shop_owner_id']);

        $html = $this->renderToString('pdf/order-invoice', [
            'order' => $order,
            'artist' => $artist,
            'company' => $this->settingModel->all(),
        ]);

        PdfService::download($html, 'facture-commande-' . $order['id'] . '.pdf');
    }

    public function subscriptionInvoice(int $id): void
    {
        $invoice = $this->invoiceModel->findByIdWithShop($id);

        if ($invoice === null) {
            http_response_code(404);
            echo 'Facture introuvable.';
            exit;
        }

        $userId = $_SESSION['user_id'];
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        if (!$isAdmin && $invoice['shop_owner_id'] !== $userId) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        $html = $this->renderToString('pdf/subscription-invoice', [
            'invoice' => $invoice,
            'company' => $this->settingModel->all(),
        ]);

        PdfService::download($html, 'facture-abonnement-' . $invoice['id'] . '.pdf');
    }

    // Rendu de la vue en chaîne (pas envoyée au navigateur) : Renderer::render()
    // fait un echo direct, on capture sa sortie pour la passer à Dompdf.
    private function renderToString(string $view, array $data): string
    {
        ob_start();
        $this->renderer->render($view, $data, false);
        return ob_get_clean();
    }
}
