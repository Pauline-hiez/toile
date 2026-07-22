-- Permet à l'artiste d'accepter ou non les demandes de devis (commande
-- sans paiement immédiat, prix à négocier) sur sa boutique.
ALTER TABLE shop
    ADD COLUMN accepts_quotes BOOLEAN NOT NULL DEFAULT TRUE AFTER is_open;
