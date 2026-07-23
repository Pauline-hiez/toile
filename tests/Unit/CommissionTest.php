<?php

namespace Tests\Unit;

use App\Core\Commission;
use PHPUnit\Framework\TestCase;

class CommissionTest extends TestCase
{
    public function testCalculatesSimplePercentage(): void
    {
        // 100,00 € à 10% → 10,00 €
        $this->assertSame(1000, Commission::calculateAmount(10000, 10.0));
    }

    public function testZeroRateGivesZeroCommission(): void
    {
        // Palier "Commission" gratuit sans abonnement payant à 0% ne
        // doit jamais faire perdre d'argent à l'artiste par erreur.
        $this->assertSame(0, Commission::calculateAmount(10000, 0.0));
    }

    public function testZeroPriceGivesZeroCommission(): void
    {
        $this->assertSame(0, Commission::calculateAmount(0, 10.0));
    }

    public function testRoundsToNearestCent(): void
    {
        // 9,99 € à 10% = 0,999 € → arrondi à 1,00 €.
        $this->assertSame(100, Commission::calculateAmount(999, 10.0));
    }

    public function testRoundsHalfAwayFromZero(): void
    {
        // 1,00 € à 12,5% = 0,125 € (12,5 centimes) → arrondi à 13 centimes.
        $this->assertSame(13, Commission::calculateAmount(100, 12.5));
    }

    public function testHandlesDecimalRateFromDatabase(): void
    {
        // subscription_plan.commission_rate est un DECIMAL(5,2) — vérifie
        // qu'un taux non entier (palier "Essentiel", 5.00%) est correct.
        $this->assertSame(750, Commission::calculateAmount(15000, 5.0));
    }
}
