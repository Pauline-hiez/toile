<?php

namespace App\Controllers;

use App\Core\Renderer;

class HomeController
{
    private Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    // Page d'accueil
    public function index(): void
    {
        $this->renderer->render('home', [
            'title' => 'Toile',
        ]);
    }

    public function testDb(): void
    {
        try {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            echo 'Connexion à la base de données réussie ✅';
        } catch (\Throwable $e) {
            echo 'Erreur de connexion ❌ : ' . htmlspecialchars($e->getMessage());
        }
    }
}
