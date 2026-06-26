-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: card_shop
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
--



--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `buyer_id` int(10) unsigned NOT NULL,
  `seller_id` int(10) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('paid','completed') NOT NULL DEFAULT 'paid',
  `created_at` datetime NOT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notification_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_orders_product` (`product_id`),
  KEY `fk_orders_buyer` (`buyer_id`),
  KEY `fk_orders_seller` (`seller_id`),
  CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,5,3,2,1,777.00,'paid','2026-06-16 21:21:23','2026-06-16 21:21:23',NULL),(2,6,5,2,1,888.00,'paid','2026-06-18 15:51:41','2026-06-18 15:51:41','2026-06-18 15:51:44'),(3,7,3,2,1,456.00,'paid','2026-06-19 17:16:46','2026-06-19 17:16:46','2026-06-19 17:16:50');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_images_product` (`product_id`),
  CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,5,'uploads/products/card_5_6a314da9a12b16.36518384.png',1,'2026-06-16 21:20:41'),(2,5,'uploads/products/card_5_6a314da9a643e7.42049296.png',0,'2026-06-16 21:20:41'),(3,6,'uploads/products/card_6_6a33a37890e307.82314384.png',1,'2026-06-18 15:51:20'),(4,6,'uploads/products/card_6_6a33a378947e00.09473982.png',0,'2026-06-18 15:51:20'),(5,7,'uploads/products/card_7_6a3508fc78fc83.16519765.png',1,'2026-06-19 17:16:44');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','sold_out') NOT NULL DEFAULT 'active',
  `condition_tags` varchar(255) NOT NULL DEFAULT '',
  `group_name` varchar(120) NOT NULL DEFAULT '',
  `member_name` varchar(120) NOT NULL DEFAULT '',
  `album_name` varchar(120) NOT NULL DEFAULT '',
  `card_version` varchar(120) NOT NULL DEFAULT '',
  `card_code` varchar(80) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sold_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_products_seller` (`seller_id`),
  CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,2,'IVE 安兪真粉卡','奶油白邊框、近全新，適合收藏。',320.00,1,'active','近全新,限量特典','','','','','','2026-06-16 20:40:10','2026-06-16 20:40:10',NULL),(2,2,'NewJeans Hanni 拍立得卡','韓系柔霧風格，多角度實拍。',450.00,2,'active','未拆封,官方卡套','','','','','','2026-06-16 20:40:10','2026-06-16 20:40:10',NULL),(3,2,'SEVENTEEN 小卡組','適合新手入坑，一次收三張。',590.00,0,'sold_out','輕微瑕疵,稀有閃卡','','','','','','2026-06-16 20:40:10','2026-06-16 20:40:10','2026-06-16 20:40:10'),(5,2,'TestCard_20260616212041','Automated upload flow verification',777.00,0,'sold_out','未拆封,官方卡套','','','','','','2026-06-16 21:20:41','2026-06-16 21:21:23','2026-06-16 21:21:23'),(6,2,'MilkyTest_20260618155120','End to end flow verification card',888.00,0,'sold_out','未拆封,官方卡套','IVE','Yujin','Switch','Lucky Draw','IVE-YUJIN-20260618155120','2026-06-18 15:51:20','2026-06-18 15:51:41','2026-06-18 15:51:41'),(7,2,'FlowCard_20260619171641','Flow upload check 20260619171641',456.00,0,'sold_out','全新, 已拆','IVE','Yujin','Flow Album','Lucky Draw','FLOW-20260619171641','2026-06-19 17:16:44','2026-06-19 17:16:46','2026-06-19 17:16:46');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remember_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `last_used_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `fk_remember_user` (`user_id`),
  CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remember_tokens`
--

LOCK TABLES `remember_tokens` WRITE;
/*!40000 ALTER TABLE `remember_tokens` DISABLE KEYS */;
INSERT INTO `remember_tokens` VALUES (2,2,'5c28c64d59eada3c','169e0ceca478e4378175a947549fdb6690b2fbfa7bd90f393afa5d2dbd4317b1','2026-06-23 21:23:28','2026-06-16 21:23:28','2026-06-16 21:23:28'),(3,7,'64e3048be8bd4d12','9a5d8a3bd3ae35d85df454449bfac740f40c6ffe1ec65387bacd17b555afce1a','2026-06-26 17:16:44','2026-06-19 17:16:44','2026-06-19 17:16:44');
/*!40000 ALTER TABLE `remember_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `buyer_id` int(10) unsigned NOT NULL,
  `seller_id` int(10) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review_once` (`order_id`,`buyer_id`,`seller_id`),
  KEY `fk_reviews_buyer` (`buyer_id`),
  KEY `fk_reviews_seller` (`seller_id`),
  CONSTRAINT `fk_reviews_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,1,3,2,5,'Automated review flow verification','2026-06-16 21:21:57'),(2,2,5,2,5,'Flow verification review','2026-06-18 15:52:13'),(3,3,3,2,5,'flow review 20260619171641','2026-06-19 17:16:52');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','seller','buyer') NOT NULL DEFAULT 'buyer',
  `display_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `favorite_group` varchar(100) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$VMkEq318Ps.vc43sftonveHPfRuBGWN9FqFzG8bceuGE4vUEDLNiq','admin','Admin Milk','admin@example.com','','','','2026-06-19 17:16:54','2026-06-16 20:40:10'),(2,'seller01','$2y$10$qW.RfIGw7dsbEHPhlNDSduJ82g6oQ/.VJqaqTL.CLgJLl.XzIOFNG','seller','Cream Seller','seller@example.com','','','','2026-06-19 17:16:44','2026-06-16 20:40:10'),(3,'buyer01','$2y$10$OyKw67zscpx9L9c6nYEtHuLPn5zI07/OKgvLWiskrk22a4gD4yTty','buyer','Latte Buyer','buyer@example.com','','','','2026-06-19 17:16:46','2026-06-16 20:40:10'),(4,'user20260616212226','$2y$10$hDdY34HwDaJMLNUd/31E4e0zrPQQM/Rf0IxhNxwkKEwX8ihx98jRC','buyer','Test User','user20260616212226@example.com','','','',NULL,'2026-06-16 21:22:26'),(5,'buyer20260618155037','$2y$10$AqCzcEwjDvFtCDnkkh.bb.De3aIXYfg0Amwq449AUId4pBLDBeWQm','buyer','Flow Buyer','buyer20260618155037@example.com','0911222333','IVE','Taipei Test Address','2026-06-18 15:51:41','2026-06-18 15:50:37'),(6,'test','$2y$10$C0Bu/1B80dnb/dad9nDItO7OOBjbunB2gXpxhaC5BmVDsPT99A9MS','buyer','test','test@gmail.com','','','','2026-06-18 16:06:31','2026-06-18 16:06:31'),(7,'flow_buyer_20260619171641','$2y$10$l5aDwl.LTWard9ydkc4xwe60sHzLZLN0RX8CExEQB7qSWUsU7RNKa','buyer','Flow Buyer 20260619171641','flow_buyer_20260619171641@example.com','','','','2026-06-19 17:16:44','2026-06-19 17:16:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-19 17:21:54
