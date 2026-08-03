<?php

namespace App\Controllers;

use App\Core\Renderer;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\RememberToken;
use App\Core\GoogleAuth;
use App\Core\RateLimiter;

class AuthController
{
    private const REMEMBER_COOKIE = 'remember_token';

    private Renderer $renderer;
    private User $userModel;
    private RememberToken $rememberTokenModel;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->userModel = new User();
        $this->rememberTokenModel = new RememberToken();
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

        // Rate limiting par IP : protège contre la création massive de
        // comptes (spam/bots), indépendamment de la validité des champs.
        $ipIdentifier = 'ip:' . RateLimiter::clientIp();

        if (RateLimiter::tooManyAttempts($ipIdentifier, 'register', 5, 60)) {
            http_response_code(429);
            $this->renderer->render('auth/register', [
                'errors' => ['general' => 'Trop de tentatives d\'inscription. Réessaie dans une heure.'],
                'old' => ['email' => $email, 'username' => $username],
            ]);
            return;
        }

        RateLimiter::hit($ipIdentifier, 'register');

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
            'avatar' => 'default.png',
        ]);

        // Envoi un email de bienvenue
        $html = \App\Core\Mailer::renderTemplate('welcome', [
            'username' => $username,
        ]);
        \App\Core\Mailer::send($email, 'Bienvenue sur Toile !', $html, 'welcome', [
            'email-illustration' => __DIR__ . '/../../public/assets/images/decor/emails.png',
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

        if (!\App\Core\PasswordPolicy::isValid($password)) {
            $errors['password'] = \App\Core\PasswordPolicy::REQUIREMENTS;
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

        // Deux compteurs en parallèle : par email ciblé (protège un compte
        // précis contre le bruteforce, même si l'attaquant change d'IP) et
        // par IP (protège contre la pulvérisation sur de nombreux comptes
        // depuis une seule source).
        $emailIdentifier = 'email:' . strtolower($email);
        $ipIdentifier = 'ip:' . RateLimiter::clientIp();

        if (RateLimiter::tooManyAttempts($emailIdentifier, 'login', 5, 15)
            || RateLimiter::tooManyAttempts($ipIdentifier, 'login', 20, 15)) {
            http_response_code(429);
            $this->renderer->render('auth/login', [
                'error' => 'Trop de tentatives de connexion. Réessaie dans quelques minutes.',
            ]);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            RateLimiter::hit($emailIdentifier, 'login');
            RateLimiter::hit($ipIdentifier, 'login');

            $this->renderer->render('auth/login', [
                'error' => 'Email ou mot de passe incorrect.',
            ]);
            return;
        }

        if ($user['is_banned']) {
            $this->renderer->render('auth/login', [
                'error' => 'Ce compte a été suspendu.',
            ]);
            return;
        }

        RateLimiter::clear($emailIdentifier, 'login');
        RateLimiter::clear($ipIdentifier, 'login');

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        if (isset($_POST['remember_me'])) {
            $this->rememberMe($user['id']);
        }

        header('Location: /');
        exit;
    }

    /**
     * Émet un jeton "se souvenir de moi" et le pose en cookie longue
     * durée (30 jours), pour reconnecter automatiquement l'utilisateur
     * tant que la session a expiré (voir bootstrap dans public/index.php).
     */
    private function rememberMe(int $userId): void
    {
        $token = $this->rememberTokenModel->issue($userId);

        setcookie(
            self::REMEMBER_COOKIE,
            $token,
            [
                'expires' => time() + 30 * 86400,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->rememberTokenModel->deleteByUserId($_SESSION['user_id']);
        }

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

        setcookie(self::REMEMBER_COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);

        header('Location: /');
        exit;
    }

    // Affiche le formulaire de demande de reset
    public function showForgotPassword(): void
    {
        $this->renderer->render('auth/forgot-password', [
            'success' => null,
            'error' => null,
            'pageTitle' => 'Mot de passe oublié - Toile',
        ]);
    }

    // Traite la demande de Reset
    public function forgotPassword(): void
    {
        $email = trim($_POST['email'] ?? '');

        // Par IP (volume global de demandes) et par email ciblé (protège
        // un compte précis contre le spam de liens de réinitialisation) —
        // enregistré avant toute recherche en base, qu'un compte existe ou
        // non, pour ne pas laisser deviner l'existence d'un email via une
        // différence de comportement du rate limiting.
        $ipIdentifier = 'ip:' . RateLimiter::clientIp();
        $emailIdentifier = 'email:' . strtolower($email);

        if (RateLimiter::tooManyAttempts($ipIdentifier, 'forgot_password', 10, 60)
            || RateLimiter::tooManyAttempts($emailIdentifier, 'forgot_password', 3, 60)) {
            http_response_code(429);
            $this->renderer->render('auth/forgot-password', [
                'success' => null,
                'error' => 'Trop de demandes. Réessaie dans quelques minutes.',
                'pageTitle' => 'Mot de passe oublié - Toile',
            ]);
            return;
        }

        RateLimiter::hit($ipIdentifier, 'forgot_password');
        RateLimiter::hit($emailIdentifier, 'forgot_password');

        $user = $this->userModel->findByEmail($email);

        $successMessage = 'Si un compte existe avec cet email, tu recevras un lien de réinitialisation.';

        if ($user !== null) {
            $resetModel = new PasswordReset();
            $token = $resetModel->createToken($user['id']);
            $resetLink = ($_ENV['APP_URL'] ?? 'http://toile.test') . '/reset-password?token=' . $token;

            $html = \App\Core\Mailer::renderTemplate('reset-password', [
                'username' => $user['username'],
                'resetLink' => $resetLink,
            ]);

            \App\Core\Mailer::send(
                $user['email'],
                'Réintialisation de ton mot de passe',
                $html,
                'reset-password',
                ['email-illustration' => __DIR__ . '/../../public/assets/images/decor/trousse.png']
            );
        }

        $this->renderer->render('auth/forgot-password', [
            'success' => $successMessage,
            'error' => null,
            'pageTitle' => 'Mot de passe oublié - Toile',
        ]);
    }

    // Affiche le formulaire de nouveau mot de passe
    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        $resetModel = new PasswordReset();
        $resetEntry = $resetModel->findValidToken($token);

        if ($resetEntry === null) {
            $this->renderer->render('auth/reset-password', [
                'token' => null,
                'error' => 'Ce lien est invalide ou expiré.',
                'pageTitle' => 'Réinitialisation - Toile',
            ]);
            return;
        }

        $this->renderer->render('auth/reset-password', [
            'token' => $token,
            'error' => null,
            'pageTitle' => 'Réinitialisation - Toile',
        ]);
    }

    // Traite le nouveau mot de passe
    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $resetModel = new PasswordReset();
        $resetEntry = $resetModel->findValidToken($token);

        if ($resetEntry === null) {
            $this->renderer->render('auth/reset-password', [
                'token' => null,
                'error' => 'Ce lien est invalide ou expliré.',
                'pageTitle' => 'Réinitialisation - Toile',
            ]);
            return;
        }

        if (!\App\Core\PasswordPolicy::isValid($password)) {
            $this->renderer->render('auth/reset-password', [
                'token' => $token,
                'error' => \App\Core\PasswordPolicy::REQUIREMENTS,
                'pageTitle' => 'Réinitialisation - Toile',
            ]);
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->renderer->render('auth/reset-password', [
                'token' => $token,
                'error' => 'Les deux mots de passe ne correspondent pas.',
                'pageTitle' => 'Réinitialisation - Toile',
            ]);
            return;
        }
        $this->userModel->update($resetEntry['user_id'], [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        $resetModel->markAsUsed($resetEntry['id']);

        header('Location: /login?reset=1');
        exit;
    }


    // Redirige vers Google pour l'authentification

    public function redirectToGoogle(): void
    {
        // Connexion Google indisponible en démonstration (OAuth sortant bloqué).
        if (\App\Core\Demo::isEnabled()) {
            header('Location: /login?error=google_failed');
            exit;
        }

        $googleAuth = new GoogleAuth();
        header('Location: ' . $googleAuth->getAuthUrl());
        exit;
    }

    // Traite le retour Google (callback)
    public function handleGoogleCallback(): void
    {
        if (\App\Core\Demo::isEnabled()) {
            header('Location: /login?error=google_failed');
            exit;
        }

        $code = $_GET['code'] ?? null;

        if ($code === null) {
            header('Location: /login?error=google_failed');
            exit;
        }

        $googleAuth = new GoogleAuth();
        $userInfo = $googleAuth->getUserInfo($code);

        if ($userInfo === null) {
            header('Location: /login?error=google_failed');
            exit;
        }

        // Cherche si un compte existe déjà avec cet email
        $existingUser = $this->userModel->findByEmail($userInfo['email']);

        if ($existingUser !== null) {
            // Le compte existe — on connecte directement
            if ($existingUser['provider'] === 'credentials') {
                $this->userModel->update($existingUser['id'], [
                    'provider_id' => $userInfo['id'],
                ]);
            }

            if ($existingUser['is_banned']) {
                header('Location: /login?banned=1');
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $existingUser['id'];
            $_SESSION['user_role'] = $existingUser['role'];
        } else {
            // Nouveau compte — on le crée automatiquement
            $userId = $this->userModel->create([
                'email' => $userInfo['email'],
                'username' => $userInfo['name'],
                'provider' => 'google',
                'provider_id' => $userInfo['id'],
                'email_verified_at' => date('Y-m-d H:i:s'),
                // L'email Google est déjà vérifié par Google.
                'avatar' => 'default.png',
            ]);

            // Email de bienvenue
            $html = \App\Core\Mailer::renderTemplate('welcome', [
                'username' => $userInfo['name'],
            ]);
            \App\Core\Mailer::send($userInfo['email'], 'Bienvenue sur Toile !', $html, 'welcome', [
                'email-illustration' => __DIR__ . '/../../public/assets/images/decor/emails.png',
            ]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = 'user';
        }

        header('Location: /');
        exit;
    }
}
