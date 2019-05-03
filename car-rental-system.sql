-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.19 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for car-rental-system
CREATE DATABASE IF NOT EXISTS `car-rental-system` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `car-rental-system`;

-- Dumping structure for table car-rental-system.cars
CREATE TABLE IF NOT EXISTS `cars` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(10) NOT NULL,
  `price_per_hour` int(100) DEFAULT '0',
  `model_id` int(100) NOT NULL,
  `year` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table car-rental-system.cars: ~1 rows (approximately)
/*!40000 ALTER TABLE `cars` DISABLE KEYS */;
INSERT INTO `cars` (`id`, `plate_number`, `price_per_hour`, `model_id`, `year`) VALUES
	(1, 'wjy3291', 100, 2, '2019');
/*!40000 ALTER TABLE `cars` ENABLE KEYS */;

-- Dumping structure for table car-rental-system.manufacturers
CREATE TABLE IF NOT EXISTS `manufacturers` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
  `manufacturer_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table car-rental-system.manufacturers: ~2 rows (approximately)
/*!40000 ALTER TABLE `manufacturers` DISABLE KEYS */;
INSERT INTO `manufacturers` (`id`, `manufacturer_name`) VALUES
	(1, 'toyota'),
	(2, 'bmw');
/*!40000 ALTER TABLE `manufacturers` ENABLE KEYS */;

-- Dumping structure for table car-rental-system.models
CREATE TABLE IF NOT EXISTS `models` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
  `manufacturer_id` int(100) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table car-rental-system.models: ~2 rows (approximately)
/*!40000 ALTER TABLE `models` DISABLE KEYS */;
INSERT INTO `models` (`id`, `manufacturer_id`, `model_name`) VALUES
	(1, 2, 'X4'),
	(2, 1, 'Camry');
/*!40000 ALTER TABLE `models` ENABLE KEYS */;

-- Dumping structure for table car-rental-system.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
  `car_id` int(100) DEFAULT NULL,
  `user_id` int(100) DEFAULT NULL,
  `approved` int(1) NOT NULL DEFAULT '0',
  `total_price` int(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table car-rental-system.orders: ~0 rows (approximately)
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;

-- Dumping structure for table car-rental-system.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `role` int(1) unsigned NOT NULL DEFAULT '0',
  `ic_number` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table car-rental-system.users: ~1 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `password`, `email`, `role`, `ic_number`) VALUES
	(1, 'syafiq', 'shamsuddin', 'syafiq', 'hashedPassword', 'syafiq@mail.com', 1, '978789878767');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
