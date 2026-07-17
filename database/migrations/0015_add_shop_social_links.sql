-- Liens vers les réseaux sociaux de l'artiste, affichés sur la page
-- boutique publique (sous le bouton "Ajouter aux favoris").
ALTER TABLE shop
    ADD COLUMN social_instagram VARCHAR(255) NULL AFTER bio,
    ADD COLUMN social_facebook VARCHAR(255) NULL AFTER social_instagram,
    ADD COLUMN social_pinterest VARCHAR(255) NULL AFTER social_facebook,
    ADD COLUMN social_tiktok VARCHAR(255) NULL AFTER social_pinterest;
