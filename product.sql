-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 03:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kyla_bistro`
--

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `category_id`, `product_name`, `description`, `price`, `stock_quantity`, `image`) VALUES
(1, 1, 'Pork Sisig', 'Shimmered, grilled and sauted pork topped with egg.', 378.00, 15, 'pork-sisig.jpg'),
(2, 8, 'Baby Back Ribs', 'Pugon roasted baby back ribs in smokey barbeque sauce.', 368.00, 10, 'back-ribs.jpg'),
(3, 7, 'Kassy Kass', 'Heavy ground beef, pineapple, mushroom, black olive, and heavy cheese.', 378.00, 10, 'kassy-kass.jpg'),
(4, 10, 'Baked Prawns', 'Butterflied tiger prawns baked with special 3 cheese toppings broiled finish.', 468.00, 9, 'prawns.jpg'),
(5, 6, 'Carbonara', 'Rich and creamy mushroom sauce serve with fettucine, grilled chicken and parmesan cheese.', 318.00, 15, 'carbonara.jpg'),
(6, 10, 'Baked Salmon', 'Baked pink salmon fillet with special 3 cheese toppings thin broiled to perfection.', 598.00, 10, 'salmon.jpg'),
(7, 6, 'Lasagna', 'Oven baked layers of meat, rich bechamel and fresh tomato sauce.', 238.00, 20, 'lasagna.jpg'),
(8, 3, 'Chicken Fingers', 'Dreaded chicken finger with fries and gravy.', 268.00, 15, 'chiken-fingers.jpg'),
(9, 3, 'Country Club Chicken', 'Crispy fried breaded chicken with traditional gravy sauce.', 268.00, 20, 'country-club-chicken.jpg'),
(10, 3, 'Chicken Inasal', 'Chicken breast stuffed with creamy spinach with mushroom demi glaze', 278.00, 15, 'chicken-inasal.jpg'),
(11, 1, 'French Fries', 'Classic.', 138.00, 30, 'fries.jpg'),
(12, 1, 'Kinilaw', 'Fresh raw fish fillet steeped in Kylas special vinegar mix.', 378.00, 15, 'kinilaw.jpg'),
(13, 2, 'Beef Salpicao', 'A hearty stir-fry of meat, potatoes, garlic, and scallions in a rich, dark sauce.', 340.00, 15, 'salpicao.jpg'),
(14, 7, 'Kyla\'s Supreme', 'Everything on it without pork.', 438.00, 15, 'supreme.jpg'),
(15, 7, 'Carls Hawaiian', 'Ham, pineapple and cheese.', 358.00, 15, 'hawaiian.png'),
(16, 7, 'Robins Veggie Corner', 'Broccoli, cauli flower, lettuce, bell pepper, tomato, white onion, pineapple and cheese.', 378.00, 10, 'veggie-pizza.png'),
(17, 7, 'Rachelle\'s Mango', 'Creamy white sauce with fresh mango fruit, cheese and nata.', 378.00, 10, 'mango-pizza.jpg'),
(18, 8, 'Crispy Pata', 'Herb rubbed crispy pata with soy vinegar dip.', 798.00, 10, 'crispy-pata.jpg'),
(19, 8, 'Lechon Kawali', '(fried Pork Belly) Simmered till tender then deep fried.', 328.00, 20, 'lechon-kawali.jpg'),
(20, 8, 'Sizzling Pork Belly', 'Grilled pork belly topped with egg and brown sauce.', 328.00, 20, 'pork-belly.jpg'),
(21, 1, 'Calamares', 'Fried squid flash fried braded squid with spiced up native vinegar.', 348.00, 20, 'calamares.jpg'),
(22, 10, 'Nilasing na Hipon', 'A crispy fried shrimp topped with sliced green onions, crunchy, and full of flavor.', 358.00, 15, 'hipon.jpg'),
(23, 9, 'Clubhouse', 'Overload sandwich filled with fresh lettuce, tomato, cucumber, cheese, egg and chicken.', 318.00, 30, 'clubhouse.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
