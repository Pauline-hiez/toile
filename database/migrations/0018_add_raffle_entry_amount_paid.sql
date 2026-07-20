-- Montant réellement payé pour ce ticket (centimes), figé au moment de
-- l'achat — le prix des réglages (raffle_price/raffle_homepage_price)
-- peut changer après coup, ce montant reste celui effectivement prélevé.
-- NULL pour les tickets vendus avant cette colonne (historique non
-- rétroactif, voir page Statistiques de l'admin).
ALTER TABLE raffle_entry
    ADD COLUMN amount_paid INT NULL AFTER stripe_payment_intent_id;
