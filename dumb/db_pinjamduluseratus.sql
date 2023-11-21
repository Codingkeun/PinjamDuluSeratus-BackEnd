/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 10.4.18-MariaDB : Database - db_pinjamduluseratus
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_pinjamduluseratus` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `db_pinjamduluseratus`;

/*Table structure for table `investor` */

DROP TABLE IF EXISTS `investor`;

CREATE TABLE `investor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `npm` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `faculty` varchar(255) NOT NULL,
  `major` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `foto_ktm` varchar(255) NOT NULL,
  `foto_selfie` varchar(255) NOT NULL,
  `foto_profile` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `investor_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `investor` */

/*Table structure for table `peminjam` */

DROP TABLE IF EXISTS `peminjam`;

CREATE TABLE `peminjam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `npm` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `faculty` varchar(255) NOT NULL,
  `major` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `foto_ktm` varchar(255) NOT NULL,
  `foto_selfie` varchar(255) NOT NULL,
  `foto_profile` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `peminjam_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `peminjam` */

/*Table structure for table `request_pinjaman` */

DROP TABLE IF EXISTS `request_pinjaman`;

CREATE TABLE `request_pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_peminjam` int(11) NOT NULL,
  `id_investor` int(11) NOT NULL,
  `nominal` int(10) NOT NULL,
  `tip` int(10) NOT NULL,
  `instalment_nominal` int(10) DEFAULT NULL,
  `instalment_total` int(10) DEFAULT NULL,
  `instalment_status` enum('belum','pending','lunas','gagal') DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `status_approval` enum('wait','approve') DEFAULT NULL,
  `bank_name` varchar(20) DEFAULT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_peminjam` (`id_peminjam`),
  KEY `id_investor` (`id_investor`),
  CONSTRAINT `request_pinjaman_ibfk_1` FOREIGN KEY (`id_peminjam`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `request_pinjaman_ibfk_2` FOREIGN KEY (`id_investor`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `request_pinjaman` */

/*Table structure for table `request_pinjaman_cicilan` */

DROP TABLE IF EXISTS `request_pinjaman_cicilan`;

CREATE TABLE `request_pinjaman_cicilan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_request_pinjaman` int(11) NOT NULL,
  `date` date NOT NULL,
  `nominal` int(10) NOT NULL,
  `status` enum('belum','pending','lunas') DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_request_pinjaman` (`id_request_pinjaman`),
  CONSTRAINT `request_pinjaman_cicilan_ibfk_1` FOREIGN KEY (`id_request_pinjaman`) REFERENCES `request_pinjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `request_pinjaman_cicilan` */

/*Table structure for table `saldo_investor` */

DROP TABLE IF EXISTS `saldo_investor`;

CREATE TABLE `saldo_investor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_investor` int(11) NOT NULL,
  `nominal` int(10) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `saldo_investor` */

/*Table structure for table `saldo_sedekah` */

DROP TABLE IF EXISTS `saldo_sedekah`;

CREATE TABLE `saldo_sedekah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `nominal` int(10) NOT NULL,
  `siklus` enum('masuk','keluar') NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('wait','success') DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `saldo_sedekah` */

/*Table structure for table `transaction` */

DROP TABLE IF EXISTS `transaction`;

CREATE TABLE `transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_investor` int(11) NOT NULL,
  `nominal` int(10) NOT NULL,
  `siklus` enum('masuk','keluar') NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('wait','success') NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_investor` (`id_investor`),
  CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_investor`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `transaction` */

/*Table structure for table `univ_profile` */

DROP TABLE IF EXISTS `univ_profile`;

CREATE TABLE `univ_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `univ_profile` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('peminjam','investor') NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `users` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
