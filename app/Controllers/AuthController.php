<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\User;

class AuthController
{
    private Renderer $renderer;
    private User $userModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->userModel = new User();
    }

    /**
     * Affiche le formulaire d'inscription (GET /register).
     */
    public function showRegister(): void
    {
        $this->renderer->render('auth/register', [
            'errors' => [],
            'old' => [],
        ]);
    }

    /**
     * Traite la soumission du formulaire d'inscription (POST /register).
     */
    public function register(): void
    {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = $this->validate($email, $username, $password, $passwordConfirm);

        if (!empty($errors)) {
            $this->renderer->render('auth/register', [
                'errors' => $errors,
                'old' => ['email' => $email, 'username' => $username],
            ]);
            return;
        }

        $this->userModel->create([
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'provider' => 'credentials',
        ]);

        header('Location: /login');
        exit;
    }

    private function validate(string $email, string $username, string $password, string $passwordConfirm): array
    {
        $errors = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        } elseif ($this->userModel->findByEmail($email) !== null) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (mb_strlen($username) < 3) {
            $errors['username'] = 'Le nom d\'utilisateur doit faire au moins 3 caractères.';
        }

        if (mb_strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit faire au moins 8 caractères.';
        } elseif ($password !== $passwordConfirm) {
            $errors['password'] = 'Les mots de passe ne correspondent pas.';
        }

        return $errors;
    }

    public function showLogin(): void
    {
        $this->renderer->render('auth/login', [
            'error' => null,
        ]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $this->renderer->render('auth/login', [
                'error' => 'Email ou mot de passe incorrect.',
            ]);
        }

        if ($user['is_banned']) {
            $this->renderer->render('auth/login', [
                'error' => 'Ce compte a été suspendu.',
            ]);
            return;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use.cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: /');
        exit;
    }
}
