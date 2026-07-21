<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\Renderer;

class ContactController
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    // Page de contact complète (dégradation sans JS de la modale globale)
    public function index(): void
    {
        $this->renderer->render('contact', [
            'pageTitle' => 'Contact — Toile',
        ]);
    }

    public function submit(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // N'accepte de rediriger que vers un chemin interne relatif,
        // pour éviter une redirection ouverte vers un domaine externe.
        $redirect = $_POST['redirect'] ?? '/';
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/';
        }
        $redirect = strtok($redirect, '?');
        $separator = '?';

        if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . $redirect . $separator . 'contact=error');
            exit;
        }

        $html = Mailer::renderTemplate('contact-message', [
            'name' => $name,
            'email' => $email,
            'subject' => $subject !== '' ? $subject : '(Aucun sujet)',
            'message' => $message,
        ]);

        Mailer::send(
            $_ENV['MAIL_FROM'],
            'Nouveau message de contact — ' . ($subject !== '' ? $subject : 'Toile'),
            $html,
            'contact'
        );

        header('Location: ' . $redirect . $separator . 'contact=sent');
        exit;
    }
}
