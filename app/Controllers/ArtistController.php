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
        ]);
    }

    public function submitRequest(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);

        if ($user['role'] === 'artist' || $user['artist_request_status'] === 'pending') {
            header('Location: /become-artist');
            exit;
        }

        $this->userModel->setArtistRequestStatus($user['id'], 'pending');

        $this->renderer->render('artist/become', [
            'user' => $this->userModel->findById($user['id']),
            'success' => 'Ta demande a bien été envoyée ! Elle est en attente de validation.',
        ]);
    }
}
