<?php

namespace App\Controllers;

use App\Core\FileUploader;
use App\Core\Renderer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Models\ServiceBase;
use App\Models\OrderServiceBase;
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
    private ServiceBase $baseModel;
    private OrderServiceBase $orderBaseModel;
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
        $this->baseModel = new ServiceBase();
        $this->orderBaseModel = new OrderServiceBase();
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

        if ($shop === null || !$shop['is_open']) {
            http_response_code(403);
            echo 'Cette boutique est actuellement fermée aux commandes.';
            exit;
        }

        $options = $this->optionModel->findByServiceId($service['id']);
        $basesGrouped = $this->baseModel->findByServiceIdGrouped($service['id']);

        $this->renderer->render('order/create', [
            'service' => $service,
            'shop' => $shop,
            'options' => $options,
            'basesGrouped' => $basesGrouped,
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

        if ($shop === null || !$shop['is_open']) {
            http_response_code(403);
            echo 'Cette boutique est actuellement fermée aux commandes.';
            exit;
        }

        $options = $this->optionModel->findByServiceId($service['id']);
        $basesGrouped = $this->baseModel->findByServiceIdGrouped($service['id']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        // Ne fait confiance à la case "demander un devis" que si la
        // boutique accepte les devis — évite qu'un client la force via une
        // requête directe alors que order/create.php ne l'affiche même pas.
        $isQuote = isset($_POST['is_quote']) && !empty($shop['accepts_quotes']);
        $selectedOptionIds = array_map('intval', $_POST['options'] ?? []);

        // Adresse de livraison : uniquement si le client a coché "je
        // souhaite recevoir une version physique" (sinon livraison
        // numérique, aucune adresse enregistrée même si des champs cachés
        // contenaient encore une valeur d'une requête forgée).
        $wantsShipping = isset($_POST['wants_shipping']);
        $shippingFields = [
            'shipping_name' => null,
            'shipping_address_line1' => null,
            'shipping_address_line2' => null,
            'shipping_city' => null,
            'shipping_postal_code' => null,
            'shipping_country' => null,
        ];

        $errors = [];

        if (mb_strlen($title) < 3) {
            $errors['title'] = 'Le titre doit faire au moins 3 caractères.';
        }

        if (mb_strlen($description) < 10) {
            $errors['description'] = 'La description doit faire au moins 10 caractères.';
        }

        if ($wantsShipping) {
            $shippingFields = [
                'shipping_name' => trim($_POST['shipping_name'] ?? ''),
                'shipping_address_line1' => trim($_POST['shipping_address_line1'] ?? ''),
                'shipping_address_line2' => trim($_POST['shipping_address_line2'] ?? ''),
                'shipping_city' => trim($_POST['shipping_city'] ?? ''),
                'shipping_postal_code' => trim($_POST['shipping_postal_code'] ?? ''),
                'shipping_country' => trim($_POST['shipping_country'] ?? ''),
            ];

            if ($shippingFields['shipping_address_line1'] === '' || $shippingFields['shipping_city'] === '' || $shippingFields['shipping_postal_code'] === '') {
                $errors['shipping'] = 'Renseigne au moins l\'adresse, la ville et le code postal pour une livraison physique.';
            }

            $shippingFields = array_map(fn($v) => $v !== '' ? $v : null, $shippingFields);
        }

        // Éléments de base : choix purement descriptifs groupés par
        // catégorie (format, style, matériaux...), sans impact sur le
        // prix — un choix obligatoire par catégorie proposée.
        $submittedBaseChoices = $_POST['service_base'] ?? [];
        $selectedBaseIds = [];

        foreach ($basesGrouped as $category => $categoryBases) {
            $choiceId = isset($submittedBaseChoices[$category]) ? (int) $submittedBaseChoices[$category] : null;
            $validIds = array_column($categoryBases, 'id');

            if ($choiceId === null || !in_array($choiceId, $validIds, true)) {
                $errors['service_base'] = 'Choisis une option pour chaque catégorie proposée.';
                break;
            }

            $selectedBaseIds[] = $choiceId;
        }

        if (!empty($errors)) {
            $this->renderer->render('order/create', [
                'service' => $service,
                'shop' => $shop,
                'options' => $options,
                'basesGrouped' => $basesGrouped,
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
                    'basesGrouped' => $basesGrouped,
                    'errors' => $errors,
                    'pageTitle' => 'Commander — Toile',
                ]);
                return;
            }
            $referenceFile = $result['filename'];
        }

        // Aplatit les catégories pour retrouver catégorie + libellé par id
        // (voir OrderServiceBase::createForOrder()).
        $flatBases = array_merge(...array_values($basesGrouped ?: [[]]));

        // Si demande de devis, pas de paiement — on crée directement la commande.
        if ($isQuote) {
            $orderId = $this->orderModel->create(array_merge([
                'client_id' => $_SESSION['user_id'],
                'shop_id' => $shop['id'],
                'service_id' => $service['id'],
                'title' => $title,
                'description' => $description,
                'total_price' => $totalPrice,
                'status' => 'quote_requested',
                'delivery_file' => $referenceFile,
            ], $shippingFields));

            $this->orderBaseModel->createForOrder($orderId, $selectedBaseIds, $flatBases);

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

        // Si la boutique a connecté son compte bancaire (Stripe Connect),
        // sa part est reversée automatiquement via un paiement à
        // destination — sinon le paiement reste inchangé (tout reste sur
        // le compte plateforme, comme avant la mise en place de Connect).
        $connectedAccountId = null;
        $applicationFeeAmount = null;
        if (!empty($shop['stripe_account_id']) && !empty($shop['stripe_payouts_enabled'])) {
            $connectedAccountId = $shop['stripe_account_id'];
            $commissionRate = $this->subscriptionModel->getCommissionRate($shop['id']);
            $applicationFeeAmount = (int) round($totalPrice * $commissionRate / 100);
        }

        // Crée le PaymentIntent Stripe (autorisation différée).
        $paymentData = $stripe->createPaymentIntent($totalPrice, 'eur', [
            'service_id' => $service['id'],
            'client_id' => $_SESSION['user_id'],
        ], $stripeCustomerId, 'manual', $connectedAccountId, $applicationFeeAmount);

        // Nécessaire pour que le Payment Element affiche la case
        // "Mémoriser cette carte" et les cartes déjà enregistrées.
        $customerSessionClientSecret = $stripe->createCustomerSession($stripeCustomerId);

        // Stocke les données de commande en session pour les récupérer
        // après la confirmation Stripe (étape suivante).
        $_SESSION['pending_order'] = array_merge([
            'client_id' => $_SESSION['user_id'],
            'shop_id' => $shop['id'],
            'service_id' => $service['id'],
            'title' => $title,
            'description' => $description,
            'total_price' => $totalPrice,
            'delivery_file' => $referenceFile,
            'stripe_payment_intent_id' => $paymentData['payment_intent_id'],
        ], $shippingFields);
        // Stocké à part : ce n'est pas une colonne de orders, seulement
        // utilisé après coup pour peupler order_service_base (voir confirm()).
        $_SESSION['pending_order_base_ids'] = $selectedBaseIds;
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
     * Demande de devis générale, sans prestation précise — soumise depuis
     * la modale "Demander un devis" de shop/show.php. Contrairement à
     * store(), pas de service/options/bases : le client décrit librement
     * son projet, le prix sera négocié avec l'artiste via la messagerie
     * de la commande (comme pour les devis liés à une prestation, où le
     * passage "accepted" ne déclenche déjà aucun paiement Stripe faute de
     * PaymentIntent).
     * (POST /boutiques/[slug]/devis)
     */
    public function storeGeneric(string $slug): void
    {
        $shop = $this->shopModel->findBySlug($slug);

        if ($shop === null) {
            http_response_code(404);
            echo 'Boutique introuvable.';
            exit;
        }

        if (!$shop['is_open'] || !$shop['accepts_quotes']) {
            http_response_code(403);
            echo 'Cette boutique n\'accepte pas les demandes de devis pour le moment.';
            exit;
        }

        $description = trim($_POST['description'] ?? '');

        if (mb_strlen($description) < 10) {
            header('Location: /boutiques/' . urlencode($slug) . '?quote_error=1');
            exit;
        }

        // Pas de champ titre dans la modale : on en dérive un à partir du
        // début de la description pour peupler la colonne title (NOT NULL).
        $title = mb_strlen($description) > 80
            ? mb_substr($description, 0, 80) . '…'
            : $description;

        $referenceFile = null;
        if (isset($_FILES['reference']) && $_FILES['reference']['error'] === UPLOAD_ERR_OK) {
            $result = FileUploader::upload(
                $_FILES['reference'],
                __DIR__ . '/../../public/uploads/references'
            );
            if ($result['error'] !== null) {
                header('Location: /boutiques/' . urlencode($slug) . '?quote_error=1');
                exit;
            }
            $referenceFile = $result['filename'];
        }

        $orderId = $this->orderModel->create([
            'client_id' => $_SESSION['user_id'],
            'shop_id' => $shop['id'],
            'service_id' => null,
            'title' => $title,
            'description' => $description,
            'total_price' => 0,
            'status' => 'quote_requested',
            'delivery_file' => $referenceFile,
        ]);

        $this->notificationModel->notify(
            $shop['user_id'],
            'new_order',
            'Nouvelle demande de devis personnalisée : ' . $title,
            '/commandes/' . $orderId
        );

        header('Location: /boutiques/' . urlencode($slug) . '?quote_sent=1');
        exit;
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

        $selectedBaseIds = $_SESSION['pending_order_base_ids'] ?? [];
        if (!empty($selectedBaseIds)) {
            $bases = $this->baseModel->findByServiceId($pendingOrder['service_id']);
            $this->orderBaseModel->createForOrder($orderId, $selectedBaseIds, $bases);
        }

        // notify() attend l'ID de l'utilisateur artiste, pas l'ID de la
        // boutique — il faut résoudre le propriétaire de la boutique.
        $shop = $this->shopModel->findById($pendingOrder['shop_id']);
        $this->notificationModel->notify(
            $shop['user_id'],
            'new_order',
            'Nouvelle commande : ' . $pendingOrder['title'],
            '/commandes/' . $orderId
        );

        unset($_SESSION['pending_order'], $_SESSION['pending_order_base_ids']);

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

        $pendingStatuses = ['quote_requested', 'pending'];
        $pendingOrders = array_values(array_filter($orders, fn($o) => in_array($o['status'], $pendingStatuses, true)));

        $stats = [
            'total' => count($orders),
            'in_progress' => count(array_filter($orders, fn($o) => $o['status'] === 'in_progress')),
            'pending' => count($pendingOrders),
        ];

        $this->renderer->render('artist/orders', [
            'orders' => $orders,
            'stats' => $stats,
            'pendingCount' => count($pendingOrders),
            'pageTitle' => 'Mes commandes — Toile',
            'pageHeading' => 'Mes commandes',
            'pageSubtitle' => "Consulte et gère les commandes reçues sur ta boutique.",
        ], 'layouts/artist');
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

        // Proposition de prix par l'artiste sur un devis — total_price sert
        // à la fois de prix proposé et de prix final, comme pour une
        // commande classique.
        if ($newStatus === 'price_proposed') {
            $proposedPrice = (float) str_replace(',', '.', $_POST['proposed_price'] ?? '');

            if ($proposedPrice <= 0) {
                http_response_code(400);
                echo 'Merci de proposer un prix valide.';
                exit;
            }

            $updateData['total_price'] = (int) round($proposedPrice * 100);

            // Message optionnel accompagnant la proposition de prix — un
            // seul bouton d'envoi plutôt que deux (prix + message
            // séparés), voir la discussion sur la confusion des boutons.
            $priceMessage = trim($_POST['message'] ?? '');
            if ($priceMessage !== '') {
                $this->messageModel->create([
                    'order_id' => $order['id'],
                    'sender_id' => $userId,
                    'content' => $priceMessage,
                ]);
            }
        }

        // Appels Stripe selon la transition
        if (!empty($order['stripe_payment_intent_id'])) {
            $stripe = new \App\Core\StripeService();

            try {
                if ($newStatus === 'accepted') {
                    // Artiste accepte → on capture (débit effectif).
                    // Calcule et enregistre la commission.
                    $commissionRate = $this->subscriptionModel->getCommissionRate($order['shop_id']);
                    $commissionAmount = \App\Core\Commission::calculateAmount($order['total_price'], $commissionRate);

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
                            'tone' => 'success',
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
                                'tone' => 'refund',
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
                'order_status',
                ['email-illustration' => __DIR__ . '/../../public/assets/images/decor/boite.png']
            );
        }

        header('Location: /commandes/' . $order['id']);
        exit;
    }

    /**
     * Étape 1 de l'acceptation d'un prix proposé par l'artiste : le client
     * paie (autorisation Stripe) avant que la commande ne rejoigne le
     * statut 'pending', exactement comme une commande classique — pas de
     * transition directe vers 'accepted' sans paiement.
     * (GET /commandes/[id]/payer-devis)
     */
    public function payQuote(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        if ($order['client_id'] !== $_SESSION['user_id']) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        if ($order['status'] !== 'price_proposed') {
            http_response_code(403);
            echo 'Cette commande n\'a pas de prix proposé en attente de paiement.';
            exit;
        }

        $user = $this->userModel->findById($order['client_id']);
        $stripe = new \App\Core\StripeService();

        $stripeCustomerId = $user['stripe_customer_id'];
        if (empty($stripeCustomerId)) {
            $stripeCustomerId = $stripe->createCustomer($user['email'], $user['username']);
            $this->userModel->update($user['id'], ['stripe_customer_id' => $stripeCustomerId]);
        }

        // Voir store() : reversement automatique de la part de l'artiste
        // si sa boutique a connecté son compte bancaire (Stripe Connect).
        $shop = $this->shopModel->findById($order['shop_id']);
        $connectedAccountId = null;
        $applicationFeeAmount = null;
        if (!empty($shop['stripe_account_id']) && !empty($shop['stripe_payouts_enabled'])) {
            $connectedAccountId = $shop['stripe_account_id'];
            $commissionRate = $this->subscriptionModel->getCommissionRate($shop['id']);
            $applicationFeeAmount = (int) round($order['total_price'] * $commissionRate / 100);
        }

        $paymentData = $stripe->createPaymentIntent($order['total_price'], 'eur', [
            'order_id' => $order['id'],
            'client_id' => $order['client_id'],
        ], $stripeCustomerId, 'manual', $connectedAccountId, $applicationFeeAmount);

        $customerSessionClientSecret = $stripe->createCustomerSession($stripeCustomerId);

        // Contrairement à store(), la commande existe déjà : on persiste
        // le PaymentIntent directement dessus (pas besoin de session), ce
        // qui rend confirmQuotePayment() résistant à un rafraîchissement.
        $this->orderModel->update($order['id'], [
            'stripe_payment_intent_id' => $paymentData['payment_intent_id'],
        ]);

        $this->renderer->render('order/payment', [
            'service' => ['title' => $order['service_title'] ?? $order['title']],
            'shop' => ['name' => $order['shop_name']],
            'totalPrice' => $order['total_price'],
            'clientSecret' => $paymentData['client_secret'],
            'customerSessionClientSecret' => $customerSessionClientSecret,
            'stripePublicKey' => $_ENV['STRIPE_PUBLIC_KEY'],
            'returnUrl' => '/commandes/' . $order['id'] . '/confirmer-devis',
            'pageTitle' => 'Paiement — Toile',
        ]);
    }

    /**
     * Étape 2 : Stripe redirige ici après l'autorisation du paiement.
     * (GET /commandes/[id]/confirmer-devis)
     */
    public function confirmQuotePayment(int $id): void
    {
        $order = $this->orderModel->findByIdWithDetails($id);

        if ($order === null) {
            http_response_code(404);
            echo 'Commande introuvable.';
            exit;
        }

        if ($order['client_id'] !== $_SESSION['user_id']) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        if ($order['status'] !== 'price_proposed' || empty($order['stripe_payment_intent_id'])) {
            header('Location: /commandes/' . $order['id']);
            exit;
        }

        $stripe = new \App\Core\StripeService();
        $status = $stripe->getPaymentIntentStatus($order['stripe_payment_intent_id']);

        if ($status !== 'requires_capture') {
            header('Location: /commandes/' . $order['id'] . '?payment=failed');
            exit;
        }

        $this->orderModel->update($order['id'], ['status' => 'pending']);

        $this->notificationModel->notify(
            $order['shop_owner_id'],
            'new_order',
            'Devis accepté et payé par le client : ' . $order['title'],
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
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        if (!$isAdmin && $order['client_id'] !== $userId && $order['shop_owner_id'] !== $userId) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }

        if ($isAdmin && $order['client_id'] !== $userId && $order['shop_owner_id'] !== $userId) {
            $actor = 'admin';
        } else {
            $actor = $order['shop_owner_id'] === $userId ? 'artist' : 'client';
        }

        $transitions = $this->orderModel->getAllowedTransitions();
        $allowedStatuses = $transitions[$order['status']][$actor] ?? [];

        // Chargement des messages — nouveau par rapport à avant.
        $messages = $this->messageModel->findByOrderId($order['id']);

        $timelineSteps = [
            'pending'     => 'Commande reçue',
            'accepted'    => 'Acceptée',
            'in_progress' => 'En création',
            'delivered'   => 'Livrée',
            'completed'   => 'Terminée',
        ];

        $stepKeys = array_keys($timelineSteps);
        $currentIndex = array_search($order['status'], $stepKeys);
        $existingReview = $this->reviewModel->findByOrderId($order['id']);
        $selectedBases = $this->orderBaseModel->findByOrderId($order['id']);

        $this->renderer->render('order/show', [
            'order' => $order,
            'actor' => $actor,
            'allowedStatuses' => $allowedStatuses,
            'messages' => $messages,
            'timelineSteps' => $timelineSteps,
            'stepKeys' => $stepKeys,
            'currentIndex' => $currentIndex,
            'existingReview' => $existingReview,
            'selectedBases' => $selectedBases,
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
