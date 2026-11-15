-- UPDATED DATABASE SCHEMA FOR DELIVERY PROOF SYSTEM
-- Run this script to update your database

-- ========================================
-- ALTER ORDERS TABLE - Add delivery proof tracking
-- ========================================
ALTER TABLE `orders` 
ADD COLUMN `delivery_proof_image` VARCHAR(255) DEFAULT NULL AFTER `receipt_image`,
ADD COLUMN `delivery_notes` TEXT DEFAULT NULL AFTER `delivery_proof_image`,
ADD COLUMN `can_update_to_delivered` BOOLEAN DEFAULT FALSE AFTER `driver_id`,
MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'Pending';

-- ========================================
-- UPDATE DRIVER STATUS FIELD
-- ========================================
ALTER TABLE `drivers` 
MODIFY COLUMN `availability_status` VARCHAR(50) DEFAULT 'Offline',
ADD COLUMN `is_online` BOOLEAN DEFAULT FALSE AFTER `availability_status`,
ADD COLUMN `service_area` VARCHAR(255) DEFAULT NULL AFTER `is_online`;

-- ========================================
-- CREATE DELIVERY PROOF TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `delivery_proofs` (
  `proof_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `delivery_notes` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10, 8) DEFAULT NULL,
  `longitude` decimal(11, 8) DEFAULT NULL,
  PRIMARY KEY (`proof_id`),
  KEY `fk_proof_order` (`order_id`),
  KEY `fk_proof_driver` (`driver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- ADD FOREIGN KEY CONSTRAINTS FOR DELIVERY_PROOFS
-- ========================================
ALTER TABLE `delivery_proofs`
  ADD CONSTRAINT `fk_proof_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proof_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ========================================
-- CREATE ORDER STATUS LOG TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `order_status_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_by_role` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `fk_log_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- ADD FOREIGN KEY CONSTRAINT FOR ORDER_STATUS_LOG
-- ========================================
ALTER TABLE `order_status_log`
  ADD CONSTRAINT `fk_log_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ========================================
-- CREATE DRIVER LOCATION TRACKING TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS `driver_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `latitude` decimal(10, 8) NOT NULL,
  `longitude` decimal(11, 8) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`location_id`),
  KEY `fk_loc_driver` (`driver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- ADD FOREIGN KEY CONSTRAINT FOR DRIVER_LOCATIONS
-- ========================================
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `fk_loc_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ========================================
-- UPDATE CUSTOMERS TABLE - Add preferred location for delivery matching
-- ========================================
ALTER TABLE `customers` 
ADD COLUMN `latitude` decimal(10, 8) DEFAULT NULL AFTER `location`,
ADD COLUMN `longitude` decimal(11, 8) DEFAULT NULL AFTER `latitude`;

COMMIT;