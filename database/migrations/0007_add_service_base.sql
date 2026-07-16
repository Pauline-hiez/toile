-- Éléments de base d'une prestation : variantes à choix unique côté client
-- (ex. "Buste" / "Corps entier"), en plus des options additives existantes
-- (service_option). Miroir exact de service_option, prix en centimes.
CREATE TABLE service_base (
    id INT AUTO_INCREMENT PRIMARY KEY,

    service_id INT NOT NULL,

    label VARCHAR(150) NOT NULL,
    price INT NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_service_base_service
        FOREIGN KEY (service_id) REFERENCES service(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trace l'élément de base choisi par le client sur sa commande, même
-- pattern que orders.service_id (nullable, SET NULL si l'élément est
-- supprimé ensuite — la commande garde son historique via title/description).
ALTER TABLE orders
    ADD COLUMN service_base_id INT NULL AFTER service_id,
    ADD CONSTRAINT fk_order_service_base
        FOREIGN KEY (service_base_id) REFERENCES service_base(id)
        ON DELETE SET NULL;
