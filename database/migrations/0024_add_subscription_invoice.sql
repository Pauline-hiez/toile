-- Historique des paiements d'abonnement, alimenté par
-- StripeWebhookController::handlePaymentSucceeded() (événement Stripe
-- invoice.payment_succeeded) — permet de générer une facture PDF par
-- paiement, y compris après un changement/suppression de plan (plan_name
-- est un instantané, pas une clé étrangère vers subscription_plan).
CREATE TABLE subscription_invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    amount INT NOT NULL,

    stripe_invoice_id VARCHAR(255) NOT NULL UNIQUE,

    period_start DATETIME NOT NULL,
    period_end DATETIME NOT NULL,
    paid_at DATETIME NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_subscription_invoice_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
