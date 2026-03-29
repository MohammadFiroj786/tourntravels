-- =====================================================
-- DATABASE SCHEMA UPDATE FOR CUSTOM TOUR PACKAGE SYSTEM
-- =====================================================
-- Run this SQL to update your existing custom_package_requests table
-- with new columns needed for the service type system

-- Backup your database before running these commands!
-- For phpMyAdmin: Copy and paste into the SQL tab

-- Add new columns to track service type
ALTER TABLE `custom_package_requests` ADD COLUMN `service_type` VARCHAR(50) DEFAULT 'full' AFTER `user_id`;
ALTER TABLE `custom_package_requests` ADD COLUMN `pickup_location` VARCHAR(100) AFTER `service_type`;
ALTER TABLE `custom_package_requests` ADD COLUMN `destinations` TEXT AFTER `pickup_location`;
ALTER TABLE `custom_package_requests` ADD COLUMN `sightseeing_places` LONGTEXT AFTER `destinations`;
ALTER TABLE `custom_package_requests` ADD COLUMN `hotel_type` VARCHAR(50) AFTER `sightseeing_places`;
ALTER TABLE `custom_package_requests` ADD COLUMN `user_notes` LONGTEXT AFTER `hotel_type`;
ALTER TABLE `custom_package_requests` ADD COLUMN `car_type` VARCHAR(100) AFTER `user_notes`;
ALTER TABLE `custom_package_requests` ADD COLUMN `status` VARCHAR(50) DEFAULT 'Pending' AFTER `car_type`;
ALTER TABLE `custom_package_requests` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `status`;

-- If the table doesn't exist yet, create it with the full new structure:
CREATE TABLE IF NOT EXISTS `custom_package_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `service_type` VARCHAR(50) DEFAULT 'full' COMMENT 'full, stay, or sightseeing',
  `pickup_location` VARCHAR(100),
  `destinations` TEXT COMMENT 'Comma-separated destinations',
  `sightseeing_places` LONGTEXT COMMENT 'Selected attractions/places',
  `travel_date` DATE,
  `days` INT DEFAULT 1,
  `travelers` INT DEFAULT 1,
  `hotel_type` VARCHAR(50) COMMENT 'Budget, Standard, Deluxe, Luxury',
  `user_notes` LONGTEXT,
  `car_type` VARCHAR(100) COMMENT 'Car category based on traveler count',
  `status` VARCHAR(50) DEFAULT 'Pending' COMMENT 'Pending or Accepted',
  `price` DECIMAL(10, 2),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
