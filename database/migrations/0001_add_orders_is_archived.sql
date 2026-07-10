-- Ajoute la possibilité d'archiver une commande depuis l'admin,
-- au lieu de la supprimer (les commandes sont des enregistrements
-- financiers liés à Stripe, on ne les supprime jamais).
ALTER TABLE orders
    ADD COLUMN is_archived BOOLEAN NOT NULL DEFAULT FALSE AFTER delivery_file;
