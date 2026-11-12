-- ==============================================================
-- ALL-go-live.sql — every database update in one go.
-- Run this ONCE in phpMyAdmin (select tcplckfa_chumchum26 → SQL → paste → Go).
-- Every part is safe to repeat: nothing you have already edited is
-- overwritten, and steps that are already done are skipped.
-- ==============================================================

SET NAMES utf8mb4;


-- ############ 2026-11-11-hero-slider.sql ############
-- ==============================================================
-- Update 2026-11-11 — Hero dish slider
-- Adds slide fields to menu_categories and fills in starter text.
-- Run in phpMyAdmin → (select the database) → SQL. Safe to run twice:
-- columns are only added if missing, and only EMPTY slide fields are filled.
-- ==============================================================


-- 1. Add the four slide columns (only if they don't exist yet)
SET @add_cols = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `menu_categories`
       ADD COLUMN `hero_badge`    VARCHAR(100) NOT NULL DEFAULT '''' AFTER `is_active`,
       ADD COLUMN `hero_title`    VARCHAR(150) NOT NULL DEFAULT '''' AFTER `hero_badge`,
       ADD COLUMN `hero_subtitle` VARCHAR(150) NOT NULL DEFAULT '''' AFTER `hero_title`,
       ADD COLUMN `hero_image`    VARCHAR(255) NOT NULL DEFAULT '''' AFTER `hero_subtitle`',
    'SELECT ''hero columns already exist'' AS info')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menu_categories' AND COLUMN_NAME = 'hero_title'
);
PREPARE stmt FROM @add_cols;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Starter slide text (never overwrites text you've already entered)
UPDATE `menu_categories` SET `hero_title` = 'Chicken Submarine',            `hero_subtitle` = 'Toasted Bread, Fresh Salad And Signature Sauce'  WHERE `slug` = 'submarine'   AND `hero_title` = '';
UPDATE `menu_categories` SET `hero_title` = 'Seafood & Chicken Fried Rice', `hero_subtitle` = 'With Fresh Vegetables And Egg'                   WHERE `slug` = 'fried-rice'  AND `hero_title` = '';
UPDATE `menu_categories` SET `hero_title` = 'Chicken Kottu Roti',           `hero_subtitle` = 'Chopped Roti, Vegetables And Spicy Curry Gravy'  WHERE `slug` = 'kottu'       AND `hero_title` = '';
UPDATE `menu_categories` SET `hero_title` = 'Devilled Cuttle Fish',         `hero_subtitle` = 'Crispy, Spicy And Tossed With Peppers And Onion' WHERE `slug` = 'cuttle-fish' AND `hero_title` = '';
UPDATE `menu_categories` SET `hero_title` = 'Sri Lankan Rice & Curry',      `hero_subtitle` = 'Rice With Curries, Sambol And Papadam'           WHERE `slug` = 'rice-curry'  AND `hero_title` = '';
UPDATE `menu_categories` SET `hero_title` = 'Chicken Biryani',              `hero_subtitle` = 'Fragrant Spiced Rice With Tender Chicken'        WHERE `slug` = 'biryani'     AND `hero_title` = '';

-- 3. The existing fried-rice bowl photo becomes the Fried Rice slide photo
UPDATE `menu_categories` SET `hero_image` = 'hero-bowl.webp' WHERE `slug` = 'fried-rice' AND `hero_image` = '';


-- ############ 2026-11-11-spinning-plate.sql ############
-- ==============================================================
-- Update 2026-11-11 — Spinning plate
-- The hero now spins the dish photo, so it needs a full round plate.
-- Swaps the old half-bowl photo (hero-bowl.webp) for the round version
-- (hero-plate.webp). Only changes rows still using the old file, so it is
-- safe to run more than once and never touches photos you uploaded.
-- ==============================================================


UPDATE `settings`
   SET `setting_value` = 'hero-plate.webp'
 WHERE `setting_key` = 'hero_image' AND `setting_value` = 'hero-bowl.webp';

UPDATE `menu_categories`
   SET `hero_image` = 'hero-plate.webp'
 WHERE `hero_image` = 'hero-bowl.webp';


-- ############ 2026-11-11-contact-page.sql ############
-- ==============================================================
-- Update 2026-11-11 — Contact page
-- Adds the table that stores contact-form messages and the Contact
-- page settings. Safe to run twice: the table is only created if missing
-- and settings you've already edited are never overwritten.
-- ==============================================================


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


-- ############ 2026-11-11-about-page.sql ############
-- ==============================================================
-- Update 2026-11-11 — About page
-- Adds the About page settings and the "Why Choose Us" cards table.
-- Safe to run twice: settings you've edited are never overwritten and
-- the starter cards are only added if there are no cards yet.
-- ==============================================================


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

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
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
('aboutpage_meta_description',    'Chum Chum''s brings the vibrant flavours of Sri Lankan cuisine to Clayton, Melbourne — traditional favourites and modern fusion dishes, made fresh with bold spices.');


-- ############ 2026-11-11-menu-page.sql ############
-- ==============================================================
-- Update 2026-11-11 — Menu page
-- 1. Lets a setting hold up to 16 MB (a pasted menu with CSS can be
--    bigger than the old 64 KB limit). Existing content is kept.
-- 2. Adds the Menu page settings (never overwrites ones you have edited).
-- Safe to run more than once.
-- ==============================================================


ALTER TABLE `settings` MODIFY `setting_value` MEDIUMTEXT NOT NULL;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('menu_title',              'Menu'),
('menu_intro',              'Explore Our Menu Of Modern Sri Lankan Favourites And Fusion Dishes — From Kottu And Rice & Curry To Biryani And Fresh Seafood, Made Fresh Every Day.'),
('menu_banner',             'menu-banner.jpg'),
('menu_html',               ''),
('menu_css',                ''),
('menu_empty_text',         'Our full menu is coming soon. In the meantime, give us a call and we''ll happily tell you what''s cooking today.'),
('menu_meta_description',   'See the menu at Chum Chum''s Sri Lankan Cuisine Restaurant in Clayton, Melbourne — kottu, rice & curry, biryani, seafood and modern fusion dishes.');


-- ############ 2026-11-11-menu-banner.sql ############
-- ==============================================================
-- Update 2026-11-11 — Menu page banner
-- Shows the new banner photo (assets/img/menu-banner.jpg) under the
-- Menu page heading, like the Contact and About pages. Only applies if
-- no banner has been chosen yet, so it never replaces one you uploaded.
-- Safe to run more than once.
-- ==============================================================


INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('menu_banner', 'menu-banner.jpg');

UPDATE `settings` SET `setting_value` = 'menu-banner.jpg'
 WHERE `setting_key` = 'menu_banner' AND `setting_value` = '';


-- ############ 2026-11-11-gallery-page.sql ############
-- ==============================================================
-- Update 2026-11-11 — Gallery page
-- Adds the gallery photos table (filled with photos already on the site)
-- and the Gallery page settings. Safe to run twice: starter photos are
-- only added if the gallery is empty, edited settings are never overwritten.
-- ==============================================================


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

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('gallery_title',               'Gallery'),
('gallery_intro',               'A Taste Of Life At Chum Chum''s — Our Dishes, Our Kitchen And The Warm, Busy Dining Room In Clayton. Click Any Photo To See It Bigger.'),
('gallery_empty_text',          'Our gallery is coming soon — check back shortly for photos of our food and restaurant.'),
('gallery_meta_description',    'Photos of the food, kitchen and dining room at Chum Chum''s Sri Lankan Cuisine Restaurant in Clayton, Melbourne.');


-- ############ maintenance mode settings (if not added before) ############
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('maintenance_mode', '0'),
('maintenance_title', 'We''ll Be Back Soon'),
('maintenance_message', 'Our website is getting a fresh new look. We''re still serving delicious Sri Lankan food — give us a call or drop us an email in the meantime.');


-- ############ 2026-11-12-email-smtp.sql ############
-- ==============================================================
-- Update 2026-11-12 — Email (SMTP) settings
-- 1. Contact messages remember whether their email was sent.
-- 2. Adds the Email (SMTP) settings (filled in from the admin).
-- Safe to run more than once.
-- ==============================================================


SET @add_cols = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `contact_messages`
       ADD COLUMN `email_status` VARCHAR(20)  NOT NULL DEFAULT '''' AFTER `is_read`,
       ADD COLUMN `email_error`  VARCHAR(255) NOT NULL DEFAULT '''' AFTER `email_status`',
    'SELECT ''email columns already exist'' AS info')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'email_status'
);
PREPARE stmt FROM @add_cols;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('smtp_enabled',     '0'),
('smtp_host',        ''),
('smtp_port',        '465'),
('smtp_encryption',  'ssl'),
('smtp_username',    ''),
('smtp_password',    ''),
('smtp_verify_cert', '1'),
('smtp_from_email',  ''),
('smtp_from_name',   'Chum Chum''s Website'),
('smtp_to_email',    ''),
('smtp_cc',          '');
