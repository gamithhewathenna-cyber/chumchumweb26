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
  `setting_value` MEDIUMTEXT   NOT NULL,
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
('hero_image',        'hero-plate.webp'),
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
('maintenance_message', 'Our website is getting a fresh new look. We''re still serving delicious Sri Lankan food — give us a call or drop us an email in the meantime.'),
('aboutpage_title',               'About Us'),
('aboutpage_intro',               'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat. Vel Dictumst In Cras Viverra Pretium Massa.'),
('aboutpage_banner',              'about-banner.jpg'),
('aboutpage_intro_badge',         'About Chumchums'),
('aboutpage_intro_title',         'A Taste Of Sri Lanka,\nReimagined For Melbourne'),
('aboutpage_intro_text',          'At Chum Chums, We Bring The Vibrant Flavours, Aromas, And Warmth Of Sri Lankan Cuisine To The Heart Of Clayton, Melbourne.\n\nBorn From A Passion For Authentic Sri Lankan Food And A Love For Bringing People Together, Chum Chums Was Created With One Simple Idea — To Give Melbourne A Fresh And Modern Way To Experience The Flavours Of Sri Lanka.\n\nFrom Traditional Favourites To Creative Fusion Dishes, Our Menu Celebrates The Rich Culinary Heritage Of Sri Lanka While Adding Our Own Modern Touch. Every Dish Is Prepared With Carefully Selected Ingredients, Bold Spices, And Plenty Of Passion.'),
('aboutpage_intro_image',         'about-fork.jpg'),
('aboutpage_story_title',         'Our Story.'),
('aboutpage_story_text',          'Lorem Ipsum Dolor Sit Amet Consectetur. Magna Ullamcorper Bibendum Sodales Eu Egestas Quis Suspendisse Non. Nibh Tincidunt Sit Metus Nulla Interdum Cursus. Lorem Eget Sed Amet Dignissim In. Neque Facilisi Metus Diam Eu Eleifend Libero. Viverra Adipiscing Purus Leo Congue Etiam Eget Nisl. Nec Tristique Amet Fames Morbi Duis Diam Maecenas A Consequat. Risus Eros Malesuada Faucibus Velit Ante Varius.\n\nNunc Et Neque Convallis Ac Lacus. Velit Aenean A Venenatis Mattis Lorem Urna Viverra Convallis Sed. Quisque Parturient Ac Pellentesque Dictum Imperdiet Mauris Proin Lectus. Placerat At Sit Sit Praesent Ultricies Ut Neque Molestie. Viverra Tellus Neque Diam A Semper. Penatibus Eget Pellentesque Arcu Nisi Parturient Amet. Ut Euismod Ut Condimentum Risus Luctus.\n\nSit Varius Sit Consectetur At Nisi Risus Dolor Ut Fermentum. Non Sed Orci Arcu Pretium Ultrices Ullamcorper. Dictumst Euismod Id Suspendisse Pellentesque Nunc Ultrices. Habitant Aenean Faucibus Egestas Tortor. Gravida Bibendum Leo Viverra Feugiat Tortor Adipiscing. Turpis Fames Quam Feugiat Et Ut. Eget Imperdiet Mi Cum Ullamcorper. Volutpat Maecenas Cras Fermentum Molestie Sit Cras. Venenatis Congue Aliquam Nibh Id Et Tellus.'),
('aboutpage_story_image',         'about-story.jpg'),
('aboutpage_why_badge',           'Modern Flavours. Sri Lankan Roots.'),
('aboutpage_why_title',           'Why Choose Chumchums?'),
('aboutpage_why_text',            'Lorem Ipsum Dolor Sit Amet Consectetur. Adipiscing Potenti Sit Risus Mattis Placerat Risus. Gravida Ac Duis Sed Placerat.'),
('aboutpage_show_reservation',    '1'),
('aboutpage_meta_description',    'Chum Chum''s brings the vibrant flavours of Sri Lankan cuisine to Clayton, Melbourne — traditional favourites and modern fusion dishes, made fresh with bold spices.'),
('gallery_title',               'Gallery'),
('gallery_intro',               'A Taste Of Life At Chum Chum''s — Our Dishes, Our Kitchen And The Warm, Busy Dining Room In Clayton. Click Any Photo To See It Bigger.'),
('gallery_empty_text',          'Our gallery is coming soon — check back shortly for photos of our food and restaurant.'),
('gallery_meta_description',    'Photos of the food, kitchen and dining room at Chum Chum''s Sri Lankan Cuisine Restaurant in Clayton, Melbourne.'),
('menu_title',              'Menu'),
('menu_intro',              'Explore Our Menu Of Modern Sri Lankan Favourites And Fusion Dishes — From Kottu And Rice & Curry To Biryani And Fresh Seafood, Made Fresh Every Day.'),
('menu_banner',             'menu-banner.jpg'),
('menu_html',               ''),
('menu_css',                ''),
('menu_empty_text',         'Our full menu is coming soon. In the meantime, give us a call and we''ll happily tell you what''s cooking today.'),
('menu_meta_description',   'See the menu at Chum Chum''s Sri Lankan Cuisine Restaurant in Clayton, Melbourne — kottu, rice & curry, biryani, seafood and modern fusion dishes.'),
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
  -- Hero slider: each category is also a slide (empty = use the Hero defaults)
  `hero_badge`    VARCHAR(100) NOT NULL DEFAULT '',
  `hero_title`    VARCHAR(150) NOT NULL DEFAULT '',
  `hero_subtitle` VARCHAR(150) NOT NULL DEFAULT '',
  `hero_image`    VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_categories` (`name`, `slug`, `thumbnail`, `link_url`, `side`, `sort_order`, `hero_title`, `hero_subtitle`, `hero_image`) VALUES
('Submarine',    'submarine',    'thumb-submarine.png',   '#menu', 'left',  1, 'Chicken Submarine',            'Toasted Bread, Fresh Salad And Signature Sauce',  ''),
('Fried Rice',   'fried-rice',   'thumb-fried-rice.png',  '#menu', 'left',  2, 'Seafood & Chicken Fried Rice', 'With Fresh Vegetables And Egg',                   'hero-plate.webp'),
('Kottu',        'kottu',        'thumb-kottu.png',       '#menu', 'left',  3, 'Chicken Kottu Roti',           'Chopped Roti, Vegetables And Spicy Curry Gravy',  ''),
('Cuttle Fish',  'cuttle-fish',  'thumb-cuttle-fish.png', '#menu', 'right', 1, 'Devilled Cuttle Fish',         'Crispy, Spicy And Tossed With Peppers And Onion', ''),
('Rice & Curry', 'rice-curry',   'thumb-rice-curry.png',  '#menu', 'right', 2, 'Sri Lankan Rice & Curry',      'Rice With Curries, Sambol And Papadam',           ''),
('Biryani',      'biryani',      'thumb-biryani.png',     '#menu', 'right', 3, 'Chicken Biryani',              'Fragrant Spiced Rice With Tender Chicken',        '');

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

-- ---------- About page: Why Choose Us cards ----------
-- Not dropped on re-import, so your cards survive a content reset.
CREATE TABLE IF NOT EXISTS `about_features` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(100) NOT NULL,
  `text`       VARCHAR(400) NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starter cards (only if the table is still empty)
INSERT INTO `about_features` (`title`, `text`, `sort_order`)
SELECT t.* FROM (
  SELECT 'Authentic Sri Lankan Inspiration' AS title, 'Experience The Bold Spices, Textures, And Flavours Inspired By Sri Lanka.' AS text, 1 AS sort_order
  UNION ALL
  SELECT 'Modern Fusion Cuisine' AS title, 'Traditional Favourites Meet Creative Ideas To Create Something Uniquely Chum Chums.' AS text, 2 AS sort_order
  UNION ALL
  SELECT 'Freshly Prepared' AS title, 'We Focus On Quality Ingredients And Freshly Prepared Dishes For Every Customer.' AS text, 3 AS sort_order
  UNION ALL
  SELECT 'Made For Sharing' AS title, 'Our Food Is Meant To Bring People - Whether It''s A Family Feast Or A Casual Meal With Friends.' AS text, 4 AS sort_order
  UNION ALL
  SELECT 'Dine-In & Takeaway' AS title, 'Enjoy Your Favourite Dishes At Our Clayton Restaurant Or Take Them With You.' AS text, 5 AS sort_order
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `about_features`);

-- ---------- Gallery photos ----------
-- Not dropped on re-import, so your photos survive a content reset.
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image`      VARCHAR(255) NOT NULL,
  `caption`    VARCHAR(200) NOT NULL DEFAULT '',
  `size`       ENUM('normal','wide','tall','big') NOT NULL DEFAULT 'normal',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starter photos (only if the gallery is still empty)
