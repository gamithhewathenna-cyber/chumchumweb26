-- 2026-11-17 — Popup Offers (promotion popup). Safe to run more than once.
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS `promo_popups` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(150) NOT NULL DEFAULT '',
  `image`       VARCHAR(255) NOT NULL,
  `link_url`    VARCHAR(500) NOT NULL DEFAULT '',
  `button_text` VARCHAR(60)  NOT NULL DEFAULT '',
  `starts_on`   DATE NULL DEFAULT NULL,
  `ends_on`     DATE NULL DEFAULT NULL,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('popup_enabled',   '1'),
('popup_frequency', 'session'),
('popup_delay',     '2'),
('popup_pages',     'all');
