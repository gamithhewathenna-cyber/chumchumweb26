-- ==============================================================
-- Update 2026-11-11 — About page
-- Adds the About page settings and the "Why Choose Us" cards table.
-- Safe to run twice: settings you've edited are never overwritten and
-- the starter cards are only added if there are no cards yet.
-- ==============================================================

SET NAMES utf8mb4;

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
