-- Position d'affichage des images de portfolio, pour permettre à
-- l'artiste de les réordonner par glisser-déposer.
ALTER TABLE portfolio_image
    ADD COLUMN position INT NOT NULL DEFAULT 0 AFTER filename;

UPDATE portfolio_image pi
INNER JOIN (
    SELECT id, ROW_NUMBER() OVER (PARTITION BY shop_id ORDER BY created_at, id) AS rn
    FROM portfolio_image
) ranked ON ranked.id = pi.id
SET pi.position = ranked.rn;
