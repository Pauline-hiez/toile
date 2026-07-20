-- Compte Stripe Connect (Express) de l'artiste, pour reverser sa part
-- automatiquement via des paiements à destination — voir
-- StripeService::createConnectedAccount()/createPaymentIntent().
ALTER TABLE shop
    ADD COLUMN stripe_account_id VARCHAR(255) NULL AFTER monetization_type,
    ADD COLUMN stripe_payouts_enabled BOOLEAN NOT NULL DEFAULT FALSE AFTER stripe_account_id;

-- Adresse de livraison, facultative (une création numérique n'en a pas
-- besoin) — renseignée par le client à la commande.
ALTER TABLE orders
    ADD COLUMN shipping_name VARCHAR(150) NULL AFTER delivery_file,
    ADD COLUMN shipping_address_line1 VARCHAR(255) NULL AFTER shipping_name,
    ADD COLUMN shipping_address_line2 VARCHAR(255) NULL AFTER shipping_address_line1,
    ADD COLUMN shipping_city VARCHAR(100) NULL AFTER shipping_address_line2,
    ADD COLUMN shipping_postal_code VARCHAR(20) NULL AFTER shipping_city,
    ADD COLUMN shipping_country VARCHAR(100) NULL AFTER shipping_postal_code;
