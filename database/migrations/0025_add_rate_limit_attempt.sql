-- Rate limiting sur les routes sensibles (login, register, forgot-password)
-- — voir App\Core\RateLimiter. Une ligne par tentative ; identifier porte
-- un préfixe ('ip:'/'email:') pour distinguer les deux natures de
-- compteur (par IP et par cible) sans risque de collision.
CREATE TABLE rate_limit_attempt (
    id INT AUTO_INCREMENT PRIMARY KEY,

    identifier VARCHAR(190) NOT NULL,
    action VARCHAR(50) NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_rate_limit_lookup (identifier, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
