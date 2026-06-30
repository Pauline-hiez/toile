-- =========================================================
-- Schéma de la base de données "toile"
-- Ce fichier regroupe toutes les tables du projet.
-- Il s'enrichit au fil des issues/branches du projet.
-- =========================================================

-- -----------------------------------------------------
-- Table : users
-- (nommée au pluriel : "user" est un mot réservé MySQL,
-- utilisé par la fonction native USER())
-- Stocke les comptes (clients, artistes, admins), avec
-- support de l'authentification classique (mot de passe)
-- et OAuth (Google / Pinterest).
-- -----------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(180) NOT NULL UNIQUE,
    username VARCHAR(80) NOT NULL,

    password_hash VARCHAR(255) NULL,

    provider ENUM('credentials', 'google', 'pinterest') NOT NULL DEFAULT 'credentials',
    provider_id VARCHAR(255) NULL,

    email_verified_at DATETIME NULL,

    avatar VARCHAR(255) NULL,

    role ENUM('user', 'artist', 'admin') NOT NULL DEFAULT 'user',

    is_banned BOOLEAN NOT NULL DEFAULT FALSE,

    artist_request_status ENUM('pending', 'approved', 'rejected') NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : shop
-- Chaque boutique appartient à un seul utilisateur
-- (qui doit avoir le rôle artist).
-- -----------------------------------------------------
CREATE TABLE shop (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL UNIQUE,

    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,

    bio TEXT NULL,
    banner VARCHAR(255) NULL,

    -- Liste des styles artistiques, stockée en JSON.
    -- Exemple : ["anime", "réaliste", "chibi"]
    styles JSON NULL,

    is_open BOOLEAN NOT NULL DEFAULT FALSE,

    monetization_type ENUM('subscription', 'commission') NOT NULL DEFAULT 'commission',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_shop_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : service
-- Une prestation proposée par une boutique.
-- -----------------------------------------------------
CREATE TABLE service (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL,

    title VARCHAR(150) NOT NULL,
    description TEXT NULL,

    -- Prix de base en centimes (ex: 3500 = 35,00 €).
    -- On évite les nombres décimaux pour les montants d'argent :
    -- les flottants (FLOAT/DOUBLE) peuvent introduire des erreurs
    -- d'arrondi imperceptibles mais réelles sur des calculs financiers.
    base_price INT NOT NULL,

    delivery_days INT NOT NULL,

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_service_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : service_option
-- Options de prix cumulables pour une prestation
-- (ex: "+ couleur" : +1000 centimes).
-- -----------------------------------------------------
CREATE TABLE service_option (
    id INT AUTO_INCREMENT PRIMARY KEY,

    service_id INT NOT NULL,

    label VARCHAR(150) NOT NULL,
    extra_price INT NOT NULL,

    CONSTRAINT fk_service_option_service
        FOREIGN KEY (service_id) REFERENCES service(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : orders
-- Une commande passée par un client auprès d'une boutique,
-- pour une prestation donnée.
-- -----------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,

    client_id INT NOT NULL,
    shop_id INT NOT NULL,
    service_id INT NULL,

    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,

    total_price INT NOT NULL,

    status ENUM(
        'quote_requested',
        'pending',
        'accepted',
        'rejected',
        'in_progress',
        'delivered',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',

    stripe_payment_intent_id VARCHAR(255) NULL,
    delivery_file VARCHAR(255) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_client
        FOREIGN KEY (client_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_service
        FOREIGN KEY (service_id) REFERENCES service(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : order_message
-- Messages échangés entre client et artiste sur une commande.
-- -----------------------------------------------------
CREATE TABLE order_message (
    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,
    sender_id INT NOT NULL,

    content TEXT NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_message_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_message_sender
        FOREIGN KEY (sender_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : review
-- Avis laissé par un client sur une commande terminée.
-- -----------------------------------------------------
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- UNIQUE : un seul avis possible par commande.
    order_id INT NOT NULL UNIQUE,

    rating TINYINT NOT NULL,
    comment TEXT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_review_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,

    -- Contrainte au niveau base de données : la note doit être entre 1 et 5.
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : portfolio_image
-- Images de portfolio affichées sur la fiche boutique.
-- -----------------------------------------------------
CREATE TABLE portfolio_image (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL,

    filename VARCHAR(255) NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_portfolio_image_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : favorite
-- Association utilisateur <-> boutique mise en favori.
-- -----------------------------------------------------
CREATE TABLE favorite (
    user_id INT NOT NULL,
    shop_id INT NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Clé primaire composite : empêche qu'un même utilisateur
    -- mette deux fois la même boutique en favori.
    PRIMARY KEY (user_id, shop_id),

    CONSTRAINT fk_favorite_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_favorite_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : notification
-- Notifications internes pour un utilisateur.
-- -----------------------------------------------------
CREATE TABLE notification (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255) NULL,

    is_read BOOLEAN NOT NULL DEFAULT FALSE,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notification_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;