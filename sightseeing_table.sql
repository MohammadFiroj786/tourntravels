-- Create sightseeing_places table
CREATE TABLE IF NOT EXISTS `sightseeing_places` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `destination` VARCHAR(100) NOT NULL,
  `place_name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image` VARCHAR(255),
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
INSERT INTO `sightseeing_places` (`destination`, `place_name`, `description`, `status`) VALUES
('Darjeeling', 'Tiger Hill', 'Famous for breathtaking sunrise views over Kanchenjunga', 'active'),
('Darjeeling', 'Batasia Loop', 'Historic railway loop with stunning mountain scenery', 'active'),
('Darjeeling', 'Japanese Temple', 'Peace Pagoda offering panoramic views', 'active'),
('Darjeeling', 'Toy Train', 'Heritage narrow gauge train ride through the hills', 'active'),
('Sikkim', 'Tsomgo Lake', 'Sacred glacial lake with stunning natural beauty', 'active'),
('Sikkim', 'Nathula Pass', 'Strategic border pass with historical significance', 'active'),
('Sikkim', 'Baba Mandir', 'War memorial dedicated to soldiers', 'active'),
('Sikkim', 'Gangtok', 'Capital city with monasteries and cultural sites', 'active'),
('Kalimpong', 'Pine View Nursery', 'Beautiful nursery with exotic plants and flowers', 'active'),
('Kalimpong', 'Durpin Monastery', 'Ancient monastery with religious artifacts', 'active'),
('Mirik', 'Sumendu Lake', 'Peaceful lake surrounded by pine forests', 'active'),
('Mirik', 'Lepcha Museum', 'Museum showcasing Lepcha culture and heritage', 'active');