<?php

namespace Tests\Support;

use App\Core\Database;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base pour les tests qui touchent la vraie base (toile_test, voir
 * tests/bootstrap.php) — chaque test est enveloppé dans une transaction
 * annulée à la fin, pour rester isolé sans avoir à nettoyer les tables à
 * la main ni risquer de laisser des résidus entre deux tests.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        parent::tearDown();
    }
}
