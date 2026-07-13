-- Signalements : un utilisateur peut signaler un avis (commentaire
-- abusif) ou une image de portfolio (plagiat). Association polymorphe
-- (reportable_type + reportable_id), donc pas de FK directe sur la
-- cible — l'intégrité est vérifiée côté application.
CREATE TABLE report (
    id INT AUTO_INCREMENT PRIMARY KEY,

    reporter_id INT NOT NULL,

    reportable_type ENUM('review', 'portfolio_image') NOT NULL,
    reportable_id INT NOT NULL,

    reason ENUM('plagiat', 'commentaire_inapproprie', 'spam', 'autre') NOT NULL DEFAULT 'autre',
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
