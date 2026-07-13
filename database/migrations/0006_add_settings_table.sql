-- Paramètres du site, en clé/valeur pour rester extensible sans
-- migration supplémentaire à chaque nouveau réglage.
CREATE TABLE setting (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
