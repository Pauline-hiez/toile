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
use App\Models\User;
use App\Models\ShopSubscription;

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
    private User $userModel;
    private ShopSubscription $subscriptionModel;

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
        $this->userModel = new User();
        $this->subscriptionModel = new ShopSubscription();
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

        // Crée ou récupère le customer Stripe pour cet utilisateur
        $user = $this->userModel->findById($_SESSION['user_id']);
        $stripe = new \App\Core\StripeService();

        $stripeCustomerId = $user['stripe_customer_id'];
        if (empty($stripeCustomerId)) {
            $stripeCustomerId = $stripe->createCustomer($user['email'], $user['username']);
            $this->userModel->update($user['id'], ['stripe_customer_id' => $stripeCustomerId]);
        }

        // Crée le PaymentIntent Stripe (autorisation différée).
        $paymentData = $stripe->createPaymentIntent($totalPrice, 'eur', [
            'service_id' => $service['id'],
            'client_id' => $_SESSION['user_id'],
        ], $stripeCustomerId);

        // Nécessaire pour que le Payment Element affiche la case
        // "Mémoriser cette carte" et les cartes déjà enregistrées.
        $customerSessionClientSecret = $stripe->createCustomerSession($stripeCustomerId);

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
        $this->renderer->render('order/payment', [
            'service' => $service,
            'shop' => $shop,
            'totalPrice' => $totalPrice,
            'clientSecret' => $paymentData['client_secret'],
            'customerSessionClientSecret' => $customerSessionClientSecret,
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
        $newStatus = $_POST['status'] ?? '';

        if ($order['shop_owner_id'] === $userId) {
            $actor = 'artist';
        } elseif ($order['client_id'] === $userId) {
            $actor = 'client';
        } else {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        if (!$this->orderModel->canTransition($order['status'], $actor, $newStatus)) {
            http_response_code(403);
            echo 'Transition non autorisée.';
            exit;
        }

        $updateData = ['status' => $newStatus];

        // Gestion du fichier livré
        if ($newStatus === 'delivered') {
            if (!isset($_FILES['delivery_file']) || $_FILES['delivery_file']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo 'Vous devez joindre le fichier livré.';
                exit;
            }

            $result = FileUploader::upload(
                $_FILES['delivery_file'],
                __DIR__ . '/../../public/uploads/deliveries'
            );

            if ($result['error'] !== null) {
                http_response_code(400);
                echo htmlspecialchars($result['error']);
                exit;
            }

            $updateData['delivery_file'] = $result['filename'];
        }

        // Appels Stripe selon la transition
        if (!empty($order['stripe_payment_intent_id'])) {
            $stripe = new \App\Core\StripeService();

            try {
                if ($newStatus === 'accepted') {
                    // Artiste accepte → on capture (débit effectif).
                    // Calcule et enregistre la commission.
                    $commissionRate = $this->subscriptionModel->getCommissionRate($order['shop_id']);
                    $commissionAmount = (int) round($order['total_price'] * $commissionRate / 100);

                    // Met à jour la commande avec les infos de commission
                    // avant la capture — pour avoir une trace même si
                    // la capture échoue ensuite.
                    $this->orderModel->update($order['id'], [
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                    ]);

                    $stripe->capturePaymentIntent($order['stripe_payment_intent_id']);

                    // Email de confirmation de débit au client.
                    $client = $this->userModel->findById($order['client_id']);
                    if ($client) {
                        $html = \App\Core\Mailer::renderTemplate('payment-event', [
                            'username' => $client['username'],
                            'message' => 'Ton paiement a été débité suite à l\'acceptation de ta commande par l\'artiste.',
                            'orderId' => $order['id'],
                            'orderTitle' => $order['title'],
                            'amount' => $order['total_price'],
                        ]);
                        \App\Core\Mailer::send(
                            $client['email'],
                            'Paiement débité — Commande #' . $order['id'],
                            $html,
                            'payment_captured'
                        );
                    }

                    $this->notificationModel->notify(
                        $order['client_id'],
                        'payment_captured',
                        'Paiement de ' . number_format($order['total_price'] / 100, 2) . ' € débité — commande acceptée.',
                        '/commandes/' . $order['id']
                    );
                } elseif ($newStatus === 'rejected') {
                    // Artiste refuse → on annule (aucun débit).
                    $stripe->cancelPaymentIntent($order['stripe_payment_intent_id']);

                    $this->notificationModel->notify(
                        $order['client_id'],
                        'payment_cancelled',
                        'Autorisation de paiement annulée — commande refusée.',
                        '/commandes/' . $order['id']
                    );
                } elseif ($newStatus === 'cancelled') {
                    if (in_array($order['status'], ['accepted', 'in_progress'], true)) {
                        // Annulation après acceptation → remboursement.
                        $stripe->refundPaymentIntent($order['stripe_payment_intent_id']);

                        // Email de confirmation de remboursement au client.
                        $client = $this->userModel->findById($order['client_id']);
                        if ($client) {
                            $html = \App\Core\Mailer::renderTemplate('payment-event', [
                                'username' => $client['username'],
                                'message' => 'Un remboursement a été initié suite à l\'annulation de ta commande.',
                                'orderId' => $order['id'],
                                'orderTitle' => $order['title'],
                                'amount' => $order['total_price'],
                            ]);
                            \App\Core\Mailer::send(
                                $client['email'],
                                'Remboursement en cours — Commande #' . $order['id'],
                                $html,
                                'payment_refunded'
                            );
                        }

                        $recipientId = $actor === 'artist'
                            ? $order['client_id']
                            : $order['shop_owner_id'];

                        $this->notificationModel->notify(
                            $order['client_id'],
                            'payment_refunded',
                            'Remboursement de ' . number_format($order['total_price'] / 100, 2) . ' € en cours.',
                            '/commandes/' . $order['id']
                        );
                    } else {
                        // Annulation avant acceptation → simple annulation.
                        $stripe->cancelPaymentIntent($order['stripe_payment_intent_id']);
                    }
                }
            } catch (\Exception $e) {
                // Si Stripe échoue, on n'applique pas la transition —
                // mieux vaut une commande bloquée qu'un état incohérent
                // entre la base et Stripe.
                http_response_code(500);
                echo 'Erreur de paiement : ' . htmlspecialchars($e->getMessage());
                exit;
            }
        }

        $this->orderModel->update($order['id'], $updateData);

        // Notifie l'autre partie du changement de statut.
        $recipientId = $actor === 'artist'
            ? $order['client_id']
            : $order['shop_owner_id'];

        $this->notificationModel->notify(
            $recipientId,
            'order_status',
            'Commande #' . $order['id'] . ' : ' . \App\Core\OrderStatus::label($newStatus),
            '/commandes/' . $order['id']
        );

        $recipient = $actor === 'artist'
            ? $this->userModel->findById($order['client_id'])
            : $this->userModel->findById($order['shop_owner_id']);

        if ($recipient) {
            $html = \App\Core\Mailer::renderTemplate('order-status', [
                'username' => $recipient['username'],
                'orderId' => $order['id'],
                'orderTitle' => $order['title'],
                'statusLabel' => \App\Core\OrderStatus::label($newStatus),
            ]);
            \App\Core\Mailer::send(
                $recipient['email'],
                'Commande #' . $order['id'] . ' — ' . \App\Core\OrderStatus::label($newStatus),
                $html,
                'order_status'
            );
        }

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
