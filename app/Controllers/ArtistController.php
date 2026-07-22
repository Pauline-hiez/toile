<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\User;

class ArtistController
{
    private Renderer $renderer;
    private User $userModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->userModel = new User();
    }

    public function showRequest(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);

        $this->renderer->render('artist/become', [
            'user' => $user,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function submitRequest(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);

        if ($user['role'] === 'artist' || $user['artist_request_status'] === 'pending') {
            header('Location: /become-artist');
            exit;
        }

        $old = [
            'artist_display_name' => trim($_POST['artist_display_name'] ?? ''),
            'requested_shop_name' => trim($_POST['requested_shop_name'] ?? ''),
            'artist_contact_email' => trim($_POST['artist_contact_email'] ?? ''),
            'artist_presentation' => trim($_POST['artist_presentation'] ?? ''),
            'artist_motivation' => trim($_POST['artist_motivation'] ?? ''),
        ];

        $errors = [];

        if (mb_strlen($old['artist_display_name']) < 3) {
            $errors['artist_display_name'] = 'Le nom d\'artiste doit faire au moins 3 caractères.';
        }

        if (mb_strlen($old['requested_shop_name']) < 3) {
            $errors['requested_shop_name'] = 'Le nom de la boutique doit faire au moins 3 caractères.';
        }

        if (!filter_var($old['artist_contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['artist_contact_email'] = 'Adresse email invalide.';
        }

        if (mb_strlen($old['artist_presentation']) < 20) {
            $errors['artist_presentation'] = 'Présente-toi en quelques phrases (20 caractères minimum).';
        }

        if (mb_strlen($old['artist_motivation']) < 20) {
            $errors['artist_motivation'] = 'Explique un peu plus ta motivation (20 caractères minimum).';
        }

        if (!isset($_POST['terms'])) {
            $errors['terms'] = 'Tu dois accepter le règlement intérieur du site pour continuer.';
        }

        if (!empty($errors)) {
            $this->renderer->render('artist/become', [
                'user' => $user,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $this->userModel->submitArtistRequest($user['id'], $old);

        $this->renderer->render('artist/become', [
            'user' => $this->userModel->findById($user['id']),
            'errors' => [],
            'old' => [],
            'success' => 'Ta demande a bien été envoyée ! Elle est en attente de validation.',
        ]);
    }
}
