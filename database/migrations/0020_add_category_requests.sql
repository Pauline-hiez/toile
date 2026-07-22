-- Demandes de nouveau style/type soumises par les artistes depuis leur
-- boutique (voir ShopController::submitCategoryRequest()), validées par
-- l'admin avant de rejoindre la liste sélectionnable Shop::STYLES/TYPES.
-- L'image n'est utilisée que pour les styles (tuile page d'accueil) ;
-- show_on_homepage est décidé par l'admin au moment de l'approbation.
CREATE TABLE category_request (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL,

    category_type ENUM('style', 'type') NOT NULL,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255) NULL,

    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    show_on_homepage TINYINT(1) NOT NULL DEFAULT 0,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_category_request_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
