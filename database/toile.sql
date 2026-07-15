-- =========================================================
-- Schéma de la base de données "toile"
-- Ce fichier regroupe toutes les tables du projet.
-- Il s'enrichit au fil des issues/branches du projet.
-- =========================================================

-- -----------------------------------------------------
-- Table : users
-- -----------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(180) NOT NULL UNIQUE,
    username VARCHAR(80) NOT NULL,

    password_hash VARCHAR(255) NULL,

    provider ENUM('credentials', 'google', 'pinterest') NOT NULL DEFAULT 'credentials',
    provider_id VARCHAR(255) NULL,
    stripe_customer_id VARCHAR(255) NULL,

    email_verified_at DATETIME NULL,

    avatar VARCHAR(255) NULL,

    role ENUM('user', 'artist', 'admin') NOT NULL DEFAULT 'user',

    is_banned BOOLEAN NOT NULL DEFAULT FALSE,

    artist_request_status ENUM('pending', 'approved', 'rejected') NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : shop
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
    plan_selected BOOLEAN NOT NULL DEFAULT FALSE,

    monetization_type ENUM('subscription', 'commission') NOT NULL DEFAULT 'commission',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_shop_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : service
-- -----------------------------------------------------
CREATE TABLE service (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL,

    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,

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
-- Table : service_base
-- Éléments de base d'une prestation : choix purement descriptifs côté
-- client (ex. catégorie "Style" → "Réaliste"/"Illustration"/"Caricature"),
-- regroupés par catégorie librement nommée par l'artiste. Un seul choix
-- par catégorie côté client (comportement radio par groupe). Pas de
-- prix — sert à préciser le cahier des charges (format, style,
-- matériaux...), en plus des options payantes de service_option.
-- -----------------------------------------------------
CREATE TABLE service_base (
    id INT AUTO_INCREMENT PRIMARY KEY,

    service_id INT NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT '',

    label VARCHAR(150) NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_service_base_service
        FOREIGN KEY (service_id) REFERENCES service(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : orders
-- -----------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,

    client_id INT NOT NULL,
    shop_id INT NOT NULL,
    service_id INT NULL,

    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,

    total_price INT NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    commission_amount INT NOT NULL DEFAULT 0,

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

    is_archived BOOLEAN NOT NULL DEFAULT FALSE,

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
-- Table : order_service_base
-- Cases à cocher (service_base) sélectionnées par le client sur une
-- commande. Le libellé est dupliqué (snapshot) pour que l'historique de
-- la commande reste lisible même si l'artiste modifie ou supprime la
-- case ensuite (service_base_id passe alors à NULL).
-- -----------------------------------------------------
CREATE TABLE order_service_base (
    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,
    service_base_id INT NULL,
    category VARCHAR(100) NOT NULL DEFAULT '',
    label VARCHAR(150) NOT NULL,

    CONSTRAINT fk_order_service_base_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_service_base_base
        FOREIGN KEY (service_base_id) REFERENCES service_base(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : order_message
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
-- -----------------------------------------------------
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL UNIQUE,

    rating TINYINT NOT NULL,
    comment TEXT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_review_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : portfolio_image
-- -----------------------------------------------------
CREATE TABLE portfolio_image (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL,

    filename VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    label VARCHAR(100) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_portfolio_image_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : favorite
-- -----------------------------------------------------
CREATE TABLE favorite (
    user_id INT NOT NULL,
    shop_id INT NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

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


-- -----------------------------------------------------
-- Table : email_log
-- -----------------------------------------------------
CREATE TABLE email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,

    recipient_email VARCHAR(180) NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,

    status ENUM('sent', 'failed') NOT NULL DEFAULT 'sent',
    error_message TEXT NULL,

    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : password_reset
-- -----------------------------------------------------
CREATE TABLE password_reset (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN NOT NULL DEFAULT FALSE,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : subscription_plan
-- Les paliers d'abonnement (Commission gratuit, Essentiel, Pro).
-- commission_rate  : taux prélevé sur chaque commande.
-- max_services     : nombre max de prestations actives (9999 = illimité).
-- max_portfolio    : nombre max d'images portfolio (9999 = illimité).
-- max_options      : nombre max d'options par prestation (9999 = illimité).
-- -----------------------------------------------------
CREATE TABLE subscription_plan (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,
    price INT NOT NULL,

    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,

    max_services INT NOT NULL DEFAULT 3,
    max_portfolio_images INT NOT NULL DEFAULT 5,
    max_options_per_service INT NOT NULL DEFAULT 2,

    stripe_price_id VARCHAR(255) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : shop_subscription
-- -----------------------------------------------------
CREATE TABLE shop_subscription (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL UNIQUE,
    plan_id INT NOT NULL,

    stripe_subscription_id VARCHAR(255) NULL,

    status ENUM('active', 'cancelled', 'past_due') NOT NULL DEFAULT 'active',

    current_period_start DATETIME NOT NULL,
    current_period_end DATETIME NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_shop_subscription_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_shop_subscription_plan
        FOREIGN KEY (plan_id) REFERENCES subscription_plan(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : raffle_entry
-- Inscriptions aux tirages au sort de mise en avant.
-- type 'boutiques' : tirage mensuel, mise en avant page /boutiques.
-- type 'homepage'  : tirage hebdomadaire, mise en avant page d'accueil.
-- -----------------------------------------------------
CREATE TABLE raffle_entry (
    id INT AUTO_INCREMENT PRIMARY KEY,

    shop_id INT NOT NULL,

    -- Type de tirage.
    type ENUM('boutiques', 'homepage') NOT NULL DEFAULT 'boutiques',

    -- Période concernée.
    -- Pour 'boutiques' : format YYYY-MM (ex: '2026-07').
    -- Pour 'homepage'  : date du lundi de la semaine (ex: '2026-07-07').
    period VARCHAR(10) NOT NULL,

    stripe_payment_intent_id VARCHAR(255) NULL,

    status ENUM('entered', 'selected', 'not_selected', 'cancelled') NOT NULL DEFAULT 'entered',

    -- Pour le tirage homepage : date de fin de mise en avant (7 jours).
    featured_until DATE NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Une boutique ne peut s'inscrire qu'une fois par type et par période.
    UNIQUE KEY unique_shop_type_period (shop_id, type, period),

    CONSTRAINT fk_raffle_entry_shop
        FOREIGN KEY (shop_id) REFERENCES shop(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : report
-- Signalement d'une boutique (dans son ensemble) ou d'un avis.
-- Association polymorphe (reportable_type + reportable_id), pas de
-- FK directe sur la cible — intégrité vérifiée côté application.
-- -----------------------------------------------------
CREATE TABLE report (
    id INT AUTO_INCREMENT PRIMARY KEY,

    reporter_id INT NOT NULL,

    reportable_type ENUM('shop', 'review') NOT NULL,
    reportable_id INT NOT NULL,

    reason ENUM(
        'plagiat',
        'contenu_inapproprie',
        'arnaque',
        'commentaire_inapproprie',
        'spam',
        'autre'
    ) NOT NULL DEFAULT 'autre',
    message TEXT NULL,

    status ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
    resolved_by INT NULL,
    resolved_at DATETIME NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_report_reporter
        FOREIGN KEY (reporter_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_report_resolved_by
        FOREIGN KEY (resolved_by) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Table : setting
-- Paramètres du site, en clé/valeur pour rester extensible.
-- -----------------------------------------------------
CREATE TABLE setting (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- -----------------------------------------------------
-- Données initiales : paliers d'abonnement
-- -----------------------------------------------------
INSERT INTO subscription_plan (name, price, commission_rate, max_services, max_portfolio_images, max_options_per_service, stripe_price_id)
VALUES
    ('Commission', 0, 10.00, 3, 5, 2, NULL),
    ('Essentiel', 1490, 5.00, 10, 15, 5, 'price_1TqAUxCHvrrlwjMD7A31tt6O'),
    ('Pro', 2990, 0.00, 9999, 9999, 9999, 'price_1TqAVUCHvrrlwjMDQd8ZCEbx');