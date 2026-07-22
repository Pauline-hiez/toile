-- Nouvel état dans le cycle de vie d'une commande issue d'un devis :
-- l'artiste doit proposer un prix avant que le client puisse l'accepter
-- (et payer) ou le refuser.
ALTER TABLE orders MODIFY COLUMN status ENUM(
    'quote_requested',
    'price_proposed',
    'pending',
    'accepted',
    'rejected',
    'in_progress',
    'delivered',
    'completed',
    'cancelled'
) NOT NULL DEFAULT 'pending';
