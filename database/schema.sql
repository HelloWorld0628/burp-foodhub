-- Schema-only setup; includes public menu catalog rows, no account or order data.
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2026 at 01:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `burp_foodhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `location` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



-- --------------------------------------------------------

--
-- Table structure for table `delivery_proofs`
--

CREATE TABLE `delivery_proofs` (
  `proof_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `delivery_notes` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) DEFAULT 'Motorcycle',
  `availability_status` varchar(50) DEFAULT 'Offline',
  `is_online` tinyint(1) DEFAULT 0,
  `service_area` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `location_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `menu_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `can_update_to_delivered` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `delivery_proof_image` varchar(255) DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



-- --------------------------------------------------------

--
-- Table structure for table `order_status_log`
--

CREATE TABLE `order_status_log` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_by_role` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--



--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `delivery_proofs`
--
ALTER TABLE `delivery_proofs`
  ADD PRIMARY KEY (`proof_id`),
  ADD KEY `fk_proof_order` (`order_id`),
  ADD KEY `fk_proof_driver` (`driver_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `fk_loc_driver` (`driver_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`menu_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_customer` (`customer_id`),
  ADD KEY `fk_order_driver` (`driver_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_item_order` (`order_id`),
  ADD KEY `fk_item_menu` (`menu_id`);

--
-- Indexes for table `order_status_log`
--
ALTER TABLE `order_status_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_log_order` (`order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `delivery_proofs`
--
ALTER TABLE `delivery_proofs`
  MODIFY `proof_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_status_log`
--
ALTER TABLE `order_status_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery_proofs`
--
ALTER TABLE `delivery_proofs`
  ADD CONSTRAINT `fk_proof_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proof_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `fk_loc_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_item_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu_items` (`menu_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_status_log`
--
ALTER TABLE `order_status_log`
  ADD CONSTRAINT `fk_log_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;
INSERT INTO `menu_items` (`menu_id`, `name`, `price`, `category`, `image`, `description`) VALUES
(1, 'Crispy Pata (Whole)', 565.00, 'pork-chicken', 'menu_img/crispy_pata__whole__1779917495.jpg', 'Pork / Chicken Dishes'),
(2, 'Lechon Kawali', 285.00, 'pork-chicken', 'menu_img/lechon_kawali_1779917737.webp', 'Good for 2-3'),
(3, 'Sizzling Pork Sisig', 215.00, 'pork-chicken', 'menu_img/sizzling_pork_sisig_1779917829.jpg', 'Good for 2'),
(4, 'Dinakdakan', 225.00, 'pork-chicken', 'menu_img/dinakdakan_1779917965.webp', 'Good for 2'),
(5, 'Grilled Liempo', 265.00, 'pork-chicken', 'menu_img/grilled_liempo_1779918068.jpg', 'Good for 2-3'),
(6, 'Fried Liempo', 255.00, 'pork-chicken', 'menu_img/fried_liempo_1779918612.jpg', 'Good for 2-3'),
(7, 'Tokwa\'t Baboy', 145.00, 'pork-chicken', 'menu_img/tokwa__t_baboy_1779918712.jpg', 'Pork / Chicken Dishes'),
(8, 'Adobong Baboy', 265.00, 'pork-chicken', 'menu_img/adobong_baboy_1779918794.jpg', 'Good for 2-3'),
(9, 'Pork Steak', 285.00, 'pork-chicken', 'menu_img/pork_steak_1779918970.jpg', 'Good for 2-3'),
(10, 'Sweet & Sour Pork', 285.00, 'pork-chicken', 'menu_img/sweet___sour_pork_1779919149.jpg', 'Good for 2-3'),
(11, 'Buttered Chicken (Half)', 345.00, 'pork-chicken', 'menu_img/buttered_chicken__half__1779919737.jpg', 'Pork / Chicken Dishes'),
(12, 'Garlic Parmesan Chicken (Half)', 365.00, 'pork-chicken', 'menu_img/garlic_parmesan_chicken__half__1779919895.webp', 'Pork / Chicken Dishes'),
(13, 'Spicy Buffalo Chicken (Half)', 365.00, 'pork-chicken', 'menu_img/spicy_buffalo_chicken__half__1779920012.jpg', 'Pork / Chicken Dishes'),
(14, 'Chicken Sisig', 195.00, 'pork-chicken', 'menu_img/chicken_sisig_1779920086.webp', 'Good for 2'),
(15, 'Pansit Bihon', 170.00, 'noodles-pasta', 'menu_img/pansit_bihon_1779920302.png', 'Noodles / Pasta'),
(16, 'Pansit Canton', 180.00, 'noodles-pasta', 'menu_img/pansit_canton_1779920480.webp', 'Noodles / Pasta'),
(17, 'Pansit Mix', 185.00, 'noodles-pasta', 'menu_img/pansit_mix_1779920639.jpg', 'Noodles / Pasta'),
(18, 'Lomi Regular', 80.00, 'noodles-pasta', 'menu_img/lomi_regular_1779920725.avif', 'Noodles / Pasta'),
(19, 'Lomi Overload', 120.00, 'noodles-pasta', 'menu_img/lomi_overload_1779920780.avif', 'Noodles / Pasta'),
(20, 'Spaghetti Platter', 250.00, 'noodles-pasta', 'menu_img/spaghetti_platter_1779921114.webp', 'Noodles / Pasta'),
(21, 'Carbonara Platter', 280.00, 'noodles-pasta', 'menu_img/carbonara_platter_1779921187.webp', 'Noodles / Pasta'),
(22, 'Palabok Platter', 280.00, 'noodles-pasta', 'img/b8.png', 'Noodles / Pasta'),
(23, 'Bangus Sisig', 215.00, 'seafood', 'img/c1.png', 'Good for 2'),
(24, 'Sinigang na Bangus', 265.00, 'seafood', 'img/c2.png', 'Fish / Seafood Dishes'),
(25, 'Pritong Bangus (Boneless)', 245.00, 'seafood', 'img/c3.png', 'Fish / Seafood Dishes'),
(26, 'Sweet & Sour Fish', 220.00, 'seafood', 'img/c4.png', 'Fish / Seafood Dishes'),
(27, 'Buttered Shrimp', 295.00, 'seafood', 'img/c5.png', 'Fish / Seafood Dishes'),
(28, 'Garlic Buttered Shrimp', 310.00, 'seafood', 'img/c6.png', 'Fish / Seafood Dishes'),
(29, 'Spicy Garlic Buttered Shrimp', 320.00, 'seafood', 'img/c7.png', 'Fish / Seafood Dishes'),
(30, 'Calamares', 285.00, 'seafood', 'img/c8.png', 'Fish / Seafood Dishes'),
(31, 'French Fries', 80.00, 'appetizer-soup', 'img/d1.png', 'Cheese / BBQ / Sour Cream'),
(32, 'Nachos', 120.00, 'appetizer-soup', 'img/d2.png', 'Appetizer'),
(33, 'Cheese Sticks', 95.00, 'appetizer-soup', 'img/d3.png', 'Appetizer'),
(34, 'Onion Rings', 110.00, 'appetizer-soup', 'img/d4.png', 'Appetizer'),
(35, 'Chicken Skin', 145.00, 'appetizer-soup', 'img/d5.png', 'Appetizer'),
(36, 'Mushroom Soup', 120.00, 'appetizer-soup', 'img/d6.png', 'Soup'),
(37, 'Corn Soup', 120.00, 'appetizer-soup', 'img/d7.png', 'Soup'),
(38, 'Nido Soup', 130.00, 'appetizer-soup', 'img/d8.png', 'Soup'),
(39, 'Sinigang na Baboy', 285.00, 'appetizer-soup', 'img/d9.png', 'Soup'),
(40, 'Bulalo', 295.00, 'appetizer-soup', 'img/d10.png', 'Soup'),
(41, 'Leche Flan', 85.00, 'dessert-drinks', 'img/e1.png', 'Dessert'),
(42, 'Buko Pandan', 95.00, 'dessert-drinks', 'img/e2.png', 'Dessert'),
(43, 'Coffee Jelly', 85.00, 'dessert-drinks', 'img/e3.png', 'Dessert'),
(44, 'Halo-Halo (Regular)', 95.00, 'dessert-drinks', 'img/e4.png', 'Dessert'),
(45, 'Halo-Halo (Special)', 120.00, 'dessert-drinks', 'img/e5.png', 'Dessert'),
(46, 'Pitcher Iced Tea / Red Tea', 85.00, 'dessert-drinks', 'img/e6.png', 'Drinks'),
(47, 'Pitcher Blue Lemonade', 95.00, 'dessert-drinks', 'img/e7.png', 'Drinks'),
(48, '1.5L Soda', 90.00, 'dessert-drinks', 'img/e8.png', 'Drinks'),
(49, 'Bottled Water', 25.00, 'dessert-drinks', 'img/e9.png', 'Drinks'),
(50, 'Plain Rice', 25.00, 'dessert-drinks', 'img/e10.png', 'Rice'),
(51, 'Garlic Rice', 35.00, 'dessert-drinks', 'img/e11.png', 'Rice'),
(52, 'Platter Plain Rice', 110.00, 'dessert-drinks', 'img/e12.png', 'Rice Platter'),
(53, 'Platter Garlic Rice', 150.00, 'dessert-drinks', 'img/e13.png', 'Rice Platter'),
(54, 'Barkada Bilao A', 850.00, 'group-meal', 'img/f1.png', 'Pansit, Lumpia, Fried Chicken, Pitcher Drink'),
(55, 'Barkada Bilao B', 1150.00, 'group-meal', 'img/f2.png', 'Pansit, Lumpia, Fried Chicken, Liempo, Pitcher Drink'),
(56, 'Family Bilao A', 1450.00, 'group-meal', 'img/f3.png', 'Pansit, Lumpia, Fried Chicken, Liempo, Sisig, Drink'),
(57, 'Family Bilao B', 1850.00, 'group-meal', 'img/f4.png', 'Pansit, Lumpia, Fried Chicken, Crispy Pata, Sisig, Drinks');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
