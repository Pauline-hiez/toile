-- =========================================================
-- Jeu de données de test pour le développement local.
-- À exécuter une fois le schéma (toile.sql) en place.
-- Ne JAMAIS exécuter ce fichier en production.
-- =========================================================

-- Un client
INSERT INTO users (email, username, password_hash, role)
VALUES (
    'client@test.com',
    'ClientTest',
    -- Mot de passe en clair pour ce hash : "password123"
    '$2y$10$pUyfk4n0VLLlNVQ.WMyR9.PVwTl/v8b/8r3HmqsZcOEpr2gN4Q9c2',
    'user'
);

-- Un artiste
INSERT INTO users (email, username, password_hash, role)
VALUES (
    'artiste@test.com',
    'ArtisteTest',
    '$2y$10$pUyfk4n0VLLlNVQ.WMyR9.PVwTl/v8b/8r3HmqsZcOEpr2gN4Q9c2',
    'artist'
);

-- La boutique de l'artiste (user_id = 2, le 2ème inséré ci-dessus)
INSERT INTO shop (user_id, name, slug, bio, is_open)
VALUES (
    2,
    'Atelier Lunarya',
    'atelier-lunarya',
    'Illustrations oniriques et poétiques, inspirées par la nature et les rêves.',
    TRUE
);

-- Quelques prestations pour cette boutique (shop_id = 1)
INSERT INTO service (shop_id, title, description, base_price, delivery_days)
VALUES
    (1, 'Portrait', 'Demi-corps, personnage ou personne.', 3500, 7),
    (1, 'Illustration', 'Un personnage, un décor, une ambiance.', 8000, 14),
    (1, 'Illustration complexe', 'Scène détaillée, plusieurs personnages, décor fouillé.', 15000, 21);

-- Un administrateur
INSERT INTO users (email, username, password_hash, role)
VALUES (
    'admin@test.com',
    'AdminTest',
    '$2y$10$pUyfk4n0VLLlNVQ.WMyR9.PVwTl/v8b/8r3HmqsZcOEpr2gN4Q9c2',
    'admin'
);