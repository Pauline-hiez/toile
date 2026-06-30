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

    styles JSON NULL,

    is_open BOOLEAN NOT NULL DEFAULT FALSE,

    monetization_type ENUM('subscription', 'commission') NOT NULL DEFAULT 'commission',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_shop_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;