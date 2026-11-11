-- ==============================================================
-- Update 2026-11-11 — Gallery page
-- Adds the gallery photos table (filled with photos already on the site)
-- and the Gallery page settings. Safe to run twice: starter photos are
-- only added if the gallery is empty, edited settings are never overwritten.
-- ==============================================================

SET NAMES utf8mb4;

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
