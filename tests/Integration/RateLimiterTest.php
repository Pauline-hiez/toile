<?php

namespace Tests\Integration;

use App\Core\RateLimiter;
use Tests\Support\IntegrationTestCase;

class RateLimiterTest extends IntegrationTestCase
{
    public function testAllowsAttemptsBelowThreshold(): void
    {
        $identifier = 'email:test-below@example.test';

        for ($i = 0; $i < 4; $i++) {
            $this->assertFalse(RateLimiter::tooManyAttempts($identifier, 'login', 5, 15));
            RateLimiter::hit($identifier, 'login');
        }

        // 4 tentatives enregistrées, seuil à 5 : toujours autorisé.
        $this->assertFalse(RateLimiter::tooManyAttempts($identifier, 'login', 5, 15));
    }

    public function testBlocksOnceThresholdReached(): void
    {
        $identifier = 'email:test-blocked@example.test';

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($identifier, 'login');
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($identifier, 'login', 5, 15));
    }

    public function testClearResetsTheCounter(): void
    {
        $identifier = 'email:test-clear@example.test';

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($identifier, 'login');
        }
        $this->assertTrue(RateLimiter::tooManyAttempts($identifier, 'login', 5, 15));

        RateLimiter::clear($identifier, 'login');

        $this->assertFalse(RateLimiter::tooManyAttempts($identifier, 'login', 5, 15));
    }

    public function testDifferentActionsAreIndependent(): void
    {
        $identifier = 'ip:203.0.113.5';

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($identifier, 'login');
        }

        // Même identifiant, action différente ('register') : pas concerné
        // par les tentatives de connexion.
        $this->assertTrue(RateLimiter::tooManyAttempts($identifier, 'login', 5, 15));
        $this->assertFalse(RateLimiter::tooManyAttempts($identifier, 'register', 5, 15));
    }

    public function testDifferentIdentifiersAreIndependent(): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('email:victim@example.test', 'login');
        }

        // Un attaquant qui cible un autre compte ne doit pas être
        // bloqué par les tentatives sur le compte "victim".
        $this->assertFalse(RateLimiter::tooManyAttempts('email:attacker@example.test', 'login', 5, 15));
    }

    public function testAttemptsOutsideTheTimeWindowDoNotCount(): void
    {
        $identifier = 'email:test-old-attempts@example.test';

        // Simule 5 tentatives vieilles de 30 minutes (hors fenêtre de 15
        // minutes) — insérées directement en base, RateLimiter::hit()
        // n'ayant pas de moyen d'antidater une tentative.
        $stmt = $this->pdo->prepare(
            "INSERT INTO rate_limit_attempt (identifier, action, created_at)
             VALUES (:identifier, 'login', DATE_SUB(NOW(), INTERVAL 30 MINUTE))"
        );
        for ($i = 0; $i < 5; $i++) {
            $stmt->execute(['identifier' => $identifier]);
        }

        $this->assertFalse(RateLimiter::tooManyAttempts($identifier, 'login', 5, 15));
    }
}
