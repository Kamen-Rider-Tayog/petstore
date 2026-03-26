-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 04:52 PM
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
-- Database: `pet_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Philippines',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `customer_id`, `address_line1`, `address_line2`, `city`, `state`, `zip_code`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, '123 Maple Street, Springfield', NULL, '', NULL, NULL, 'Philippines', 1, '2026-03-22 16:28:17', '2026-03-22 16:28:17'),
(2, 2, '456 Oak Avenue, Springfield', NULL, '', NULL, NULL, 'Philippines', 1, '2026-03-22 16:28:17', '2026-03-22 16:28:17'),
(3, 3, '789 Pine Road, Springfield', NULL, '', NULL, NULL, 'Philippines', 1, '2026-03-22 16:28:17', '2026-03-22 16:28:17'),
(4, 4, '321 Elm Street, Springfield', NULL, '', NULL, NULL, 'Philippines', 1, '2026-03-22 16:28:17', '2026-03-22 16:28:17'),
(8, 5, '', '', '', '', '', 'Philippines', 1, '2026-03-22 16:29:56', '2026-03-22 16:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `customer_id`, `pet_id`, `employee_id`, `appointment_date`, `service_type`, `duration_minutes`, `status`) VALUES
(1, 1, 1, 1, '2024-03-05 10:00:00', 'grooming', 60, 'pending'),
(2, 2, 2, 2, '2024-03-05 14:00:00', 'checkup', 30, 'pending'),
(3, 3, 3, 1, '2024-03-06 11:30:00', 'nail trim', 20, 'pending'),
(5, 5, 3, 1, '2026-03-30 23:35:00', 'Grooming', 0, 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `product_id`, `quantity`, `added_date`) VALUES
(1, 5, 8, 1, '2026-03-22 10:08:58');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `parent_id`) VALUES
(1, 'Food', NULL),
(2, 'Accessories', NULL),
(3, 'Toys', NULL),
(4, 'Housing', NULL),
(5, 'Health', NULL),
(6, 'Dog Food', 1),
(7, 'Cat Food', 1),
(8, 'Bird Food', 1),
(9, 'Fish Food', 1),
(10, 'Leashes', 2),
(11, 'Collars', 2),
(12, 'Beds', 2),
(13, 'Plush Toys', 3),
(14, 'Interactive Toys', 3),
(15, 'Chew Toys', 3),
(16, 'Cages', 4),
(17, 'Tanks', 4),
(18, 'Bowls', 4),
(19, 'Medications', 5),
(20, 'Supplements', 5);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `ip_address`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'john.doe@email.com', 'Product Question', 'I have a question about the premium dog food. Is it suitable for puppies?', '192.168.1.100', 1, '2026-03-15 13:29:00', '2026-03-15 13:29:00'),
(2, 'Jane Smith', 'jane.smith@email.com', 'General Inquiry', 'I would like to know more about your grooming services and pricing.', '192.168.1.101', 0, '2026-03-15 13:29:00', '2026-03-15 13:29:00'),
(3, 'Mike Johnson', 'mike.j@email.com', 'Order Support', 'I placed an order yesterday but haven\'t received a confirmation email. Order #12345', '192.168.1.102', 0, '2026-03-15 13:29:00', '2026-03-15 13:29:00'),
(4, 'John Doe', 'john.doe@email.com', 'Product Question', 'I have a question about the premium dog food. Is it suitable for puppies?', '192.168.1.100', 1, '2026-03-15 13:29:13', '2026-03-15 13:29:13'),
(5, 'Jane Smith', 'jane.smith@email.com', 'General Inquiry', 'I would like to know more about your grooming services and pricing.', '192.168.1.101', 0, '2026-03-15 13:29:13', '2026-03-15 13:29:13'),
(6, 'Mike Johnson', 'mike.j@email.com', 'Order Support', 'I placed an order yesterday but haven\'t received a confirmation email. Order #12345', '192.168.1.102', 0, '2026-03-15 13:29:13', '2026-03-15 13:29:13');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Emma', 'Wilson', 'emma@email.com', '555-9876', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(2, 'David', 'Brown', 'david@email.com', '555-5678', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(3, 'Jessica', 'Taylor', 'jessica@email.com', '555-4321', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(4, 'Robert', 'Garcia', 'robert@email.com', '555-8765', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(5, 'Ria', 'Basallo', 'riabasallo@email.com', '09053351213', '$2y$10$x3RsnYQnVMge/6h0brSA3eugxMcG04BeDTg03z/85KfFsvL5RqAfa', '2026-03-21 17:38:23', '2026-03-22 16:38:34');

-- --------------------------------------------------------

--
-- Table structure for table `customer_pets`
--

CREATE TABLE `customer_pets` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `species` varchar(50) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `weight_unit` varchar(10) DEFAULT 'kg',
  `microchip_id` varchar(50) DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_pets`
