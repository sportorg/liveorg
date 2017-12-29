-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.20 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for liveorg
CREATE DATABASE IF NOT EXISTS `liveorg` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `liveorg`;


-- Dumping structure for table liveorg.group
CREATE TABLE IF NOT EXISTS `group` (
  `id` binary(16) NOT NULL,
  `race_id` binary(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `course` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table liveorg.person
CREATE TABLE IF NOT EXISTS `person` (
  `id` binary(16) NOT NULL,
  `group_id` binary(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `link` text,
  `bib` varchar(255),
  `team` text,
  `start` datetime DEFAULT NULL,
  `finish` datetime DEFAULT NULL,
  `result` int(11) DEFAULT NULL,
  `split` text,
  `status` enum('OK','DSQ') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table liveorg.race
CREATE TABLE IF NOT EXISTS `race` (
  `id` binary(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `timezone` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table liveorg.token
CREATE TABLE IF NOT EXISTS `token` (
  `token` binary(16) NOT NULL,
  `race_id` binary(16) DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
