-- Légende/badge facultatif affiché sur une image de portfolio.
ALTER TABLE portfolio_image
    ADD COLUMN label VARCHAR(100) NULL AFTER position;
