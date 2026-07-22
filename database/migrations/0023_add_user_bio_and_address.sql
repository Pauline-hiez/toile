-- Bio + adresse par défaut, affichées/éditables sur la page profil
-- publique (voir UserController::publicProfile()/updateProfile()).
ALTER TABLE users
    ADD COLUMN bio TEXT NULL AFTER avatar,
    ADD COLUMN address_line1 VARCHAR(255) NULL AFTER bio,
    ADD COLUMN address_line2 VARCHAR(255) NULL AFTER address_line1,
    ADD COLUMN city VARCHAR(100) NULL AFTER address_line2,
    ADD COLUMN postal_code VARCHAR(20) NULL AFTER city,
    ADD COLUMN country VARCHAR(100) NULL AFTER postal_code;
