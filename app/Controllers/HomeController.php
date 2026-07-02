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

    public function testStripe(): void
    {
        try {
            $stripe = new \App\Core\StripeService();
            $result = $stripe->createPaymentIntent(100, 'eur', ['test' => 'ok']);
            echo 'Stripe OK ✅ — PaymentIntent créé : ' . $result['payment_intent_id'];
        } catch (\Exception $e) {
            echo 'Stripe ERROR ❌ : ' . htmlspecialchars($e->getMessage());
        }
    }
}
