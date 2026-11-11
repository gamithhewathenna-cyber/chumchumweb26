-- ==============================================================
-- Update 2026-11-11 — Contact page
-- Adds the table that stores contact-form messages and the Contact
-- page settings. Safe to run twice: the table is only created if missing
-- and settings you've already edited are never overwritten.
-- ==============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(60)  NOT NULL,
  `last_name`  VARCHAR(60)  NOT NULL DEFAULT '',
  `phone`      VARCHAR(30)  NOT NULL DEFAULT '',
  `email`      VARCHAR(150) NOT NULL,
  `message`    TEXT         NOT NULL,
  `ip`         VARCHAR(45)  NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_read_created` (`is_read`, `created_at`),
  KEY `idx_ip_created` (`ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('contact_title',            'Contact Us'),
('contact_intro',            'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.'),
('contact_banner',           'contact-banner.jpg'),
('contact_heading',          'Our Address'),
('contact_text',             'Got a craving, a question, or just want to share your foodie love? We''re all ears!'),
('contact_map_query',        '47 Morton St, Clayton VIC 3168, Australia'),
('contact_success',          'Thank you! Your message has been sent — we''ll get back to you as soon as we can.'),
('contact_notify_enabled',   '1'),
('contact_notify_email',     ''),
('contact_meta_description', 'Get in touch with Chum Chum''s Sri Lankan Cuisine Restaurant in Clayton, Melbourne — call, email, find us on the map or send us a message.');
