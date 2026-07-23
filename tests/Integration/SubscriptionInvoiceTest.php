<?php

namespace Tests\Integration;

use App\Models\Shop;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Tests\Support\IntegrationTestCase;

class SubscriptionInvoiceTest extends IntegrationTestCase
{
    private SubscriptionInvoice $invoiceModel;
    private int $shopId;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceModel = new SubscriptionInvoice();

        $userModel = new User();
        $this->userId = $userModel->create([
            'email' => 'invoice-test-' . uniqid() . '@example.test',
            'username' => 'InvoiceTestArtist',
            'password_hash' => password_hash('irrelevant', PASSWORD_BCRYPT),
            'provider' => 'credentials',
            'avatar' => 'default.png',
            'role' => 'artist',
        ]);

        $shopModel = new Shop();
        $this->shopId = $shopModel->create([
            'user_id' => $this->userId,
            'name' => 'Boutique de test facture',
            'slug' => 'boutique-test-facture-' . uniqid(),
        ]);
    }

    public function testCreateAndFindByShopId(): void
    {
        $this->invoiceModel->create([
            'shop_id' => $this->shopId,
            'plan_name' => 'Pro',
            'amount' => 2990,
            'stripe_invoice_id' => 'in_test_' . uniqid(),
            'period_start' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'period_end' => date('Y-m-d H:i:s'),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        $invoices = $this->invoiceModel->findByShopId($this->shopId);

        $this->assertCount(1, $invoices);
        $this->assertSame('Pro', $invoices[0]['plan_name']);
        $this->assertSame(2990, (int) $invoices[0]['amount']);
    }

    public function testFindByShopIdOrdersMostRecentFirst(): void
    {
        $this->invoiceModel->create([
            'shop_id' => $this->shopId,
            'plan_name' => 'Essentiel',
            'amount' => 1490,
            'stripe_invoice_id' => 'in_test_old_' . uniqid(),
            'period_start' => date('Y-m-d H:i:s', strtotime('-60 days')),
            'period_end' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'paid_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
        ]);
        $this->invoiceModel->create([
            'shop_id' => $this->shopId,
            'plan_name' => 'Pro',
            'amount' => 2990,
            'stripe_invoice_id' => 'in_test_new_' . uniqid(),
            'period_start' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'period_end' => date('Y-m-d H:i:s'),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        $invoices = $this->invoiceModel->findByShopId($this->shopId);

        $this->assertCount(2, $invoices);
        $this->assertSame('Pro', $invoices[0]['plan_name'], 'La facture la plus récente doit apparaître en premier.');
        $this->assertSame('Essentiel', $invoices[1]['plan_name']);
    }

    public function testFindByStripeInvoiceIdDetectsDuplicate(): void
    {
        $stripeId = 'in_test_dedup_' . uniqid();

        $this->assertNull($this->invoiceModel->findByStripeInvoiceId($stripeId));

        $this->invoiceModel->create([
            'shop_id' => $this->shopId,
            'plan_name' => 'Pro',
            'amount' => 2990,
            'stripe_invoice_id' => $stripeId,
            'period_start' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'period_end' => date('Y-m-d H:i:s'),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        // C'est cette vérification qui protège StripeWebhookController
        // contre un doublon si Stripe renvoie deux fois le même événement.
        $this->assertNotNull($this->invoiceModel->findByStripeInvoiceId($stripeId));
    }

    public function testFindByIdWithShopJoinsShopAndOwnerInfo(): void
    {
        $id = $this->invoiceModel->create([
            'shop_id' => $this->shopId,
            'plan_name' => 'Pro',
            'amount' => 2990,
            'stripe_invoice_id' => 'in_test_' . uniqid(),
            'period_start' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'period_end' => date('Y-m-d H:i:s'),
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        $invoice = $this->invoiceModel->findByIdWithShop($id);

        $this->assertNotNull($invoice);
        $this->assertSame('Boutique de test facture', $invoice['shop_name']);
        $this->assertSame($this->userId, (int) $invoice['shop_owner_id']);
        $this->assertSame('InvoiceTestArtist', $invoice['owner_username']);
    }

    public function testFindByIdWithShopReturnsNullForUnknownId(): void
    {
        $this->assertNull($this->invoiceModel->findByIdWithShop(999999));
    }
}
