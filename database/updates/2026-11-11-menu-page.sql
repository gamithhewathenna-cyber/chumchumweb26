-- ==============================================================
-- Update 2026-11-11 — Menu page
-- 1. Lets a setting hold up to 16 MB (a pasted menu with CSS can be
--    bigger than the old 64 KB limit). Existing content is kept.
-- 2. Adds the Menu page settings (never overwrites ones you have edited).
-- Safe to run more than once.
-- ==============================================================

SET NAMES utf8mb4;

ALTER TABLE `settings` MODIFY `setting_value` MEDIUMTEXT NOT NULL;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('menu_title',              'Menu'),
('menu_intro',              'Explore Our Menu Of Modern Sri Lankan Favourites And Fusion Dishes — From Kottu And Rice & Curry To Biryani And Fresh Seafood, Made Fresh Every Day.'),
('menu_banner',             ''),
('menu_html',               ''),
('menu_css',                ''),
('menu_empty_text',         'Our full menu is coming soon. In the meantime, give us a call and we''ll happily tell you what''s cooking today.'),
('menu_meta_description',   'See the menu at Chum Chum''s Sri Lankan Cuisine Restaurant in Clayton, Melbourne — kottu, rice & curry, biryani, seafood and modern fusion dishes.');
