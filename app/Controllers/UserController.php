<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\Shop;
use App\Models\ShopSubscription;
use App\Models\User;
use App\Core\StripeService;

class UserController
{
    private Renderer $renderer;
    private User $userModel;
    private Shop $shopModel;
    private ShopSubscription $subscriptionModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->userModel = new User();
        $this->shopModel = new Shop();
        $this->subscriptionModel = new ShopSubscription();
    }

    /**
     * Boutique/abonnement de l'artiste connecté (pour les cartes stats de
     * la page profil), et layout à utiliser : les artistes voient leur
     * espace dédié (sidebar), les autres rôles gardent le layout public.
     *
     * @return array{layout: string|null, isArtist: bool, shop: array|null, subscription: array|null}
     */
    private function artistContext(): array
    {
        if (($_SESSION['user_role'] ?? '') !== 'artist') {
            return ['layout' => null, 'isArtist' => false, 'shop' => null, 'subscription' => null];
        }

        $shop = $this->shopModel->findByUserId($_SESSION['user_id']);
        $subscription = $shop !== null ? $this->subscriptionModel->findActiveByShopId($shop['id']) : null;

        return ['layout' => 'layouts/artist', 'isArtist' => true, 'shop' => $shop, 'subscription' => $subscription];
    }

    public function showProfile(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);
        $context = $this->artistContext();

        $this->renderer->render('user/profile', [
            'user' => $user,
            'errors' => [],
            'success' => null,
            'isArtist' => $context['isArtist'],
            'subscription' => $context['subscription'],
            'pageTitle' => 'Mon profil — Toile',
        ], $context['layout']);
    }

    public function updateProfile(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);
        $username = trim($_POST['username'] ?? '');

        $errors = [];

        if (mb_strlen($username) < 3) {
            $errors['username'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        }

        $avatarFilename = $user['avatar'];

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = \App\Core\FileUploader::upload(
                $_FILES['avatar'],
                __DIR__ . '/../../public/uploads/avatars'
            );

            if ($uploadResult['error'] !== null) {
                $errors['avatar'] = $uploadResult['error'];
            } else {
                $avatarFilename = $uploadResult['filename'];
            }
        }

        $context = $this->artistContext();

        if (!empty($errors)) {
            $this->renderer->render('user/profile', [
                'user' => $user,
                'errors' => $errors,
                'success' => null,
                'isArtist' => $context['isArtist'],
                'subscription' => $context['subscription'],
                'pageTitle' => 'Mon profil — Toile',
            ], $context['layout']);
            return;
        }

        $this->userModel->update($user['id'], [
            'username' => $username,
            'avatar' => $avatarFilename,
        ]);

        $user = $this->userModel->findById($user['id']);

        $this->renderer->render('user/profile', [
            'user' => $user,
            'errors' => [],
            'success' => 'Profil mis à jour avec succès.',
            'isArtist' => $context['isArtist'],
            'subscription' => $context['subscription'],
            'pageTitle' => 'Mon profil — Toile',
        ], $context['layout']);
    }

    public function updatePassword(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';

        $errors = [];

        if ($user['password_hash'] === null || !password_verify($currentPassword, $user['password_hash'])) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        }

        if (mb_strlen($newPassword) < 8) {
            $errors['new_password'] = 'Le nouveau mot de passe doit faire au moins 8 caractères.';
        } elseif ($newPassword !== $newPasswordConfirm) {
            $errors['new_password'] = 'Les mots de passe ne correspondent pas.';
        }

        $context = $this->artistContext();

        if (!empty($errors)) {
            $this->renderer->render('user/profile', [
                'user' => $user,
                'errors' => $errors,
                'success' => null,
                'isArtist' => $context['isArtist'],
                'subscription' => $context['subscription'],
                'pageTitle' => 'Mon profil — Toile',
            ], $context['layout']);
            return;
        }

        $this->userModel->update($user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);

        $this->renderer->render('user/profile', [
            'user' => $user,
            'errors' => [],
            'success' => 'Mot de passe modifié avec succès.',
            'isArtist' => $context['isArtist'],
            'subscription' => $context['subscription'],
            'pageTitle' => 'Mon profil — Toile',
        ], $context['layout']);
    }

    // Affiche les cartes enregistrées
    public function paymentMethods(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);
        $savedCards = [];

        if (!empty($user['stripe_customer_id'])) {
            $stripe = new StripeService();
            $savedCards = $stripe->listPaymentMethods($user['stripe_customer_id']);
        }

        $this->renderer->render('user/payment-methods', [
            'savedCards' => $savedCards,
            'pageTitle' => 'Mes moyens de paiement — Toile',
        ]);
    }

    // Supprime une carte enregistrée
    public function deletePaymentMethod(): void
    {
        $paymentMethodId = $_POST['payment_method_id'] ?? '';

        if (empty($paymentMethodId)) {
            header('Location: /profile/payment-methods');
            exit;
        }

        $stripe = new StripeService();
        $stripe->detachPaymentMethod($paymentMethodId);

        header('Location: /profile/payment-methods');
        exit;
    }
}
