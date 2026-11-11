-- ==============================================================
-- Update 2026-11-11 — Hero dish slider
-- Adds slide fields to menu_categories and fills in starter text.
-- Run in phpMyAdmin → (select the database) → SQL. Safe to run twice:
-- columns are only added if missing, and only EMPTY slide fields are filled.
-- ==============================================================

SET NAMES utf8mb4;

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
