-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2026 at 12:03 PM
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
-- Database: `sahara_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('PENDING','PAID','DELIVERED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `shipping_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('ELECTRONICS','FASHION','ACCESSORIES','HOME') DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `is_new` tinyint(1) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `title`, `description`, `price`, `category`, `rating`, `is_new`, `stock`, `image`, `created_at`, `updated_at`) VALUES
(1, 4, 'Arch Linux', 'Arch Linux', 200.00, 'ELECTRONICS', 0.0, 1, 100, '/uploads/products/product_696f35e4e559e0.61833828.png', '2026-01-17 20:20:58', '2026-01-20 07:59:32'),
(8, 4, 'GTA VI', 'Grand Theft Auto 6', 10000.00, 'HOME', 0.0, 1, 50, '/uploads/products/product_696f37131734e5.58701338.jpg', '2026-01-20 08:04:35', '2026-01-20 08:04:35'),
(10, 4, 'Sample', 'Sample', 50.00, 'ACCESSORIES', 0.0, 0, 5, '/uploads/products/product_696f3825651d89.32057992.jpg', '2026-01-20 08:08:42', '2026-01-20 08:09:09'),
(11, 4, 'Sample Out of Stock', 'Sample Out of Stock', 50000.00, 'FASHION', 0.0, 0, 1, '', '2026-01-20 08:12:26', '2026-01-20 08:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 4, 'e2cdb2a2c4c7d3e7cf7c309d56bcc248d08f270450c42fba9d1ebcccd503c3d7', '2026-02-15 10:42:20', '2026-01-16 15:42:20'),
(2, 4, '5da69b163b7295298e2398ab01681f25dbcde966e813a6c2105d5efde3dc8307', '2026-02-15 10:44:03', '2026-01-16 15:44:03'),
(3, 4, '2a12f416dcd8598b4dd159e07e01db3a5f309659ce8431374eceb8d813325297', '2026-02-15 10:47:02', '2026-01-16 15:47:02'),
(4, 4, 'f8b8694035a54dd7c9db1d13615c2315d7b8a110ca2cd0819e85fe59f6cb2c22', '2026-02-15 10:47:28', '2026-01-16 15:47:28'),
(5, 4, '5c04e68888d8977beea23c94cf048ec6acc75a41107c8bdced9493fe044a1f4a', '2026-02-15 10:48:20', '2026-01-16 15:48:20'),
(6, 4, 'a120326fa3c0c713d6039d3674740350af8441197710084416846335a5f9202a', '2026-02-15 10:49:13', '2026-01-16 15:49:13'),
(7, 4, '58bf11ecfb2015db889a891f71e24dcde4c4935c26225b43c6ebd8baf8a47869', '2026-02-15 10:50:12', '2026-01-16 15:50:12'),
(8, 5, '20f6e7b713b3aba0336083708025a5bbff33a6639431e5c40338a947aa2d074b', '2026-02-15 11:02:11', '2026-01-16 16:02:11'),
(9, 5, '4832aa1c55b2ca7d1aac3f56602489751e37f48897039e9e1724200e81c69c7d', '2026-02-15 11:13:30', '2026-01-16 16:13:30'),
(10, 4, 'b8793e0985e72e1af2eab47a670ee1fe9f29b2d9d918c52589606ee4cf488173', '2026-02-16 14:43:08', '2026-01-17 19:43:08'),
(11, 4, 'ff377c78061152a5ab61427932c83943368039424d0ca1f573aa2056173af7ab', '2026-02-16 15:03:27', '2026-01-17 20:03:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('CUSTOMER','SELLER','ADMIN') NOT NULL DEFAULT 'CUSTOMER',
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `last_login`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'iftakhar.awal@gmail.com', '$2y$10$kuLtKc2qTj73l6NpMoYWreeL/7gKCWvQXfNVn5bIOoUnI1nDc8COW', 'ADMIN', '2026-01-17 19:43:08', 1, '2026-01-16 09:37:39', '2026-01-17 19:43:08'),
(5, 'john.doe@hotmail.com', '$2y$10$pOixSKnfj7TMfvBAY9glduIF/rYCF2oPBme75ZkpfSmeI8wMzW87i', 'CUSTOMER', '2026-01-16 19:26:57', 1, '2026-01-16 16:01:43', '2026-01-16 19:26:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `gender` enum('MALE','FEMALE','OTHER') NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT 0,
  `picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `first_name`, `last_name`, `phone`, `gender`, `address`, `newsletter`, `picture`) VALUES
(4, 'Atif', 'Chowdhury', '+8801834703212', 'MALE', NULL, 1, NULL),
(5, 'John', 'Doe', '+88012345678', 'MALE', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
