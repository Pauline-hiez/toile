<?php

namespace App\Controllers;

use App\Core\FileUploader;
use App\Core\Renderer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\Shop;
use App\Models\OrderMessage;
use App\Models\Notification;
use App\Models\Review;

class OrderController
{
    private Renderer $renderer;
    private Order $orderModel;
    private Service $serviceModel;
    private ServiceOption $optionModel;
    private Shop $shopModel;
    private OrderMessage $messageModel;
    private Notification $notificationModel;
    private Review $reviewModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->orderModel = new Order();
        $this->serviceModel = new Service();
        $this->optionModel = new ServiceOption();
        $this->shopModel = new Shop();
        $this->messageModel = new OrderMessage();
        $this->notificationModel = new Notification();
        $this->reviewModel = new Review();
    }

    public function create(int $serviceId): void
    {
        $service = $this->serviceModel->findById($serviceId);

        if ($service === null || !$service['is_active']) {
            http_response_code(404);
            echo 'Prestation introuvable.';
            exit;
        }

        $shop = $this->shopModel->findById($service['shop_id']);
        $options = $this->optionModel->findByServiceId($service['id']);

        $this->renderer->render('order/create', [
            'service' => $service,
            'shop' => $shop,
            'options' => $options,
            'errors' => [],
        ]);
    }

    /**
     * Traite la soumission du formulaire de commande — étape 1.
     * Valide les données, crée le PaymentIntent, affiche Stripe Elements.
     * (POST /commander)
     */
    public function store(): void
    {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $service = $this->serviceModel->findById($serviceId);

        if ($service === null || !$service['is_active']) {
            http_response_code(404);
            echo 'Prestation introuvable.';
            exit;
        }

        $shop = $this->shopModel->findById($service['shop_id']);
        $options = $this->optionModel->findByServiceId($service['id']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isQuote = isset($_POST['is_quote']);
        $selectedOptionIds = array_map('intval', $_POST['options'] ?? []);

        $errors = [];

        if (mb_strlen($title) < 3) {
            $errors['title'] = 'Le titre doit faire au moins 3 caractères.';
        }

        if (mb_strlen($description) < 10) {
            $errors['description'] = 'La description doit faire au moins 10 caractères.';
        }

        if (!empty($errors)) {
            $this->renderer->render('order/create', [
                'service' => $service,
                'shop' => $shop,
                'options' => $options,
                'errors' => $errors,
                'pageTitle' => 'Commander — Toile',
            ]);
            return;
        }

        // Calcule le prix total.
        $totalPrice = $service['base_price'];
        foreach ($options as $option) {
            if (in_array($option['id'], $selectedOptionIds, true)) {
                $totalPrice += $option['extra_price'];
            }
        }

        // Gestion du fichier de référence.
        $referenceFile = null;
        if (isset($_FILES['reference']) && $_FILES['reference']['error'] === UPLOAD_ERR_OK) {
            $result = FileUploader::upload(
                $_FILES['reference'],
                __DIR__ . '/../../public/uploads/references'
            );
            if ($result['error'] !== null) {
                $errors['reference'] = $result['error'];
                $this->renderer->render('order/create', [
                    'service' => $service,
                    'shop' => $shop,
                    'options' => $options,
                    'errors' => $errors,
                    'pageTitle' => 'Commander — Toile',
                ]);
                return;
            }
            $referenceFile = $result['filename'];
        }

        // Si demande de devis, pas de paiement — on crée directement la commande.
        if ($isQuote) {
            $orderId = $this->orderModel->create([
                'client_id' => $_SESSION['user_id'],
                'shop_id' => $shop['id'],
                'service_id' => $service['id'],
                'title' => $title,
                'description' => $description,
                'total_price' => $totalPrice,
                'status' => 'quote_requested',
                'delivery_file' => $referenceFile,
            ]);

            $this->notificationModel->notify(
                $shop['user_id'],
                'new_order',
                'Nouvelle demande de devis : ' . $title,
                '/commandes/' . $orderId
            );

            header('Location: /commandes/' . $orderId);
            exit;
        }

        // Crée le PaymentIntent Stripe (autorisation différée).
        $stripe = new \App\Core\StripeService();
        $paymentData = $stripe->createPaymentIntent($totalPrice, 'eur', [
            'service_id' => $service['id'],
            'client_id' => $_SESSION['user_id'],
        ]);

        // Stocke les données de commande en session pour les récupérer
        // après la confirmation Stripe (étape suivante).
        $_SESSION['pending_order'] = [
            'client_id' => $_SESSION['user_id'],
            'shop_id' => $shop['id'],
            'service_id' => $service['id'],
            'title' => $title,
            'description' => $description,
            'total_price' => $totalPrice,
            'delivery_file' => $referenceFile,
            'stripe_payment_intent_id' => $paymentData['payment_intent_id'],
        ];

        // Affiche la page de paiement Stripe Elements.
        $this->renderer->render('order/payment', [
            'service' => $service,
            'shop' => $shop,
            'totalPrice' => $totalPrice,
            'clientSecret' => $paymentData['client_secret'],
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'],
            'pageTitle' => 'Paiement — Toile',
        ]);
    }

    /**
     * Finalise la commande après confirmation Stripe
     * (GET /commander/confirm).
     */
    public function confirm(): void
    {
        $pendingOrder = $_SESSION['pending_order'] ?? null;

        if ($pendingOrder === null) {
            header('Location: /');
            exit;
        }

        $stripe = new \App\Core\StripeService();
        $status = $stripe->getPaymentIntentStatus($pendingOrder['stripe_payment_intent_id']);

        if ($status !== 'requires_capture') {
            unset($_SESSION['pending_order']);
            header('Location: /?payment=failed');
            exit;
        }

        $orderId = $this->orderModel->create($pendingOrder);

        $this->notificationModel->notify(
            $pendingOrder['shop_id'],
            'new_order',
            'Nouvelle commande : ' . $pendingOrder['title'],
            '/commandes/' . $orderId
        );

        unset($_SESSION['pending_order']);

        header('Location: /commandes/' . $orderId);
        exit;
    }

    public function myOrders(): void
    {
        $orders = $this->orderModel->findByClientId($_SESSION['user_id']);

        $this->renderer->render('order/my-orders', [
            'orders' => $orders,
        ]);
    }

    public function receivedOrders(): void
    {
        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        $orders = $this->orderModel->findByShopId($shop['id']);

        $this->renderer->render('order/received-orders', [
            'orders' => $orders,
        ]);
    }

    public function transition(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $newStatus = $_POST['status'] ?? '';

        // Détermine le rôle de l'user -> Un artiste devient client s'il passe une commande
        if ($order['shop_owner_id'] === $userId) {
            $actor = 'artist';
        } elseif ($order['client_id'] === $userId) {
            $actor = 'client';
        } else {
            http_response_code(403);
            echo 'Accès refusé: Vous n\'êtes pas concerné par cette commande.';
            exit;
        }

        // Vérifie que la transition demandée est autorisée
        if (!$this->orderModel->canTransition($order['status'], $actor, $newStatus)) {
            http_response_code(403);
            echo 'Transition non autorisée.';
            exit;
        }

        $updateData = ['status' => $newStatus];

        // Si l'artiste livre la commande, il doit uploader le fichier livré
        if ($newStatus === 'delivered') {
            if (!isset($_FILES['delivery_file']) || $_FILES['delivery_file']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo 'Vous devez joindre le fichier livré.';
                exit;
            }

            $result = FileUploader::upload(
                $_FILES['delivery_file'],
                __DIR__ . '/../../public/assets/images/uploads/deliveries'
            );

            if ($result['error'] !== null) {
                http_response_code(400);
                echo htmlspecialchars($result['error']);
                exit;
            }

            $updateData['delivery_file'] = $result['filename'];
        }

        $this->orderModel->update($order['id'], $updateData);

        $recipientId = $actor === 'artist' ? $order['client_id'] : $order['shop_owner_id'];

        $this->notificationModel->notify(
            $recipientId,
            'order_status',
            'Commande #' . $order['id'] . ' : ' . \App\Core\OrderStatus::label($newStatus),
            '/commandes/' . $order['id']
        );

        header('Location: /commandes/' . $order['id']);
        exit;
    }

    public function show(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        $userId = $_SESSION['user_id'];

        if ($order['client_id'] !== $userId && $order['shop_owner_id'] !== $userId) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        $actor = $order['shop_owner_id'] === $userId ? 'artist' : 'client';

        $transitions = $this->orderModel->getAllowedTransitions();
        $allowedStatuses = $transitions[$order['status']][$actor] ?? [];

        // Chargement des messages — nouveau par rapport à avant.
        $messages = $this->messageModel->findByOrderId($order['id']);

        $timelineSteps = [
            'pending'     => 'Demande envoyée',
            'accepted'    => 'Acceptée',
            'in_progress' => 'En cours',
            'delivered'   => 'Livrée',
            'completed'   => 'Terminée',
        ];

        $stepKeys = array_keys($timelineSteps);
        $currentIndex = array_search($order['status'], $stepKeys);
        $existingReview = $this->reviewModel->findByOrderId($order['id']);

        $this->renderer->render('order/show', [
            'order' => $order,
            'actor' => $actor,
            'allowedStatuses' => $allowedStatuses,
            'messages' => $messages,
            'timelineSteps' => $timelineSteps,
            'stepKeys' => $stepKeys,
            'currentIndex' => $currentIndex,
            'existingReview' => $existingReview,
            'pageTitle' => 'Commande #' . $order['id'] . ' — Toile',
        ]);
    }

    public function sendMessage(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Vérification: seul le client et l'artiste ont accès à la messagerie de la commande
        if ($order['client_id'] !== $userId && $order['shop_owner_id'] !== $userId) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        $content = trim($_POST['content'] ?? '');

        if (mb_strlen($content) < 1) {
            header('Location: /commandes/' . $id);
            exit;
        }

        $this->messageModel->create([
            'order_id' => $order['id'],
            'sender_id' => $userId,
            'content' => $content,
        ]);

        $recipientId = $userId === $order['client_id']
            ? $order['shop_owner_id']
            : $order['client_id'];

        $this->notificationModel->notify(
            $recipientId,
            'new_message',
            'Nouveau message sur la commande #' . $order['id'],
            '/commandes/' . $order['id'] . '#messages'
        );

        header('Location: /commandes/' . $id . '#messages');
        exit;
    }
}