INSERT INTO `gallery_images` (`image`, `caption`, `size`, `sort_order`)
SELECT t.* FROM (
  SELECT 'yellow-rice-curry.jpg' AS image, 'Yellow Rice & Curry on banana leaf' AS caption, 'tall' AS size, 1 AS sort_order
  UNION ALL
  SELECT 'about-banner.jpg' AS image, 'Fresh pasta, made to order' AS caption, 'wide' AS size, 2 AS sort_order
  UNION ALL
  SELECT 'seafood-soup.jpg' AS image, 'Seafood soup' AS caption, 'normal' AS size, 3 AS sort_order
  UNION ALL
  SELECT 'about-story.jpg' AS image, 'Our dining room in Clayton' AS caption, 'big' AS size, 4 AS sort_order
  UNION ALL
  SELECT 'buddha-bowl.jpg' AS image, 'Fresh Buddha bowl' AS caption, 'normal' AS size, 5 AS sort_order
  UNION ALL
  SELECT 'about-fork.jpg' AS image, 'Pesto noodles' AS caption, 'tall' AS size, 6 AS sort_order
  UNION ALL
  SELECT 'toasted-sandwich.jpg' AS image, 'Toasted sandwich' AS caption, 'normal' AS size, 7 AS sort_order
  UNION ALL
  SELECT 'contact-banner.jpg' AS image, 'BBQ ribs platter' AS caption, 'wide' AS size, 8 AS sort_order
  UNION ALL
  SELECT 'chef.jpg' AS image, 'Plating up in the kitchen' AS caption, 'tall' AS size, 9 AS sort_order
  UNION ALL
  SELECT 'hero-plate.webp' AS image, 'Seafood & chicken fried rice' AS caption, 'normal' AS size, 10 AS sort_order
  UNION ALL
  SELECT 'reservation.jpg' AS image, 'Dinner at Chum Chum''s' AS caption, 'wide' AS size, 11 AS sort_order
) AS t
WHERE NOT EXISTS (SELECT 1 FROM `gallery_images`);

-- ---------- Contact form messages ----------
-- Not dropped on re-import, so messages survive a content reset.
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
