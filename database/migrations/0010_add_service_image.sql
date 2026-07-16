-- Image représentant la prestation, affichée sur la page boutique
-- publique et dans la gestion des prestations côté artiste.
ALTER TABLE service
    ADD COLUMN image VARCHAR(255) NULL AFTER description;
