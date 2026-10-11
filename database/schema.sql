-- ==============================================================================
-- Core Inventory Management System - Database Schema & Seed Data
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `core_inventory`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `core_inventory`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `stock_transactions`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `users`;

-- Table: users
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: categories
CREATE TABLE `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: suppliers
CREATE TABLE `suppliers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `contact_person` VARCHAR(100) NULL,
    `email` VARCHAR(100) NULL,
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: products
CREATE TABLE `products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sku` VARCHAR(50) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `category_id` INT UNSIGNED NULL,
    `supplier_id` INT UNSIGNED NULL,
    `cost_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `quantity` INT NOT NULL DEFAULT 0,
    `min_threshold` INT NOT NULL DEFAULT 5,
    `unit_of_measure` VARCHAR(30) NOT NULL DEFAULT 'pcs',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_products_sku` (`sku`),
    KEY `idx_products_category` (`category_id`),
    KEY `idx_products_supplier` (`supplier_id`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`)
        REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: stock_transactions
CREATE TABLE `stock_transactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `type` ENUM('IN', 'OUT') NOT NULL,
    `quantity` INT NOT NULL,
    `balance_before` INT NOT NULL,
    `balance_after` INT NOT NULL,
    `reason` VARCHAR(100) NOT NULL DEFAULT 'Restock',
    `reference_no` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_stock_product` (`product_id`),
    KEY `idx_stock_user` (`user_id`),
    KEY `idx_stock_type` (`type`),
    KEY `idx_stock_created` (`created_at`),
    CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_stock_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed Users (Passwords: admin123, staff123)
INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin', 'admin@inventory.local', '$2y$10$jerlWg/wBTpEkokTcFFaWOR/tngSOLBywp0sxl/4znOvrBINYkk6S', 'admin', 'active'),
(2, 'Warehouse Operator', 'staff', 'staff@inventory.local', '$2y$10$GcekPCOpUdDiGgRPphfmpeIJUoU3ub7ipyq1sifoEa0cb2rgHmIle', 'staff', 'active');

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Electronics', 'Electronic devices, modules, sensors, and gadgets'),
(2, 'Office Supplies', 'Everyday stationery, paper, pens, and desk equipment'),
(3, 'Packaging Materials', 'Cardboard boxes, bubble wrap, tapes, and shipping bags'),
(4, 'Computer Peripherals', 'Keyboards, mice, monitors, cables, and adaptors');

-- Seed Suppliers
INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`) VALUES
(1, 'TechSource Global', 'Marcus Vance', 'contact@techsource.com', '+1 (555) 234-5678', '404 Silicon Way, San Jose, CA'),
(2, 'Apex Office Essentials', 'Sarah Jenkins', 'sales@apexoffice.com', '+1 (555) 876-5432', '120 Commerce Blvd, Austin, TX'),
(3, 'Swift Logistics & Packaging', 'David Lee', 'orders@swiftpack.com', '+1 (555) 432-1098', '88 Freight Depot Rd, Chicago, IL');

-- Seed Products
INSERT INTO `products` (`id`, `sku`, `name`, `description`, `category_id`, `supplier_id`, `cost_price`, `unit_price`, `quantity`, `min_threshold`, `unit_of_measure`) VALUES
(1, 'SKU-ELEC-001', 'Wireless Ergonomic Mouse', '2.4GHz rechargeable wireless optical mouse with USB-C dongle', 4, 1, 12.50, 29.99, 45, 10, 'pcs'),
(2, 'SKU-ELEC-002', 'Mechanical Keyboard (RGB)', 'Tenkeyless blue-switch gaming and typing mechanical keyboard', 4, 1, 35.00, 69.99, 18, 5, 'pcs'),
(3, 'SKU-OFF-001', 'Heavy Duty Stapler', 'Desktop metal stapler up to 100 sheets capacity', 2, 2, 8.20, 16.50, 4, 10, 'pcs'),
(4, 'SKU-OFF-002', 'A4 Premium Copy Paper (Box)', '5 reams per carton, 80 GSM bright white photocopy paper', 2, 2, 18.00, 26.00, 2, 8, 'box'),
(5, 'SKU-PKG-001', 'Cardboard Shipping Box (Medium)', '12x10x8 inch corrugated shipping mailers, bundle of 25', 3, 3, 14.50, 22.00, 60, 15, 'bundle'),
(6, 'SKU-ACC-001', 'USB-C Multiport Hub 7-in-1', 'Aluminum adapter with 4K HDMI, 3 USB 3.0 ports, SD card reader', 1, 1, 22.00, 45.00, 0, 5, 'pcs');

-- Seed Initial Stock Transactions
INSERT INTO `stock_transactions` (`product_id`, `user_id`, `type`, `quantity`, `balance_before`, `balance_after`, `reason`, `reference_no`, `notes`) VALUES
(1, 1, 'IN', 45, 0, 45, 'Initial Stock', 'INIT-2026-001', 'Initial warehouse stock count entry'),
(2, 1, 'IN', 18, 0, 18, 'Initial Stock', 'INIT-2026-002', 'Initial warehouse stock count entry'),
(3, 1, 'IN', 4, 0, 4, 'Initial Stock', 'INIT-2026-003', 'Initial warehouse stock count entry (Low stock alert item)'),
(4, 1, 'IN', 2, 0, 2, 'Initial Stock', 'INIT-2026-004', 'Initial warehouse stock count entry (Low stock alert item)'),
(5, 1, 'IN', 60, 0, 60, 'Initial Stock', 'INIT-2026-005', 'Initial warehouse stock count entry');