--

INSERT INTO `customer_pets` (`id`, `customer_id`, `name`, `species`, `breed`, `age`, `gender`, `color`, `weight`, `weight_unit`, `microchip_id`, `medical_notes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Max', 'dog', NULL, 2, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(2, 1, 'Luna', 'cat', NULL, 3, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(3, 1, 'Coco', 'rabbit', NULL, 4, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(4, 1, 'Kiwi', 'bird', NULL, 2, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(5, 1, 'Charlie', 'hamster', NULL, 1, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(6, 1, 'Charlie', 'dog', 'Beagle', 3, 'male', 'Tri-color', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(7, 1, 'Bella', 'dog', 'French Bulldog', 2, 'female', 'Fawn', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(8, 1, 'Rocky', 'dog', 'German Shepherd', 4, 'male', 'Black and Tan', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(9, 1, 'Luna', 'dog', 'Siberian Husky', 1, 'female', 'Gray and White', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(10, 1, 'Cooper', 'dog', 'Golden Retriever', 5, 'male', 'Golden', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(11, 1, 'Oliver', 'cat', 'Maine Coon', 3, 'male', 'Brown Tabby', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(12, 1, 'Chloe', 'cat', 'Persian', 2, 'female', 'White', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(13, 1, 'Simba', 'cat', 'Bengal', 1, 'male', 'Spotted', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(14, 1, 'Milo', 'cat', 'Siamese', 4, 'male', 'Seal Point', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(15, 1, 'Nala', 'cat', 'Ragdoll', 2, 'female', 'Seal Lynx', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(16, 1, 'Mango', 'bird', 'Sun Conure', 1, 'male', 'Yellow/Orange', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(17, 1, 'Blue', 'bird', 'Blue Jay', 2, 'female', 'Blue', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(18, 1, 'Kiwi', 'bird', 'Budgie', 1, 'female', 'Green', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(19, 1, 'Rio', 'bird', 'Macaw', 5, 'male', 'Blue and Gold', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(20, 1, 'Thumper', 'rabbit', 'Holland Lop', 1, 'male', 'Chocolate', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(21, 1, 'Daisy', 'rabbit', 'Rex', 2, 'female', 'White', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(22, 1, 'Oreo', 'rabbit', 'Dutch', 1, 'female', 'Black and White', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(23, 1, 'Peanut', 'hamster', 'Syrian', 0, 'male', 'Golden', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(24, 1, 'Squeaky', 'guinea pig', 'Abyssinian', 1, 'female', 'Brown/White', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(25, 1, 'Gizmo', 'ferret', 'Sable', 2, 'male', 'Dark Brown', NULL, 'kg', NULL, NULL, 1, '2026-03-24 16:25:27', '2026-03-24 16:25:27'),
(26, 5, 'Megatron', 'cat', 'Puspin', 1, '', 'Black', 67.00, 'kg', NULL, NULL, 1, '2026-03-26 15:34:54', '2026-03-26 15:34:54'),
(27, 5, 'Maui', 'cat', 'Puspin', 0, 'female', 'Black and gray', 5.00, 'kg', NULL, NULL, 1, '2026-03-26 15:44:26', '2026-03-26 15:44:26'),
(28, 1, 'Buddy', 'dog', 'Golden Retriever', 3, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-26 15:48:26', '2026-03-26 15:48:26'),
(29, 1, 'Whiskers', 'cat', 'Siamese', 2, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-26 15:48:26', '2026-03-26 15:48:26'),
(30, 2, 'Tweety', 'bird', 'Parakeet', 1, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-26 15:48:26', '2026-03-26 15:48:26'),
(31, 5, 'Fluffy', 'rabbit', 'Holland Lop', 1, NULL, NULL, NULL, 'kg', NULL, NULL, 1, '2026-03-26 15:48:26', '2026-03-26 15:48:26');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `hourly_wage` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `email`, `password`, `position`, `phone`, `address`, `hire_date`, `is_admin`, `notes`, `hourly_wage`, `created_at`, `updated_at`) VALUES
(1, 'Sarah', 'Johnson', 'admin@petstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', '555-1234', '123 Main St, City, State', '2023-01-15', 1, 'Store Manager and Administrator', 25.50, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(2, 'Mike', 'Chen', 'mike@petstore.com', NULL, 'Cashier', '555-5678', '456 Oak Ave, City, State', '2023-03-20', 0, 'Experienced cashier', 16.50, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(3, 'Lisa', 'Rodriguez', 'lisa@petstore.com', NULL, 'Groomer', '555-9012', '789 Pine Rd, City, State', '2023-02-10', 0, 'Certified pet groomer', 18.25, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(4, 'James', 'Wilson', 'james@petstore.com', NULL, 'Sales Associate', '555-3456', '321 Elm St, City, State', '2023-04-05', 0, 'New sales associate', 14.50, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(5, 'Super', 'Admin', 'superadmin@petstore.com', '$2y$10$cNQdD45B8pFKY3lZ0mE9A.wxKs3/nRQKDPTbxidmP2Fbrssm/fKXK', 'Super Administrator', '555-0000', 'Super Admin Office', '2024-01-01', 1, 'Super Administrator with full system access', 0.00, '2024-01-01 00:00:00', '2024-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `category`, `question`, `answer`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'General', 'Do you offer grooming services?', 'Yes! We provide professional grooming services for dogs and cats. Our certified groomers use only the highest quality products and techniques.', 1, 1, '2026-03-15 14:49:46', '2026-03-15 14:49:46'),
(2, 'General', 'Can I return products?', 'We offer a 30-day return policy on most products. Items must be unused and in their original packaging. Please contact us for return instructions.', 2, 1, '2026-03-15 14:49:46', '2026-03-15 14:49:46'),
(3, 'General', 'Do you have a veterinary clinic?', 'Yes, our on-site veterinary clinic is staffed by licensed veterinarians. We offer wellness exams, vaccinations, and emergency care.', 3, 1, '2026-03-15 14:49:46', '2026-03-15 14:49:46'),
(4, 'Orders', 'How long does shipping take?', 'Standard shipping takes 3-5 business days. Express shipping is available for 1-2 business days. Free shipping on orders over .', 1, 1, '2026-03-15 14:49:46', '2026-03-15 14:49:46'),
(5, 'Orders', 'Can I track my order?', 'Yes, you will receive a tracking number via email once your order ships. You can also view your order status in your account dashboard.', 2, 1, '2026-03-15 14:49:46', '2026-03-15 14:49:46'),
(6, 'Services', 'Do you offer boarding services?', 'Yes, we provide safe and comfortable boarding for dogs and cats. Our facility includes climate-controlled rooms, daily exercise, and 24/7 supervision.', 1, 1, '2026-03-15 14:49:46', '2026-03-15 14:49:46'),
(7, 'Services', 'What vaccinations do you require?', 'We require current vaccinations including Rabies, DHPP, and Bordetella for dogs, and FVRCP for cats. Please bring vaccination records when visiting.', 2, 1, '2026-03-15 14:49:46', '2026-03-15 14:49:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `quantity_in_stock` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `weight_unit` varchar(10) DEFAULT NULL,
  `supplier` int(11) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 0.0,
  `review_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `on_sale` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `brand`, `description`, `product_image`, `quantity_in_stock`, `price`, `sale_price`, `weight`, `weight_unit`, `supplier`, `featured`, `rating`, `review_count`, `created_at`, `on_sale`) VALUES
