-- Le signalement cible la boutique dans son ensemble, pas une image de
-- portfolio isolée. On remplace 'portfolio_image' par 'shop' dans le
-- type de contenu signalable, et on élargit les raisons possibles
-- (plusieurs types de signalements, pas seulement plagiat/commentaire).
ALTER TABLE report
    MODIFY COLUMN reportable_type ENUM('shop', 'review') NOT NULL,
    MODIFY COLUMN reason ENUM(
        'plagiat',
        'contenu_inapproprie',
        'arnaque',
        'commentaire_inapproprie',
        'spam',
        'autre'
    ) NOT NULL DEFAULT 'autre';
