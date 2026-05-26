CREATE TABLE IF NOT EXISTS `admin_audit_log` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id`   INT          NOT NULL,
  `action`     VARCHAR(60)  NOT NULL,
  `target_id`  INT          DEFAULT NULL,
  `note`       VARCHAR(255) DEFAULT NULL,
  `ip`         VARCHAR(45)  DEFAULT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id`   (`admin_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