(1, 'Dog Food - Premium', 'food', 'Pedigree', 'Premium dog food for adult dogs', NULL, 25, 45.99, NULL, 5.00, 'kg', 1, 1, 0.0, 0, '2026-03-15 13:21:34', 0),
(2, 'Cat Food - Wet', 'food', 'Whiskas', 'Wet cat food in gravy', NULL, 40, 2.50, NULL, 400.00, 'g', 1, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(3, 'Dog Leash', 'accessories', NULL, NULL, NULL, 15, 12.99, NULL, NULL, NULL, 2, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(4, 'Cat Toy - Mouse', 'Toys', NULL, NULL, NULL, 60, 3.25, NULL, NULL, NULL, 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(5, 'Fish Tank Filter', 'equipment', NULL, NULL, NULL, 8, 35.50, NULL, NULL, NULL, 4, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(6, 'Hamster Wheel', 'accessories', NULL, NULL, NULL, 12, 8.75, NULL, NULL, NULL, 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(7, 'Bird Cage - Small', 'housing', NULL, NULL, NULL, 5, 45.00, NULL, NULL, NULL, 2, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(8, 'Dog Bed - Large', 'accessories', NULL, NULL, NULL, 3, 65.00, NULL, NULL, NULL, 2, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(9, 'Organic Dog Food', 'Dog Food', 'Organic Paws', 'Organic, grain-free dog food', NULL, 20, 65.99, NULL, 3.00, 'kg', 1, 1, 0.0, 0, '2026-03-15 13:21:34', 0),
(10, 'Cat Scratcher Post', 'Accessories', 'Kitty Fun', 'Sisal scratching post for cats', NULL, 15, 25.99, NULL, 2.00, 'kg', 2, 1, 0.0, 0, '2026-03-15 13:21:34', 0),
(11, 'Bird Swing', 'Toys', 'Parrot Paradise', 'Wooden swing for birds', NULL, 30, 12.50, NULL, 0.50, 'kg', 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(12, 'Hamster Exercise Ball', 'Accessories', 'FunPets', 'Clear exercise ball for hamsters', NULL, 25, 8.99, NULL, 0.20, 'kg', 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(13, 'Fish Tank Heater', 'Tanks', 'AquaWorld', 'Submersible aquarium heater', NULL, 10, 32.99, NULL, 0.30, 'kg', 4, 1, 0.0, 0, '2026-03-15 13:21:34', 0),
(14, 'Premium Adult Dog Food - 2kg', 'Dog Food', 'Royal Canin', 'Complete nutrition for adult dogs with balanced vitamins and minerals', NULL, 45, 550.00, NULL, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 0),
(15, 'Puppy Formula Dog Food - 1.5kg', 'Dog Food', 'Pedigree', 'Specially formulated for growing puppies', NULL, 60, 320.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(16, 'Dog Dental Chews - 30 pcs', 'Dog Treats', 'Greenies', 'Helps clean teeth and freshen breath', NULL, 100, 280.00, 230.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(17, 'Dog Harness - Medium', 'Accessories', 'PetSafe', 'Comfortable and adjustable harness for medium dogs', NULL, 35, 450.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(18, 'Dog Bed - Orthopedic', 'Bedding', 'Furhaven', 'Memory foam bed for senior dogs', NULL, 20, 1200.00, 999.00, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 1),
(19, 'Dog Shampoo - 500ml', 'Grooming', 'Earthbath', 'All-natural oatmeal shampoo for sensitive skin', NULL, 50, 380.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(20, 'Dog Nail Clipper', 'Grooming', 'Safari', 'Professional grade nail clipper for all dog sizes', NULL, 75, 150.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(21, 'Collapsible Dog Bowl', 'Accessories', 'Kurgo', 'Portable silicone bowl for travel', NULL, 90, 180.00, 150.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(22, 'Dog Life Jacket - Small', 'Safety', 'Outward Hound', 'Buoyant vest for water activities', NULL, 25, 650.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(23, 'Dog Raincoat - Adjustable', 'Apparel', 'Pawz', 'Waterproof raincoat with hood', NULL, 40, 320.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(24, 'Adult Cat Food - 1.5kg', 'Cat Food', 'Whiskas', 'Complete nutrition for adult cats', NULL, 55, 280.00, NULL, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 0),
(25, 'Kitten Formula - 1kg', 'Cat Food', 'Royal Canin', 'Specially formulated for growing kittens', NULL, 40, 350.00, 299.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(26, 'Cat Scratching Post', 'Accessories', 'Petmate', 'Sisal rope scratching post with platform', NULL, 30, 450.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(27, 'Cat Litter - 10L', 'Litter', 'Catsan', 'Clumping litter with odor control', NULL, 80, 320.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(28, 'Cat Tunnel Toy', 'Toys', 'Petstages', 'Collapsible tunnel for play and exercise', NULL, 45, 280.00, 220.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(29, 'Laser Pointer Cat Toy', 'Toys', 'PetSafe', 'Interactive laser toy for exercise', NULL, 120, 150.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(30, 'Cat Bed - Donut', 'Bedding', 'Best Friends', 'Cozy donut-shaped bed for cats', NULL, 35, 420.00, NULL, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 0),
(31, 'Cat Brush - De-shedding', 'Grooming', 'Furminator', 'Removes loose hair and reduces shedding', NULL, 50, 280.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(32, 'Bird Cage - Large', 'Housing', 'Prevue', 'Spacious cage for medium to large birds', NULL, 15, 2800.00, 2500.00, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 1),
(33, 'Bird Perch - Natural Wood', 'Accessories', 'JW Pet', 'Natural wood perch for bird health', NULL, 60, 180.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(34, 'Bird Food - Premium Mix', 'Food', 'Zupreem', 'Nutritional blend for parrots and parakeets', NULL, 45, 380.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(35, 'Bird Toy - Bell', 'Toys', 'Super Bird', 'Colorful bell toy for mental stimulation', NULL, 85, 120.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(36, 'Bird Swing', 'Toys', 'Paradise', 'Wooden swing for birds to perch and play', NULL, 50, 150.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(37, 'Aquarium Filter', 'Equipment', 'AquaClear', 'Power filter for 20-50 gallon tanks', NULL, 30, 950.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(38, 'Fish Food - 100g', 'Food', 'Tetra', 'Nutritional flakes for tropical fish', NULL, 100, 120.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(39, 'Aquarium Heater', 'Equipment', 'Eheim', 'Submersible heater for consistent temperature', NULL, 25, 450.00, 380.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(40, 'Aquarium Decor - Plant Set', 'Decor', 'Penn-Plax', 'Set of artificial aquarium plants', NULL, 40, 280.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(41, 'Water Conditioner - 250ml', 'Supplies', 'Seachem', 'Removes chlorine and heavy metals', NULL, 70, 180.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(42, 'Hamster Cage - Large', 'Housing', 'Kaytee', 'Spacious cage with tubes and tunnels', NULL, 20, 1800.00, 1500.00, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 1),
(43, 'Hamster Food - 1kg', 'Food', 'Oxbow', 'Nutritional pellets for hamsters', NULL, 45, 220.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(44, 'Hamster Wheel - Silent', 'Accessories', 'Ware', 'Quiet exercise wheel', NULL, 35, 280.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(45, 'Rabbit Hay - 1kg', 'Food', 'Oxbow', 'Timothy hay for digestive health', NULL, 40, 180.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(46, 'Rabbit Hutch - Outdoor', 'Housing', 'Trixie', 'Weather-resistant outdoor rabbit hutch', NULL, 10, 3500.00, 2999.00, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 1),
(47, 'Guinea Pig Cage Liner', 'Bedding', 'GuineaDad', 'Washable fleece cage liner', NULL, 25, 450.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(48, 'Pet Grooming Kit', 'Grooming', 'Wahl', 'Complete grooming kit with clippers and blades', NULL, 20, 2200.00, 1899.00, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 1),
(49, 'Pet Nail Grinder', 'Grooming', 'Dremel', 'Electric nail grinder for safe trimming', NULL, 30, 650.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(50, 'Pet Hair Remover', 'Grooming', 'ChomChom', 'Reusable roller for pet hair removal', NULL, 60, 380.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(51, 'Pet Toothbrush Set', 'Grooming', 'Vet\'s Best', 'Dual-headed toothbrush with toothpaste', NULL, 80, 220.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(52, 'Flea & Tick Treatment', 'Health', 'Frontline', 'Spot-on treatment for dogs', NULL, 45, 480.00, 399.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(53, 'Multivitamin for Dogs', 'Supplements', 'Pet Naturals', 'Daily multivitamin chews', NULL, 50, 320.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(54, 'Joint Supplement for Dogs', 'Supplements', 'Cosequin', 'Supports joint health and mobility', NULL, 40, 580.00, NULL, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 0),
(55, 'Pet First Aid Kit', 'Health', 'Adventure', 'Complete first aid kit for pets', NULL, 35, 450.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(56, 'Pet Carrier - Soft Sided', 'Travel', 'Pet Gear', 'Comfortable carrier for small pets', NULL, 25, 890.00, 750.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(57, 'Pet Stroller', 'Travel', 'Pet Gear', 'Stroller for small dogs and cats', NULL, 12, 3200.00, 2800.00, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 1),
(58, 'Car Seat Cover', 'Travel', 'Kurgo', 'Protects car seats from pet hair and scratches', NULL, 30, 650.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(59, 'Pet Travel Bowl', 'Travel', 'Outward Hound', 'Collapsible silicone bowl for travel', NULL, 80, 120.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(60, 'Squeaky Toy Set', 'Toys', 'KONG', 'Set of 3 squeaky plush toys', NULL, 70, 280.00, 220.00, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 1),
(61, 'Rope Tug Toy', 'Toys', 'KONG', 'Durable rope toy for interactive play', NULL, 60, 150.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(62, 'Treat Dispensing Ball', 'Toys', 'KONG', 'Ball that dispenses treats as it rolls', NULL, 45, 380.00, NULL, NULL, NULL, NULL, 1, 0.0, 0, '2026-03-24 14:21:44', 0),
(63, 'Feather Wand Cat Toy', 'Toys', 'GoCat', 'Interactive wand toy with feathers', NULL, 55, 180.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0),
(64, 'Crinkle Cat Tunnel', 'Toys', 'Petstages', 'Crinkle tunnel for cats to hide and play', NULL, 40, 280.00, NULL, NULL, NULL, NULL, 0, 0.0, 0, '2026-03-24 14:21:44', 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT NULL,
  `sale_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `customer_id`, `employee_id`, `quantity_sold`, `sale_date`) VALUES
(1, 4, 1, 2, 2, '2024-02-20'),
(2, 8, 2, 1, 1, '2024-02-21'),
(3, 7, 3, 1, 1, '2024-02-22'),
(4, 2, 3, 1, 3, '2024-02-22'),
(5, 5, 1, 3, 1, '2024-02-23'),
(6, 1, 4, 1, 1, '2024-02-25'),
(7, 5, 1, 3, 1, '2024-02-26');

-- --------------------------------------------------------

--
-- Table structure for table `search_log`
--

CREATE TABLE `search_log` (
  `id` int(11) NOT NULL,
  `search_term` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `min_price` decimal(10,2) DEFAULT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `results_count` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `search_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `category`, `description`, `price`, `duration_minutes`, `featured`, `created_at`, `updated_at`) VALUES
(1, 'Grooming', 'grooming', 'Full grooming service including bath, haircut, nail trimming, and ear cleaning', 50.00, 120, 1, '2026-03-15 14:19:53', '2026-03-15 14:19:53'),
(2, 'Veterinary Checkup', 'medical', 'Comprehensive health checkup by our experienced veterinarians', 75.00, 60, 1, '2026-03-15 14:19:53', '2026-03-15 14:19:53'),
(3, 'Training Session', 'training', 'Basic obedience training with professional trainers', 40.00, 45, 0, '2026-03-15 14:19:53', '2026-03-15 14:19:53'),
(4, 'Boarding', 'boarding', 'Overnight care for your pets in comfortable facilities', 60.00, 1440, 0, '2026-03-15 14:19:53', '2026-03-15 14:19:53'),
(5, 'Day Care', 'boarding', 'Supervised play and care during the day', 30.00, 480, 0, '2026-03-15 14:19:53', '2026-03-15 14:19:53'),
(6, 'Dental Cleaning', 'medical', 'Professional dental cleaning and oral health check', 90.00, 90, 1, '2026-03-15 14:19:53', '2026-03-15 14:19:53'),
(7, 'Nail Trim', 'grooming', 'Quick nail trimming service', 15.00, 20, 0, '2026-03-15 14:19:53', '2026-03-15 14:19:53'),
(8, 'Vaccination', 'medical', 'Essential vaccinations for your pet', 45.00, 30, 0, '2026-03-15 14:19:53', '2026-03-15 14:19:53');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'store_name', 'Pet Store', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(2, 'store_email', 'info@petstore.com', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(3, 'tax_rate', '0', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(4, 'currency', 'PHP', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(5, 'low_stock_threshold', '10', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(6, 'max_upload_size', '5', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(7, 'timezone', 'Asia/Manila', '2024-01-01 00:00:00', '2024-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `store_pets`
--

CREATE TABLE `store_pets` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `species` varchar(30) DEFAULT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pet_status` enum('available','sold','reserved','adopted') DEFAULT 'available',
  `pet_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `featured` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_pets`
--

INSERT INTO `store_pets` (`id`, `customer_id`, `name`, `species`, `breed`, `color`, `age`, `gender`, `description`, `pet_status`, `pet_image`, `created_at`, `updated_at`, `featured`, `is_available`) VALUES
(1, 1, 'Max', 'dog', NULL, NULL, 2, NULL, 'Friendly golden retriever, great with kids and other pets.', 'available', NULL, '2024-01-01 00:00:00', '2026-03-24 16:08:41', 0, 1),
(2, 1, 'Luna', 'cat', NULL, NULL, 3, NULL, 'Playful tabby cat, loves to cuddle and play with toys.', 'available', NULL, '2024-01-01 00:00:00', '2026-03-24 16:08:41', 1, 1),
(3, 1, 'Coco', 'rabbit', NULL, NULL, 4, NULL, 'Gentle lop-eared rabbit, perfect for first-time owners.', 'available', NULL, '2024-01-01 00:00:00', '2026-03-24 16:36:54', 0, 1),
(4, 1, 'Kiwi', 'bird', NULL, NULL, 2, NULL, 'Colorful parakeet, sings beautifully and loves attention.', 'available', NULL, '2024-01-01 00:00:00', '2026-03-24 16:08:41', 0, 1),
(5, 1, 'Charlie', 'hamster', NULL, NULL, 1, NULL, 'Adorable teddy bear hamster, very active and friendly.', 'available', NULL, '2024-01-01 00:00:00', '2026-03-24 16:36:54', 0, 1),
(6, 1, 'Charlie', 'dog', 'Beagle', 'Tri-color', 3, 'male', 'Charlie is an energetic Beagle who loves to play and explore. Great with kids and other dogs.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(7, 1, 'Bella', 'dog', 'French Bulldog', 'Fawn', 2, 'female', 'Bella is a sweet French Bulldog with a calm temperament. She loves cuddles and short walks.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1),
(8, 1, 'Rocky', 'dog', 'German Shepherd', 'Black and Tan', 4, 'male', 'Rocky is a loyal German Shepherd looking for an active family. Knows basic commands.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(9, 1, 'Luna', 'dog', 'Siberian Husky', 'Gray and White', 1, 'female', 'Luna is a playful Husky puppy with beautiful blue eyes. Very energetic and friendly.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1),
(10, 1, 'Cooper', 'dog', 'Golden Retriever', 'Golden', 5, 'male', 'Cooper is a gentle Golden Retriever who loves children. Well-trained and calm.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(11, 1, 'Oliver', 'cat', 'Maine Coon', 'Brown Tabby', 3, 'male', 'Oliver is a majestic Maine Coon with a fluffy coat. Very affectionate and good with dogs.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1),
(12, 1, 'Chloe', 'cat', 'Persian', 'White', 2, 'female', 'Chloe is a beautiful Persian cat with stunning blue eyes. Quiet and elegant.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(13, 1, 'Simba', 'cat', 'Bengal', 'Spotted', 1, 'male', 'Simba is an active Bengal kitten with beautiful spotted coat. Loves to climb and play.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1),
(14, 1, 'Milo', 'cat', 'Siamese', 'Seal Point', 4, 'male', 'Milo is a vocal Siamese who loves attention. Very social and talkative.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(15, 1, 'Nala', 'cat', 'Ragdoll', 'Seal Lynx', 2, 'female', 'Nala is a floppy Ragdoll who goes limp when you hold her. Very relaxed and sweet.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(16, 1, 'Mango', 'bird', 'Sun Conure', 'Yellow/Orange', 1, 'male', 'Mango is a vibrant Sun Conure parrot. Very colorful and learning to talk.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1),
(17, 1, 'Blue', 'bird', 'Blue Jay', 'Blue', 2, 'female', 'Blue is a beautiful Blue Jay with striking blue feathers. Very active and vocal.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(18, 1, 'Kiwi', 'bird', 'Budgie', 'Green', 1, 'female', 'Kiwi is a small budgie perfect for first-time bird owners. Sweet and chirpy.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(19, 1, 'Rio', 'bird', 'Macaw', 'Blue and Gold', 5, 'male', 'Rio is a stunning Macaw with a large vocabulary. Needs an experienced owner.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1),
(20, 1, 'Thumper', 'rabbit', 'Holland Lop', 'Chocolate', 1, 'male', 'Thumper is a cute Holland Lop with floppy ears. Litter box trained and friendly.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(21, 1, 'Daisy', 'rabbit', 'Rex', 'White', 2, 'female', 'Daisy is a soft Rex rabbit with velvety fur. Very calm and loves to be petted.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(22, 1, 'Oreo', 'rabbit', 'Dutch', 'Black and White', 1, 'female', 'Oreo is a playful Dutch rabbit who loves to explore. Great with older children.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1),
(23, 1, 'Peanut', 'hamster', 'Syrian', 'Golden', 0, 'male', 'Peanut is a friendly Syrian hamster. Active at night and fun to watch.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(24, 1, 'Squeaky', 'guinea pig', 'Abyssinian', 'Brown/White', 1, 'female', 'Squeaky is a vocal guinea pig who wheeks for treats. Very social.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 0, 1),
(25, 1, 'Gizmo', 'ferret', 'Sable', 'Dark Brown', 2, 'male', 'Gizmo is a curious ferret who loves tunnels and toys. Very playful and energetic.', 'available', NULL, '2026-03-19 17:41:27', '2026-03-24 16:08:41', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `phone`, `email`, `address`) VALUES
(1, 'PetSupplies Co', 'Mark Johnson', '800-555-1000', 'order@petsupplies.com', '123 Supply Rd, Chicago, IL'),
(2, 'PetGear Inc', 'Sarah Williams', '800-555-2000', 'sales@petgear.com', '456 Gear Ave, Boston, MA'),
(3, 'FunPets Ltd', 'Mike Chen', '800-555-3000', 'info@funpets.com', '789 Toy St, Miami, FL'),
(4, 'AquaWorld', 'Lisa Rodriguez', '800-555-4000', 'support@aquaworld.com', '321 Fish Lane, Seattle, WA');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart` (`customer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_pets`
--
ALTER TABLE `customer_pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier` (`supplier`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`product_id`,`customer_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `search_log`
--
ALTER TABLE `search_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `store_pets`
--
ALTER TABLE `store_pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer_pets`
--
ALTER TABLE `customer_pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `search_log`
--
ALTER TABLE `search_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `store_pets`
--
ALTER TABLE `store_pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `customer_pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `customer_pets`
--
ALTER TABLE `customer_pets`
  ADD CONSTRAINT `customer_pets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `store_pets` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `search_log`
--
ALTER TABLE `search_log`
  ADD CONSTRAINT `search_log_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `store_pets`
--
ALTER TABLE `store_pets`
  ADD CONSTRAINT `store_pets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
