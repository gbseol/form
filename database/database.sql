-- =============================================================================
-- Lab Management System - MySQL Database
-- -----------------------------------------------------------------------------
-- Host: any MySQL 5.7+ / 8.0 database (compatible with InfinityFree)
-- Import this file using phpMyAdmin (Import tab) or the MySQL client.
--
-- Included:
--   1. users table
--   2. labs table
--   3. computers table
--   4. computer_photos table
--   5. activity_logs table
--   6. settings table
--   7. One default Super Admin account
--        Username : admin
--        Password : Admin@123   (CHANGE IT AFTER FIRST LOGIN)
-- =============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Database name is created through your hosting control panel (InfinityFree).
-- If you import via phpMyAdmin you may uncomment the line below after choosing
-- your database in the left panel:
-- CREATE DATABASE IF NOT EXISTS `computer_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `computer_management`;

-- -----------------------------------------------------------------------------
-- Table: users
-- Holds the login accounts for the three roles (super_admin, admin, staff).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `permissions` TEXT DEFAULT NULL COMMENT 'Explicit module access (comma separated); NULL inherits the role defaults',
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `theme` ENUM('light','dark') NOT NULL DEFAULT 'light' COMMENT 'Interface theme (light or dark)',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System user accounts';

-- -----------------------------------------------------------------------------
-- Table: labs
-- The physical computer labs / rooms (Lab 1, Lab 2, Office, Library ...).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `labs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_labs_name` (`name`),
  KEY `idx_labs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer labs / rooms';

-- -----------------------------------------------------------------------------
-- Table: computers
-- The full computer inventory. Holds every hardware / software detail required
-- by the specification plus the current status.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `computers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Identity
  `computer_id` VARCHAR(50) NOT NULL COMMENT 'Unique human readable computer code (e.g. LAB1-PC001)',
  `asset_number` VARCHAR(50) DEFAULT NULL,
  `computer_name` VARCHAR(100) DEFAULT NULL,
  `lab_id` INT UNSIGNED DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `desk_number` VARCHAR(50) DEFAULT NULL,

  -- Hardware
  `cpu` VARCHAR(150) DEFAULT NULL,
  `cpu_condition` VARCHAR(50) DEFAULT NULL,
  `motherboard` VARCHAR(150) DEFAULT NULL,
  `ram` VARCHAR(50) DEFAULT NULL,
  `ram_slots` VARCHAR(20) DEFAULT NULL,
  `storage_type` VARCHAR(50) DEFAULT NULL,
  `storage_capacity` VARCHAR(50) DEFAULT NULL,
  `graphics_card` VARCHAR(150) DEFAULT NULL,
  `power_supply` VARCHAR(100) DEFAULT NULL,

  -- Monitor
  `monitor_brand` VARCHAR(100) DEFAULT NULL,
  `monitor_size` VARCHAR(20) DEFAULT NULL,
  `monitor_serial` VARCHAR(100) DEFAULT NULL,
  `monitor_condition` VARCHAR(50) DEFAULT NULL,

  -- Keyboard & mouse
  `keyboard_brand` VARCHAR(100) DEFAULT NULL,
  `keyboard_condition` VARCHAR(50) DEFAULT NULL,
  `mouse_brand` VARCHAR(100) DEFAULT NULL,
  `mouse_condition` VARCHAR(50) DEFAULT NULL,

  -- Peripherals
  `ups` VARCHAR(100) DEFAULT NULL,
  `ups_battery_status` VARCHAR(50) DEFAULT NULL,
  `printer_connected` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `scanner_connected` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `speaker` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `webcam` ENUM('Yes','No') NOT NULL DEFAULT 'No',

  -- Network
  `lan_status` ENUM('Working','Not Working','Not Available') NOT NULL DEFAULT 'Working',
  `wifi_status` ENUM('Working','Not Working','Not Available') NOT NULL DEFAULT 'Not Available',
  `bluetooth_status` ENUM('Working','Not Working','Not Available') NOT NULL DEFAULT 'Not Available',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `mac_address` VARCHAR(20) DEFAULT NULL,

  -- Software & licensing
  `windows_version` VARCHAR(100) DEFAULT NULL,
  `windows_license` VARCHAR(100) DEFAULT NULL,
  `office_version` VARCHAR(100) DEFAULT NULL,
  `office_license` VARCHAR(100) DEFAULT NULL,
  `antivirus` VARCHAR(100) DEFAULT NULL,

  -- Purchase & service
  `purchase_date` DATE DEFAULT NULL,
  `warranty_expiry` DATE DEFAULT NULL,
  `vendor` VARCHAR(150) DEFAULT NULL,
  `invoice_number` VARCHAR(100) DEFAULT NULL,
  `last_service_date` DATE DEFAULT NULL,
  `next_service_date` DATE DEFAULT NULL,

  -- Status & remarks
  `status` ENUM('Working','Not Working','Has Some Issues') NOT NULL DEFAULT 'Working',
  `remarks` TEXT DEFAULT NULL,

  -- Audit
  `created_by` INT UNSIGNED DEFAULT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_computers_computer_id` (`computer_id`),
  KEY `idx_computers_lab_code` (`lab_id`, `computer_id`),
  KEY `idx_computers_asset_number` (`asset_number`),
  KEY `idx_computers_name` (`computer_name`),
  KEY `idx_computers_lab` (`lab_id`),
  KEY `idx_computers_status` (`status`),
  KEY `idx_computers_department` (`department`),
  KEY `idx_computers_cpu` (`cpu`),
  KEY `idx_computers_ip` (`ip_address`),
  KEY `idx_computers_mac` (`mac_address`),
  KEY `idx_computers_purchase_date` (`purchase_date`),
  KEY `idx_computers_created_at` (`created_at`),

  -- Relationships
  CONSTRAINT `fk_computers_lab` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_computers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_computers_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer inventory';

