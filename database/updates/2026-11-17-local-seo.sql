-- 2026-11-17 — Local SEO pages (suburb + dish pages). Safe to run more than once.
SET NAMES utf8mb4;
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('local_enabled',    '1'),
('seo_lat',          '-37.9196'),
('seo_lng',          '145.1222'),
('local_image',      ''),
('local_hub_title',  'Sri Lankan Restaurant Near You'),
('local_hub_intro',  'Chum Chum''s cooks authentic Sri Lankan food in Clayton — kottu roti, biryani, rice & curry, devilled dishes and more — for locals across Melbourne''s south-east, all within about 15 km.'),
('dishes_hub_title', 'Our Sri Lankan Dishes'),
('dishes_hub_intro', 'The dishes people cross the south-east for — cooked fresh in our Clayton kitchen with real Sri Lankan spices.');
