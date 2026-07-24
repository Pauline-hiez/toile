-- Mise aux normes des factures : adresse de facturation client figée sur la
-- commande, numérotation séquentielle persistée (deux séries continues :
-- CMD pour les commandes, ABO pour les abonnements), et compteur atomique.

-- 1) Adresse de facturation du client (figée à la commande) + numéro de
--    facture attribué à la capture du paiement (voir OrderController::transition).
ALTER TABLE orders
    ADD COLUMN billing_name VARCHAR(150) NULL AFTER shipping_country,
    ADD COLUMN billing_address_line1 VARCHAR(255) NULL AFTER billing_name,
    ADD COLUMN billing_address_line2 VARCHAR(255) NULL AFTER billing_address_line1,
    ADD COLUMN billing_city VARCHAR(100) NULL AFTER billing_address_line2,
    ADD COLUMN billing_postal_code VARCHAR(20) NULL AFTER billing_city,
    ADD COLUMN billing_country VARCHAR(100) NULL AFTER billing_postal_code,
    ADD COLUMN invoice_number VARCHAR(30) NULL UNIQUE AFTER billing_country,
    ADD COLUMN invoiced_at DATETIME NULL AFTER invoice_number;

-- 2) Numéro de facture pour les abonnements (attribué au paiement Stripe).
ALTER TABLE subscription_invoice
    ADD COLUMN invoice_number VARCHAR(30) NULL UNIQUE AFTER id;

-- 3) Compteur de numérotation, une ligne par (série, année). Incrémenté de
--    façon atomique par App\Core\InvoiceNumber (INSERT ... ON DUPLICATE KEY
--    UPDATE avec LAST_INSERT_ID) pour garantir l'unicité et l'absence de trou
--    même en cas d'accès concurrents (webhook + capture simultanés).
CREATE TABLE invoice_counter (
    series VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    last_number INT NOT NULL DEFAULT 0,

    PRIMARY KEY (series, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
