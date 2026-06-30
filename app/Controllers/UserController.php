<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\User;

class UserController
{
    private Renderer $renderer;
    private User $userModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->userModel = new User();
    }

    public function showProfile(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);

        $this->renderer->render('user/profile', [
            'user' => $user,
            'errors' => [],
            'success' => null,
        ]);
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
            $$uploadResult = \App\Core\FileUploader::upload(
                $_FILES['avatar'],
                __DIR__ . '/../../public/uploads/avatars'
            );

            if ($uploadResult['error'] !== null) {
                $errors['avatar'] = $uploadResult['error'];
            } else {
                $avatarFilename = $uploadResult['filename'];
            }
        }

        if (!empty($errors)) {
            $this->renderer->render('user/profile', [
                'user' => $user,
                'errors' => $errors,
                'success' => null,
            ]);
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
        ]);
    }

    public function updatePassword(): void
    {
        $user = $this->userModel->findById($_SESSION['user_id']);

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';

        $errors = [];

        if (mb_strlen($newPassword) < 8) {
            $errors['new_password'] = 'Le nouveau mot de passe doit faire au moins 8 caractères.';
        } elseif ($newPassword !== $newPasswordConfirm) {
            $errors['new_password'] = 'Les mots de passe ne correspondent pas.';
        }

        if (!empty($errors)) {
            $this->renderer->render('user/profile', [
                'user' => $user,
                'errors' => $errors,
                'success' => null,
            ]);
            return;
        }

        $this->userModel->update($user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);

        $this->renderer->render('user/profile', [
            'user' => $user,
            'errors' => [],
            'success' => 'Mot de passe modifié avec succès.',
        ]);
    }

    private function handleAvatarUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2Mo

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            return ['filename' => null, 'error' => 'Format de fichier non autorisé (jpeg, png, webp uniquement).'];
        }

        if ($file['size'] > $maxSize) {
            return ['filename' => null, 'error' => 'Le fichier dépasse la taille maximale (2Mo)'];
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $destination = __DIR__ . '/../../public/uploads/avatars/' . $filename;

        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['filename' => null, 'error' => 'Erreur lors de l\'enregistrement du fichier.'];
        }

        return ['filename' => $filename, 'error' => null];
    }
}
