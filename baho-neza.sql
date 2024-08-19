-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2024 at 11:59 AM
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
-- Database: `baho-neza`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(1, 'bahoneza', 'baho@123');

-- --------------------------------------------------------

--
-- Table structure for table `extraction`
--

CREATE TABLE `extraction` (
  `ext_id` int(11) NOT NULL,
  `raw_id` int(11) NOT NULL,
  `extracted_quantity` int(11) NOT NULL,
  `remaining_quantity` int(11) NOT NULL,
  `disposed_quantity` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `extraction`
--

INSERT INTO `extraction` (`ext_id`, `raw_id`, `extracted_quantity`, `remaining_quantity`, `disposed_quantity`, `date`) VALUES
(15, 64, 200, 100, 30, '2024-08-23'),
(16, 61, 50, 20, 10, '2024-08-21'),
(17, 60, 70, 15, 10, '2024-08-19'),
(18, 62, 40, 5, 5, '2024-08-27');

-- --------------------------------------------------------

--
-- Table structure for table `final_product`
--

CREATE TABLE `final_product` (
  `final_pro_id` int(11) NOT NULL,
  `mill_id` int(11) NOT NULL,
  `product_type` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` double NOT NULL,
  `total_price` double NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `final_product`
--

INSERT INTO `final_product` (`final_pro_id`, `mill_id`, `product_type`, `quantity`, `unit_price`, `total_price`, `date`) VALUES
(11, 17, 'akanoze', 40, 1000, 40000, '2024-08-12');

-- --------------------------------------------------------

--
-- Table structure for table `milled_product`
--

CREATE TABLE `milled_product` (
  `mill_id` int(11) NOT NULL,
  `raw_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `ext_id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milled_product`
--

INSERT INTO `milled_product` (`mill_id`, `raw_id`, `quantity`, `ext_id`, `date`) VALUES
(15, 64, 70, 15, '2024-08-31'),
(16, 61, 20, 16, '2024-08-29'),
(17, 60, 45, 17, '2024-08-21'),
(18, 62, 30, 18, '2024-08-17');

-- --------------------------------------------------------

--
-- Table structure for table `raw_material`
--

CREATE TABLE `raw_material` (
  `raw_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `imported_quantity` int(11) NOT NULL,
  `unit_price` double NOT NULL,
  `total_price` double NOT NULL,
  `import_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `raw_material`
--

INSERT INTO `raw_material` (`raw_id`, `name`, `imported_quantity`, `unit_price`, `total_price`, `import_date`) VALUES
(60, 'imyumbati', 100, 250, 25000, '2024-08-21'),
(61, 'ibisuguti', 70, 100, 7000, '2024-08-20'),
(62, 'ibigori', 50, 400, 20000, '2024-08-24'),
(63, 'ubunyobwa', 30, 150, 4500, '2024-08-30'),
(64, 'ibijumba', 250, 120, 30000, '2024-08-23');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `ship_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `remaining_quantity` int(11) NOT NULL,
  `sale_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `ship_id`, `quantity_sold`, `remaining_quantity`, `sale_date`) VALUES
(15, 17, 5, 10, '2024-08-22'),
(16, 17, 8, 2, '2024-08-21'),
(17, 17, 2, 0, '2024-08-30');

-- --------------------------------------------------------

--
-- Table structure for table `shipment`
--

CREATE TABLE `shipment` (
  `ship_id` int(11) NOT NULL,
  `final_pro_id` int(11) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` double NOT NULL,
  `ship_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipment`
--

INSERT INTO `shipment` (`ship_id`, `final_pro_id`, `destination`, `quantity`, `total_price`, `ship_date`) VALUES
(17, 11, 'masaka', 0, 15000, '2024-08-23'),
(18, 11, 'kabuga', 10, 10000, '2024-08-30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `extraction`
--
ALTER TABLE `extraction`
  ADD PRIMARY KEY (`ext_id`),
  ADD KEY `raw_id` (`raw_id`);

--
-- Indexes for table `final_product`
--
ALTER TABLE `final_product`
  ADD PRIMARY KEY (`final_pro_id`),
  ADD KEY `pro_mill_id` (`mill_id`),
  ADD KEY `mill_id` (`mill_id`);

--
-- Indexes for table `milled_product`
--
ALTER TABLE `milled_product`
  ADD PRIMARY KEY (`mill_id`),
  ADD KEY `ext_id` (`ext_id`),
  ADD KEY `product_type` (`raw_id`);

--
-- Indexes for table `raw_material`
--
ALTER TABLE `raw_material`
  ADD PRIMARY KEY (`raw_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `ship_id` (`ship_id`);

--
-- Indexes for table `shipment`
--
ALTER TABLE `shipment`
  ADD PRIMARY KEY (`ship_id`),
  ADD KEY `final_pro_id` (`final_pro_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `extraction`
--
ALTER TABLE `extraction`
  MODIFY `ext_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `final_product`
--
ALTER TABLE `final_product`
  MODIFY `final_pro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `milled_product`
--
ALTER TABLE `milled_product`
  MODIFY `mill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `raw_material`
--
ALTER TABLE `raw_material`
  MODIFY `raw_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `shipment`
--
ALTER TABLE `shipment`
  MODIFY `ship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `extraction`
--
ALTER TABLE `extraction`
  ADD CONSTRAINT `extraction_ibfk_1` FOREIGN KEY (`raw_id`) REFERENCES `raw_material` (`raw_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `final_product`
--
ALTER TABLE `final_product`
  ADD CONSTRAINT `final_product_ibfk_1` FOREIGN KEY (`mill_id`) REFERENCES `milled_product` (`mill_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `milled_product`
--
ALTER TABLE `milled_product`
  ADD CONSTRAINT `milled_product_ibfk_1` FOREIGN KEY (`ext_id`) REFERENCES `extraction` (`ext_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `milled_product_ibfk_2` FOREIGN KEY (`raw_id`) REFERENCES `raw_material` (`raw_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`ship_id`) REFERENCES `shipment` (`ship_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shipment`
--
ALTER TABLE `shipment`
  ADD CONSTRAINT `shipment_ibfk_1` FOREIGN KEY (`final_pro_id`) REFERENCES `final_product` (`final_pro_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
