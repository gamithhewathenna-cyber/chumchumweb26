-- ==============================================================
-- Update 2026-11-11 — Menu page banner
-- Shows the new banner photo (assets/img/menu-banner.jpg) under the
-- Menu page heading, like the Contact and About pages. Only applies if
-- no banner has been chosen yet, so it never replaces one you uploaded.
-- Safe to run more than once.
-- ==============================================================

SET NAMES utf8mb4;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('menu_banner', 'menu-banner.jpg');

UPDATE `settings` SET `setting_value` = 'menu-banner.jpg'
 WHERE `setting_key` = 'menu_banner' AND `setting_value` = '';
