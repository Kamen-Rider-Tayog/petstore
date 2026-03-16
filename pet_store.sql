-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 09:09 PM
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
(3, 3, 3, 1, '2024-03-06 11:30:00', 'nail trim', 20, 'pending');

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
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Emma', 'Wilson', 'emma@email.com', '555-9876', '123 Maple Street, Springfield', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(2, 'David', 'Brown', 'david@email.com', '555-5678', '456 Oak Avenue, Springfield', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(3, 'Jessica', 'Taylor', 'jessica@email.com', '555-4321', '789 Pine Road, Springfield', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(4, 'Robert', 'Garcia', 'robert@email.com', '555-8765', '321 Elm Street, Springfield', '2024-01-01 00:00:00', '2024-01-01 00:00:00');

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
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `species` varchar(30) DEFAULT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pet_status` enum('available','sold','reserved','adopted') DEFAULT 'available',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `name`, `species`, `breed`, `color`, `age`, `gender`, `price`, `description`, `pet_status`, `image`, `created_at`, `updated_at`, `featured`) VALUES
(1, 'Max', 'dog', NULL, NULL, 2, NULL, 350.00, 'Friendly golden retriever, great with kids and other pets.', 'available', NULL, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 0),
(2, 'Luna', 'cat', NULL, NULL, 3, NULL, 180.00, 'Playful tabby cat, loves to cuddle and play with toys.', 'available', NULL, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 0),
(3, 'Coco', 'rabbit', NULL, NULL, 4, NULL, 75.50, 'Gentle lop-eared rabbit, perfect for first-time owners.', 'sold', NULL, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 0),
(4, 'Kiwi', 'bird', NULL, NULL, 2, NULL, 120.00, 'Colorful parakeet, sings beautifully and loves attention.', 'available', NULL, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 0),
(5, 'Charlie', 'hamster', NULL, NULL, 1, NULL, 45.00, 'Adorable teddy bear hamster, very active and friendly.', 'reserved', NULL, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 0);

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

INSERT INTO `products` (`id`, `product_name`, `category`, `brand`, `description`, `quantity_in_stock`, `price`, `sale_price`, `weight`, `weight_unit`, `supplier`, `featured`, `rating`, `review_count`, `created_at`, `on_sale`) VALUES
(1, 'Dog Food - Premium', 'food', 'Pedigree', 'Premium dog food for adult dogs', 25, 45.99, NULL, 5.00, 'kg', 1, 1, 0.0, 0, '2026-03-15 13:21:34', 0),
(2, 'Cat Food - Wet', 'food', 'Whiskas', 'Wet cat food in gravy', 40, 2.50, NULL, 400.00, 'g', 1, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(3, 'Dog Leash', 'accessories', NULL, NULL, 15, 12.99, NULL, NULL, NULL, 2, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(4, 'Cat Toy - Mouse', 'toy', NULL, NULL, 60, 3.25, NULL, NULL, NULL, 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(5, 'Fish Tank Filter', 'equipment', NULL, NULL, 8, 35.50, NULL, NULL, NULL, 4, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(6, 'Hamster Wheel', 'accessories', NULL, NULL, 12, 8.75, NULL, NULL, NULL, 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(7, 'Bird Cage - Small', 'housing', NULL, NULL, 5, 45.00, NULL, NULL, NULL, 2, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(8, 'Dog Bed - Large', 'accessories', NULL, NULL, 3, 65.00, NULL, NULL, NULL, 2, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(9, 'Organic Dog Food', 'Dog Food', 'Organic Paws', 'Organic, grain-free dog food', 20, 65.99, NULL, 3.00, 'kg', 1, 1, 0.0, 0, '2026-03-15 13:21:34', 0),
(10, 'Cat Scratcher Post', 'Accessories', 'Kitty Fun', 'Sisal scratching post for cats', 15, 25.99, NULL, 2.00, 'kg', 2, 1, 0.0, 0, '2026-03-15 13:21:34', 0),
(11, 'Bird Swing', 'Toys', 'Parrot Paradise', 'Wooden swing for birds', 30, 12.50, NULL, 0.50, 'kg', 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(12, 'Hamster Exercise Ball', 'Accessories', 'FunPets', 'Clear exercise ball for hamsters', 25, 8.99, NULL, 0.20, 'kg', 3, 0, 0.0, 0, '2026-03-15 13:21:34', 0),
(13, 'Fish Tank Heater', 'Tanks', 'AquaWorld', 'Submersible aquarium heater', 10, 32.99, NULL, 0.30, 'kg', 4, 1, 0.0, 0, '2026-03-15 13:21:34', 0);

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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `created_at`) VALUES
(1, 'storeManager', 'IamtheStoreManager', 'Store Manager', '2026-03-14 18:47:13');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`),
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
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
