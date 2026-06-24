-- ROTAMIZ - Diyarbakır Yerel Rehber Multi-Tenant DB Schema Migration

-- Districts table
CREATE TABLE IF NOT EXISTS `districts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `lat` DECIMAL(10, 8) NULL,
    `lng` DECIMAL(11, 8) NULL,
    `weather_code` VARCHAR(50) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles and Points for Users
ALTER TABLE `users` 
ADD COLUMN `role` ENUM('SUPER_ADMIN', 'DISTRICT_ADMIN', 'MEMBER') DEFAULT 'MEMBER' AFTER `is_active`,
ADD COLUMN `points` INT DEFAULT 0 AFTER `role`,
ADD COLUMN `district_id` INT DEFAULT NULL AFTER `points`,
ADD CONSTRAINT fk_user_district FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL;

-- Multi-tenancy for businesses (Places)
ALTER TABLE `businesses`
ADD COLUMN `district_id` INT DEFAULT NULL AFTER `id`,
ADD COLUMN `status` ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'APPROVED' AFTER `category`,
ADD CONSTRAINT fk_business_district FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL;

-- Multi-tenancy for events
ALTER TABLE `events`
ADD COLUMN `district_id` INT DEFAULT NULL AFTER `id`,
ADD COLUMN `is_global` TINYINT(1) DEFAULT 0 AFTER `district_id`,
ADD COLUMN `status` ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'APPROVED' AFTER `is_global`,
ADD CONSTRAINT fk_event_district FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE SET NULL;

-- Check-ins table
CREATE TABLE IF NOT EXISTS `check_ins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `place_id` INT NOT NULL,
    `district_id` INT NOT NULL,
    `status` ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`place_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Badges (Achievement System)
CREATE TABLE IF NOT EXISTS `badges` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100) DEFAULT NULL,
    `description` TEXT,
    `description_en` TEXT,
    `icon` VARCHAR(255) DEFAULT 'assets/img/badges/default.png',
    `requirement_type` ENUM('CHECK_IN_COUNT', 'COMMENT_COUNT', 'DISTRICT_SPECIFIC_CHECK_IN') NOT NULL,
    `requirement_value` INT NOT NULL,
    `district_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User earned badges
CREATE TABLE IF NOT EXISTS `user_badges` (
    `user_id` INT NOT NULL,
    `badge_id` INT NOT NULL,
    `awarded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `badge_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`badge_id`) REFERENCES `badges`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Global/District Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL COMMENT 'NULL means global/district notification',
    `district_id` INT DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed initial Districts
INSERT INTO `districts` (`name`, `slug`, `lat`, `lng`) VALUES 
('Bağlar', 'baglar', 37.9158, 40.2189),
('Bismil', 'bismil', 37.8481, 40.6636),
('Çermik', 'cermik', 38.1361, 39.4491),
('Çınar', 'cinar', 37.7233, 40.4131),
('Çüngüş', 'cungus', 38.2211, 39.2741),
('Dicle', 'dicle', 38.3736, 40.0683),
('Eğil', 'egil', 38.2561, 40.0800),
('Ergani', 'ergani', 38.2714, 39.7578),
('Hani', 'hani', 38.4117, 40.4000),
('Hazro', 'hazro', 38.2536, 40.7719),
('Kayapınar', 'kayapinar', 37.9389, 40.1581),
('Kocaköy', 'kocakoy', 38.2583, 40.5475),
('Kulp', 'kulp', 38.4914, 41.0117),
('Lice', 'lice', 38.4550, 40.6439),
('Silvan', 'silvan', 38.1425, 41.0117),
('Sur', 'sur', 37.9125, 40.2317),
('Yenişehir', 'yenisehir', 37.9197, 40.2186);

-- Update existing Çermik data to have the correct district_id (Assuming 3)
UPDATE `businesses` SET `district_id` = 3 WHERE `district_id` IS NULL;
UPDATE `events` SET `district_id` = 3 WHERE `district_id` IS NULL;
