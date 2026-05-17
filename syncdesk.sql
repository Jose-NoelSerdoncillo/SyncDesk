
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `syncdesk`;
USE `syncdesk`;

-- --------------------------------------------------------
-- Table: warehouses
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `warehouses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `warehouses` (`name`, `location`) VALUES
('Warehouse A', 'Building 1, Zone A'),
('Warehouse B', 'Building 2, Zone B'),
('Warehouse C', 'Building 3, Zone C');

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`name`, `description`) VALUES
('Electronics', 'Electronic devices and components'),
('Gadgets', 'Gadgets and accessories'),
('Office Supplies', 'Office and desk supplies'),
('Furniture', 'Office and warehouse furniture'),
('Tools', 'Hardware and tools');

-- --------------------------------------------------------
-- Table: suppliers
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `suppliers` (`name`, `contact`, `email`) VALUES
('Supplier X', '+63 912 000 0001', 'supplierx@email.com'),
('Supplier Y', '+63 912 000 0002', 'suppliery@email.com'),
('Supplier Z', '+63 912 000 0003', 'supplierz@email.com');

-- --------------------------------------------------------
-- Table: products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `warehouse_location` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 50,
  `supplier` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_sku` (`sku`),
  INDEX `idx_category` (`category`),
  INDEX `idx_stock` (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`sku`, `product_name`, `category`, `warehouse_location`, `stock`, `low_stock_threshold`, `supplier`) VALUES
('SKU-001', 'Wireless Mouse', 'Electronics', 'W1-A1', 120, 50, 'Supplier X'),
('SKU-002', 'USB-C Hub', 'Electronics', 'W1-A2', 30, 50, 'Supplier X'),
('SKU-003', 'Mechanical Keyboard', 'Electronics', 'W1-B1', 75, 50, 'Supplier Y'),
('SKU-004', 'Monitor Stand', 'Furniture', 'W2-A1', 45, 50, 'Supplier Z'),
('SKU-005', 'Desk Lamp', 'Gadgets', 'W2-B1', 18, 50, 'Supplier Y'),
('SKU-006', 'Notebook Set', 'Office Supplies', 'W3-A1', 200, 50, 'Supplier X'),
('SKU-007', 'Stapler', 'Office Supplies', 'W3-A2', 8, 50, 'Supplier Z'),
('SKU-008', 'WIDGET X', 'Gadgets', 'W2-S5', 85, 50, 'Supplier Y');

-- --------------------------------------------------------
-- Table: logs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `user` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `logs` (`product_id`, `action`, `details`, `user`) VALUES
(1, 'Added product', 'SKU: SKU-001, Name: Wireless Mouse, Stock: 120', 'admin'),
(2, 'Stock updated', 'SKU: SKU-002, Stock changed from 80 to 30', 'admin'),
(7, 'Low stock alert', 'SKU: SKU-007, Stock dropped to 8', 'system');

COMMIT;
