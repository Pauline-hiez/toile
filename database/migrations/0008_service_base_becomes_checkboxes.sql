-- Les éléments de base ne sont finalement pas des variantes à choix
-- unique avec leur propre prix, mais des cases à cocher purement
-- descriptives (style, éléments, matériaux...) que le client peut
-- cocher plusieurs à la fois — comme un cahier des charges, sans impact
-- sur le prix. On retire donc le prix et la relation 1-1 avec la
-- commande, remplacée par une relation many-to-many.

ALTER TABLE service_base
    DROP COLUMN price;

ALTER TABLE orders
    DROP FOREIGN KEY fk_order_service_base,
    DROP COLUMN service_base_id;

-- Snapshot du libellé au moment de la commande : si l'artiste modifie ou
-- supprime la case à cocher ensuite, l'historique de la commande reste
-- lisible (service_base_id passe à NULL mais label est conservé).
CREATE TABLE order_service_base (
    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,
    service_base_id INT NULL,
    label VARCHAR(150) NOT NULL,

    CONSTRAINT fk_order_service_base_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_service_base_base
        FOREIGN KEY (service_base_id) REFERENCES service_base(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
