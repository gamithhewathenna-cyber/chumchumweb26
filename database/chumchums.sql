-- ==============================================================
-- Chum Chum's — database schema + homepage content
-- Import via cPanel → phpMyAdmin → (select your database) → Import
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- ==============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------- Site settings (key/value) ----------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key`   VARCHAR(64)  NOT NULL,
  `setting_value` TEXT         NOT NULL,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name',         'Chum Chum''s'),
('meta_title',        'Chum Chum''s | Modern Sri Lankan Cuisine Restaurant – Clayton, Melbourne'),
('meta_description',  'Chum Chum''s Sri Lankan Cuisine Restaurant in Clayton, Melbourne. Modern Sri Lankan flavours, fusion dishes, rice & curry, kottu, biryani and more — open six days a week till midnight.'),
('phone',             '047 593 5804'),
('email',             'chumchumscafe@gmail.com'),
('address_line1',     '47 Morton st clayton VIC 3168,'),
('address_line2',     'Clayton, VIC, Australia, Victoria'),
('facebook_url',      '#'),
('instagram_url',     '#'),
('order_url',         '#'),
('customer_url',      '#'),
('hero_badge',        'Ingredients That Matter'),
('hero_title',        'Seafood & Chicken Fried Rice'),
('hero_subtitle',     'With Fresh Vegetables And Egg'),
('hero_image',        'hero-bowl.webp'),
('about_badge',       'Sri Lankan Cuisine Restaurant'),
('about_title',       'Founded With The Sole Intention Of Presenting Modern Sri Lankan Flavors To The Live Hood Melbourne'),
('about_text',        '"Chum Chum''s" Sri Lankan Cuisine Restaurant Is Founded With The Sole Intention Of Presenting Modern Sri Lankan Flavors To The Live Hood Of Melbourne. With A Modern Touch To The Traditional Recipes, Various Fusion Dishes Will Be Presented Six Days A Week Till Midnight.'),
('about_image',       'chef.jpg'),
('interior_image',    'interior.jpg'),
('hours_title',       'Opening Hours'),
('reservation_title', 'Reservation'),
('reservation_text',  'Book Your Table At Chum Chums And Enjoy A Fantastic Dining Experience With Delicious Food And Great Ambiance!'),
('reservation_label', 'Telephone Reservation'),
('reservation_image', 'reservation.jpg'),
('footer_text_1',     'Lorem ipsum dolor sit amet consectetur. Condimentum convallis nulla nec ultrices convallis non turpis. Euismod lacus nunc in id justo ac sed. Sit habitant donec massa semper nibh maecenas.'),
('footer_text_2',     'Sit senectus ullamcorper massa turpis ac orci egestas. Augue tellus sed urna vehicula semper.'),
('copyright_name',    'Chumchums'),
('maintenance_mode',    '0'),
('maintenance_title',   'We''ll Be Back Soon'),
('maintenance_message', 'Our website is getting a fresh new look. We''re still serving delicious Sri Lankan food — give us a call or drop us an email in the meantime.');

-- ---------- Hero menu categories ----------
DROP TABLE IF EXISTS `menu_categories`;
CREATE TABLE `menu_categories` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(120) NOT NULL,
  `thumbnail`  VARCHAR(255) NOT NULL,
  `link_url`   VARCHAR(255) NOT NULL DEFAULT '#menu',
  `side`       ENUM('left','right') NOT NULL DEFAULT 'left',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_categories` (`name`, `slug`, `thumbnail`, `link_url`, `side`, `sort_order`) VALUES
('Submarine',    'submarine',    'thumb-submarine.png',   '#menu', 'left',  1),
('Fried Rice',   'fried-rice',   'thumb-fried-rice.png',  '#menu', 'left',  2),
('Kottu',        'kottu',        'thumb-kottu.png',       '#menu', 'left',  3),
('Cuttle Fish',  'cuttle-fish',  'thumb-cuttle-fish.png', '#menu', 'right', 1),
('Rice & Curry', 'rice-curry',   'thumb-rice-curry.png',  '#menu', 'right', 2),
('Biryani',      'biryani',      'thumb-biryani.png',     '#menu', 'right', 3);

