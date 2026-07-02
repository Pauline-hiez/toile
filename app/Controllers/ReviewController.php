<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Order;
use App\Models\Review;
use App\Models\Notification;

class ReviewController
{
    private Renderer $renderer;
    private Review $reviewModel;
    private Order $orderModel;
    private Notification $notificationModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->reviewModel = new Review();
        $this->orderModel = new Order();
        $this->notificationModel = new Notification();
    }

    public function store(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        $userId = $_SESSION['user_id'];

        if ($order['client_id'] !== $userId) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        if ($order['status'] !== 'completed') {
            http_response_code(403);
            echo 'Vous ne pouvez laisser un avis que sur une commande terminée.';
            exit;
        }

        if ($this->reviewModel->findByOrderId($order['id']) !== null) {
            header('Location: /commandes/' . $id);
            exit;
        }

        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            header('Location: /commandes/' . $id);
            exit;
        }

        $this->reviewModel->create([
            'order_id' => $order['id'],
            'rating' => $rating,
            'comment' => $comment ?: null,
        ]);

        $this->notificationModel->notify(
            $order['shop_owner_id'],
            'new_review',
            'Nouvel avis (' . $rating . '⭐) sur ta boutique',
            '/boutiques/' . $order['shop_slug']
        );

        header('Location: /commandes/' . $id);
        exit;
    }
}
