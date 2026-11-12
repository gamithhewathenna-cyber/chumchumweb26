-- ==============================================================
-- Update 2026-11-12 — Email (SMTP) settings
-- 1. Contact messages remember whether their email was sent.
-- 2. Adds the Email (SMTP) settings (filled in from the admin).
-- Safe to run more than once.
-- ==============================================================

SET NAMES utf8mb4;

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
