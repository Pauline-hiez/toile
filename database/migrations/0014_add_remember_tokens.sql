-- "Se souvenir de moi" : jeton persistant (cookie longue durée) qui
-- reconnecte automatiquement un utilisateur sans ressaisir ses
-- identifiants. Seul le hash du jeton est stocké (le cookie envoyé au
-- client contient la valeur en clair) pour qu'une fuite de la base ne
-- permette pas de rejouer les cookies existants.
CREATE TABLE remember_token (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_remember_token_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
