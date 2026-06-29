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
}
