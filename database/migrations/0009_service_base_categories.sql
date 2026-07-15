-- Les éléments de base se regroupent par catégorie librement nommée par
-- l'artiste (ex. "Format", "Style", "Matériaux"), le client choisit un
-- seul choix par catégorie (comportement radio par groupe). Toujours
-- purement descriptif, sans prix.

ALTER TABLE service_base
    ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT '' AFTER service_id;

ALTER TABLE order_service_base
    ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT '' AFTER service_base_id;
