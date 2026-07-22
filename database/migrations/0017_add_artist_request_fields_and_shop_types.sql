-- Détails de candidature artiste, collectés depuis /become-artist et
-- affichés à l'admin pour la revue (voir AdminController::artistRequests()).
-- Une seule candidature "vivante" par utilisateur : une resoumission
-- après refus écrase simplement les anciennes valeurs.
ALTER TABLE users
    ADD COLUMN artist_display_name VARCHAR(150) NULL AFTER artist_request_status,
    ADD COLUMN requested_shop_name VARCHAR(150) NULL AFTER artist_display_name,
    ADD COLUMN artist_contact_email VARCHAR(180) NULL AFTER requested_shop_name,
    ADD COLUMN artist_presentation TEXT NULL AFTER artist_contact_email,
    ADD COLUMN artist_motivation TEXT NULL AFTER artist_presentation,
    ADD COLUMN artist_terms_accepted_at DATETIME NULL AFTER artist_motivation;

-- Spécialité(s) de l'artiste (illustrateur, portraitiste...), au même
-- format que shop.styles (JSON, multi-sélection).
ALTER TABLE shop
    ADD COLUMN types JSON NULL AFTER styles;