-- -----------------------------------------------------------------------------
-- Table: computer_photos
-- One or many photos can be attached to every computer.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `computer_photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `computer_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_photos_computer` (`computer_id`),
  CONSTRAINT `fk_photos_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer photos';

-- -----------------------------------------------------------------------------
-- Table: issues
-- Computer issues reported by staff/users so administrators can see and fix them.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `issues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `computer_id` INT UNSIGNED DEFAULT NULL,
  `reported_by` INT UNSIGNED DEFAULT NULL,
  `issue_category` VARCHAR(50) NOT NULL DEFAULT 'Other',
  `description` TEXT NOT NULL,
  `status` ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  `fixed_by` INT UNSIGNED DEFAULT NULL,
  `fixed_at` DATETIME DEFAULT NULL,
  `fix_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_issues_computer` (`computer_id`),
  KEY `idx_issues_reporter` (`reported_by`),
  KEY `idx_issues_status` (`status`),
  CONSTRAINT `fk_issues_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_issues_reporter` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_issues_fixer` FOREIGN KEY (`fixed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer issues reported by users';

-- -----------------------------------------------------------------------------
-- Table: activity_logs
-- Records every important action performed in the system.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `table_name` VARCHAR(100) DEFAULT NULL,
  `record_id` INT UNSIGNED DEFAULT NULL,
  `old_value` MEDIUMTEXT DEFAULT NULL COMMENT 'JSON snapshot of the old state',
  `new_value` MEDIUMTEXT DEFAULT NULL COMMENT 'JSON snapshot of the new state',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_user` (`user_id`),
  KEY `idx_logs_table_record` (`table_name`,`record_id`),
  KEY `idx_logs_created` (`created_at`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Activity audit log';

-- -----------------------------------------------------------------------------
-- Table: settings
-- Simple key/value store for the Settings page (site name, logo, theme colour).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Site settings (key/value)';

-- =============================================================================
-- DEFAULT DATA
-- =============================================================================

-- Default Super Admin account
--   Username : admin
--   Password : Admin@123
-- The password below is a bcrypt hash of "Admin@123". Change it after login.
INSERT INTO `users` (`username`, `full_name`, `email`, `password_hash`, `role`, `status`, `permissions`) VALUES
('admin', 'System Administrator', 'admin@example.com', '$2y$10$/hqxYV4wOInNsicpYjQTJ.xs7ncDhxmhQmUjnPQByDN1Fbvq.7xhK', 'super_admin', 'active', NULL);

-- Example labs (edit / add more through the Lab Management page)
INSERT INTO `labs` (`name`, `location`, `description`, `status`) VALUES
('Lab 1', 'Building A, Ground Floor', 'General purpose computer lab', 'active'),
('Lab 2', 'Building A, First Floor', 'Computer lab for programming classes', 'active'),
('Lab 3', 'Building B, Ground Floor', 'Multimedia lab', 'active'),
('Office', 'Administration Block', 'Staff office computers', 'active'),
('Library', 'Central Library', 'Library browsing terminals', 'active');

-- Default site settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Lab Management'),
('logo', ''),
('theme_color', '#2948c2');

-- =============================================================================
-- END OF FILE
-- =============================================================================
