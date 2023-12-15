/*
 Navicat Premium Data Transfer

 Source Server         : Server Local
 Source Server Type    : MariaDB
 Source Server Version : 100432
 Source Host           : localhost:3306
 Source Schema         : db_pinjamduluseratus

 Target Server Type    : MariaDB
 Target Server Version : 100432
 File Encoding         : 65001

 Date: 15/12/2023 15:45:01
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for investor
-- ----------------------------
DROP TABLE IF EXISTS `investor`;
CREATE TABLE `investor`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NULL DEFAULT NULL,
  `npm` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `faculty` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `major` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_ktm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_selfie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_user`(`id_user`) USING BTREE,
  CONSTRAINT `investor_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investor
-- ----------------------------
INSERT INTO `investor` VALUES (1, 3, '11231', 'Cecep Sutisna', '083822', 'Faculty', 'Jurusa', 'kelas', '', '', '', NULL, NULL);

-- ----------------------------
-- Table structure for payment_method
-- ----------------------------
DROP TABLE IF EXISTS `payment_method`;
CREATE TABLE `payment_method`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `univ_id` int(11) NULL DEFAULT NULL,
  `bank_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `account_number` int(11) NULL DEFAULT NULL,
  `bank_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `enabled` tinyint(1) NULL DEFAULT 1,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of payment_method
-- ----------------------------
INSERT INTO `payment_method` VALUES (1, 1, 'BCA', 100192012, 'BCA.svg', 1, '2023-12-15 10:20:00', NULL);
INSERT INTO `payment_method` VALUES (2, 1, 'BNI', 100192013, 'BNI.svg', 1, NULL, NULL);
INSERT INTO `payment_method` VALUES (3, 1, 'BNI Syariah', 100192014, 'BNI_Syariah.svg', 1, NULL, NULL);
INSERT INTO `payment_method` VALUES (4, 1, 'BRI', 100192016, 'BRI.svg', 1, NULL, NULL);
INSERT INTO `payment_method` VALUES (5, 1, 'Mandiri', 100192017, 'Mandiri.svg', 1, NULL, NULL);
INSERT INTO `payment_method` VALUES (6, 1, 'VISA', 100192018, 'VISA.svg', 0, NULL, NULL);
INSERT INTO `payment_method` VALUES (7, 1, 'MasterCard', 100192019, 'MasterCard.svg', 0, NULL, NULL);

-- ----------------------------
-- Table structure for peminjam
-- ----------------------------
DROP TABLE IF EXISTS `peminjam`;
CREATE TABLE `peminjam`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NULL DEFAULT NULL,
  `npm` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `faculty` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `major` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_ktm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_selfie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_user`(`id_user`) USING BTREE,
  CONSTRAINT `peminjam_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of peminjam
-- ----------------------------
INSERT INTO `peminjam` VALUES (1, 1, '10292012', 'Cecep Rokani', '087172817231', 'Informatika', 'Teknik Informatika', 'TIF 222 MB', 'googl.com', 'google.com', 'gogle.com', '2023-11-21 11:16:09', NULL);

-- ----------------------------
-- Table structure for request_pinjaman
-- ----------------------------
DROP TABLE IF EXISTS `request_pinjaman`;
CREATE TABLE `request_pinjaman`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_peminjam` int(11) NOT NULL,
  `nominal` int(11) NOT NULL,
  `tip` int(11) NOT NULL,
  `instalment_nominal` int(11) NULL DEFAULT NULL,
  `instalment_total` int(11) NULL DEFAULT NULL,
  `instalment_status` enum('belum','pending','lunas','gagal') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status_approval` enum('wait','approve') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `bank_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `account_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_peminjam`(`id_peminjam`) USING BTREE,
  CONSTRAINT `request_pinjaman_ibfk_1` FOREIGN KEY (`id_peminjam`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of request_pinjaman
-- ----------------------------
INSERT INTO `request_pinjaman` VALUES (8, 1, 1500000, 150000, 550000, 3, 'belum', '2024-03-08 22:23:59', NULL, 'wait', 'BRI', '111223', NULL, NULL);
INSERT INTO `request_pinjaman` VALUES (9, 1, 3500000, 150000, 1216667, 3, 'belum', '2024-03-08 22:57:43', NULL, 'wait', 'BRI', '111223', NULL, NULL);
INSERT INTO `request_pinjaman` VALUES (10, 1, 100000, 10000, 22000, 5, 'belum', '2024-05-12 15:00:55', NULL, 'wait', 'BNI Syariah', '101111010101', NULL, NULL);
INSERT INTO `request_pinjaman` VALUES (11, 1, 150000, 12000, 162000, 1, 'belum', '2024-01-12 23:10:52', NULL, 'wait', 'VISA', '1012912092323', NULL, NULL);
INSERT INTO `request_pinjaman` VALUES (12, 1, 50000, 5000, 5500, 10, 'belum', '2024-10-12 23:12:26', NULL, 'approve', 'BCA', '100301023', NULL, NULL);

-- ----------------------------
-- Table structure for request_pinjaman_cicilan
-- ----------------------------
DROP TABLE IF EXISTS `request_pinjaman_cicilan`;
CREATE TABLE `request_pinjaman_cicilan`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_request_pinjaman` int(11) NOT NULL,
  `date` date NOT NULL,
  `nominal` int(11) NOT NULL,
  `status` enum('belum','pending','lunas') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `date_payment` datetime NULL DEFAULT NULL,
  `payment_method_id` int(11) NULL DEFAULT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_request_pinjaman`(`id_request_pinjaman`) USING BTREE,
  CONSTRAINT `request_pinjaman_cicilan_ibfk_1` FOREIGN KEY (`id_request_pinjaman`) REFERENCES `request_pinjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 38 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of request_pinjaman_cicilan
-- ----------------------------
INSERT INTO `request_pinjaman_cicilan` VALUES (16, 8, '2024-01-08', 550000, 'belum', NULL, NULL, NULL, '2023-12-08 22:23:59', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (17, 8, '2024-02-08', 550000, 'belum', NULL, NULL, NULL, '2023-12-08 22:23:59', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (18, 8, '2024-03-08', 550000, 'belum', NULL, NULL, NULL, '2023-12-08 22:23:59', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (19, 9, '2024-01-08', 1216667, 'belum', NULL, NULL, NULL, '2023-12-08 22:57:43', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (20, 9, '2024-02-08', 1216667, 'belum', NULL, NULL, NULL, '2023-12-08 22:57:43', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (21, 9, '2024-03-08', 1216667, 'belum', NULL, NULL, NULL, '2023-12-08 22:57:43', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (22, 10, '2024-01-12', 22000, 'belum', NULL, NULL, NULL, '2023-12-12 15:00:55', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (23, 10, '2024-02-12', 22000, 'belum', NULL, NULL, NULL, '2023-12-12 15:00:55', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (24, 10, '2024-03-12', 22000, 'belum', NULL, NULL, NULL, '2023-12-12 15:00:55', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (25, 10, '2024-04-12', 22000, 'belum', NULL, NULL, NULL, '2023-12-12 15:00:55', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (26, 10, '2024-05-12', 22000, 'belum', NULL, NULL, NULL, '2023-12-12 15:00:55', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (27, 11, '2024-01-12', 162000, 'belum', NULL, NULL, NULL, '2023-12-12 23:10:52', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (28, 12, '2024-01-12', 5500, 'lunas', '2023-12-15 15:36:21', 1, 'http://localhost:1111/assets/attachment-1f4793ecab238ad6.png', '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (29, 12, '2024-02-12', 5500, 'lunas', '2023-12-15 15:37:55', 3, 'http://localhost:1111/assets/attachment-a26549e22eeb59a9.png', '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (30, 12, '2024-03-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (31, 12, '2024-04-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (32, 12, '2024-05-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (33, 12, '2024-06-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (34, 12, '2024-07-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (35, 12, '2024-08-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (36, 12, '2024-09-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);
INSERT INTO `request_pinjaman_cicilan` VALUES (37, 12, '2024-10-12', 5500, 'belum', NULL, NULL, NULL, '2023-12-12 23:12:26', NULL);

-- ----------------------------
-- Table structure for saldo_investor
-- ----------------------------
DROP TABLE IF EXISTS `saldo_investor`;
CREATE TABLE `saldo_investor`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_investor` int(11) NOT NULL,
  `nominal` int(11) NOT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of saldo_investor
-- ----------------------------

-- ----------------------------
-- Table structure for saldo_sedekah
-- ----------------------------
DROP TABLE IF EXISTS `saldo_sedekah`;
CREATE TABLE `saldo_sedekah`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `nominal` int(11) NOT NULL,
  `siklus` enum('masuk','keluar') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` enum('wait','success') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `expired_payment` datetime NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of saldo_sedekah
-- ----------------------------

-- ----------------------------
-- Table structure for transaction
-- ----------------------------
DROP TABLE IF EXISTS `transaction`;
CREATE TABLE `transaction`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_investor` int(11) NOT NULL,
  `id_request_pinjaman` int(11) NULL DEFAULT NULL,
  `nominal` int(11) NOT NULL,
  `siklus` enum('masuk','keluar') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` enum('wait','success') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_investor`(`id_investor`) USING BTREE,
  INDEX `id_request_pinjaman`(`id_request_pinjaman`) USING BTREE,
  CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_investor`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`id_request_pinjaman`) REFERENCES `request_pinjaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of transaction
-- ----------------------------
INSERT INTO `transaction` VALUES (1, 1, 12, 55000, 'keluar', 'pinjmaan bro', 'success', NULL, '2023-12-14 16:19:45', NULL);

-- ----------------------------
-- Table structure for univ_profile
-- ----------------------------
DROP TABLE IF EXISTS `univ_profile`;
CREATE TABLE `univ_profile`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of univ_profile
-- ----------------------------
INSERT INTO `univ_profile` VALUES (1, 'Sekolah Tinggi Teknologi Bandung', 'Jl. Soekarno Hatta No.378, Kb. Lega, Kec. Bojongloa Kidul, Kota Bandung, Jawa Barat 40235', 'https://webutama-dev.sttbandung.ac.id/storage/uploads/images/600639023_logo_sttbandung_warna.png');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `role` enum('peminjam','investor') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_login` datetime NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'ceceprokani@gmail.com', '$2y$10$NQfqclWqITk2EV/PYiclW.dyFKywTH/NXv6lkceWaxMJmlNTYYYkq', 'peminjam', '2023-12-14 15:38:15', '2023-11-21 11:15:39', NULL);
INSERT INTO `users` VALUES (3, 'cecepsutisna@gmail.com', '123123', 'investor', '2023-12-08 21:41:08', NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
