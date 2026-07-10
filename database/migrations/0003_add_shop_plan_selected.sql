-- Une boutique ne s'ouvre aux commandes que lorsque l'artiste a choisi
-- son abonnement (gratuit "Commission" ou payant), pas automatiquement
-- à la création. Ce flag distingue "n'a jamais choisi de formule"
-- (boutique forcée fermée) de "a choisi une formule, actuellement
-- fermée volontairement" (le toggle ouvrir/fermer existant).
ALTER TABLE shop
    ADD COLUMN plan_selected BOOLEAN NOT NULL DEFAULT FALSE AFTER is_open;

-- Les boutiques existantes qui ont déjà une ligne shop_subscription
-- (donc déjà passées par un choix de formule, palier gratuit inclus
-- depuis la migration 0002) sont considérées comme ayant choisi.
UPDATE shop
INNER JOIN shop_subscription ON shop_subscription.shop_id = shop.id
SET shop.plan_selected = TRUE;
