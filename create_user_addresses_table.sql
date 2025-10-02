-- SQL to create user_addresses table
-- Run this in your MySQL database

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nickname` varchar(100) DEFAULT NULL COMMENT 'User-friendly name like Home, Office',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `country` varchar(2) DEFAULT 'US',
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0 COMMENT '1 if this is the default address',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add index for faster lookups
ALTER TABLE `user_addresses` ADD INDEX `user_default` (`user_id`, `is_default`);

-- Sample test data (optional - for testing with Joe Lorenzo)
INSERT INTO `user_addresses` (`user_id`, `nickname`, `first_name`, `last_name`, `address1`, `city`, `state`, `zip`, `is_default`) 
VALUES 
(19346, 'Office', 'Joe', 'Lorenzo', '123 Main St', 'Erie', 'PA', '16501', 1),
(19346, 'Warehouse', 'Joe', 'Lorenzo', '456 Industrial Blvd', 'Pittsburgh', 'PA', '15201', 0);