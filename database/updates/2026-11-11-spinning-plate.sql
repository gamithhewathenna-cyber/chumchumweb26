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