-- ---------- Opening hours ----------
DROP TABLE IF EXISTS `opening_hours`;
CREATE TABLE `opening_hours` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `label`      VARCHAR(60) NOT NULL,
  `hours`      VARCHAR(60) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `opening_hours` (`label`, `hours`, `sort_order`) VALUES
('Monday - Thursday', '8AM - 12AM', 1),
('Friday - Saturday', '8AM - 2AM',  2),
('Sunday',            '9AM - 12AM', 3);

-- ---------- Showcase grid (7 fixed slots) ----------
-- slot 1 = feature (image + text), 2/4/6 = text cards, 3/5/7 = image tiles
DROP TABLE IF EXISTS `showcase_items`;
CREATE TABLE `showcase_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slot`        TINYINT UNSIGNED NOT NULL,
  `title`       VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `image`       VARCHAR(255) NOT NULL DEFAULT '',
  `link_url`    VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slot` (`slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `showcase_items` (`slot`, `title`, `description`, `image`, `link_url`) VALUES
(1, 'Yellow Rice And Curry', 'A Delicious Platter Of Fragrant Yellow Rice, Rich Curries, Crispy Sides, Fresh Salad, And Tender Glazed Meat, Beautifully Served On A Traditional Banana Leaf.', 'yellow-rice-curry.jpg', '#menu'),
(2, 'Lorem Ipsum Dolor Sit Amet', 'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.', '', '#menu'),
(3, 'Fresh Buddha Bowl', NULL, 'buddha-bowl.jpg', ''),
(4, 'Lorem Ipsum Dolor Sit Amet', 'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.', '', '#menu'),
(5, 'Toasted Sandwich', NULL, 'toasted-sandwich.jpg', ''),
(6, 'Lorem Ipsum Dolor Sit Amet', 'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.', '', '#menu'),
(7, 'Seafood Soup', NULL, 'seafood-soup.jpg', '');

-- ---------- Testimonials ----------
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(150) NOT NULL,
  `content`    TEXT NOT NULL,
  `author`     VARCHAR(100) NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `testimonials` (`title`, `content`, `sort_order`) VALUES
('Lorem Ipsum Dolor Sit Amet', 'Lorem Ipsum Dolor Sit Amet Consectetur. Amet Dolor Quis Id Maecenas Ipsum Nullam Posuere Vitae. Volutpat Tristique Ut Hendrerit Integer Sollicitudin Nibh Pharetra Sagittis. Tellus Vestibulum Ac Nisl Quis Nec Tincidunt. Nam Ultricies Feugiat Enim Cras. Sed Odio Mattis Venenatis Luctus Cum. At Facilisi Leo Etiam Tellus. Tincidunt Id.', 1),
('Lorem Ipsum Dolor Sit Amet', 'Lorem Ipsum Dolor Sit Amet Consectetur. Amet Dolor Quis Id Maecenas Ipsum Nullam Posuere Vitae. Volutpat Tristique Ut Hendrerit Integer Sollicitudin Nibh Pharetra Sagittis. Tellus Vestibulum Ac Nisl Quis Nec Tincidunt. Nam Ultricies Feugiat Enim Cras. Sed Odio Mattis Venenatis Luctus Cum. At Facilisi Leo Etiam Tellus. Tincidunt Id.', 2),
('Lorem Ipsum Dolor Sit Amet', 'Lorem Ipsum Dolor Sit Amet Consectetur. Amet Dolor Quis Id Maecenas Ipsum Nullam Posuere Vitae. Volutpat Tristique Ut Hendrerit Integer Sollicitudin Nibh Pharetra Sagittis. Tellus Vestibulum Ac Nisl Quis Nec Tincidunt. Nam Ultricies Feugiat Enim Cras. Sed Odio Mattis Venenatis Luctus Cum. At Facilisi Leo Etiam Tellus. Tincidunt Id.', 3);

-- ---------- Admin panel ----------
-- Not dropped on re-import, so admin logins survive a content reset.
-- (The admin panel also creates these automatically on first visit.)
-- The first admin account is created at /admin/setup.php.
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(60)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_login_attempts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`           VARCHAR(45)  NOT NULL,
  `attempted_at` DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
