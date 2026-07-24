-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: toile
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `category_request`
--

DROP TABLE IF EXISTS `category_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shop_id` int DEFAULT NULL,
  `category_type` enum('style','type') NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_category_request_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_request`
--

/*!40000 ALTER TABLE `category_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_request` ENABLE KEYS */;

--
-- Table structure for table `email_log`
--

DROP TABLE IF EXISTS `email_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(180) NOT NULL,
  `type` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `error_message` text,
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_log`
--

/*!40000 ALTER TABLE `email_log` DISABLE KEYS */;
INSERT INTO `email_log` VALUES (1,'admin@toile.fr','welcome','Bienvenue sur Toile !','sent',NULL,'2026-07-23 13:42:40'),(2,'pauline.hiez@laplateforme.io','welcome','Bienvenue sur Toile !','sent',NULL,'2026-07-23 14:05:28'),(3,'pauline.hiez@laplateforme.io','artist_approved','Ta demande artiste a été acceptée !','sent',NULL,'2026-07-23 14:08:36'),(4,'client@test.com','welcome','Bienvenue sur Toile !','sent',NULL,'2026-07-23 16:21:57'),(5,'client@test.com','payment_captured','Paiement débité — Commande #2','sent',NULL,'2026-07-23 16:33:55'),(6,'client@test.com','order_status','Commande #2 — Acceptée','sent',NULL,'2026-07-23 16:33:59'),(7,'client@test.com','order_status','Commande #2 — En cours de création','sent',NULL,'2026-07-23 16:34:09'),(8,'client@test.com','order_status','Commande #2 — Livrée - En attente de validation','sent',NULL,'2026-07-23 16:34:37'),(9,'pauline.hiez@laplateforme.io','order_status','Commande #2 — Terminée','sent',NULL,'2026-07-23 16:35:09');
/*!40000 ALTER TABLE `email_log` ENABLE KEYS */;

--
-- Table structure for table `favorite`
--

DROP TABLE IF EXISTS `favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorite` (
  `user_id` int NOT NULL,
  `shop_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`shop_id`),
  KEY `fk_favorite_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorite`
--

/*!40000 ALTER TABLE `favorite` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorite` ENABLE KEYS */;

--
-- Table structure for table `invoice_counter`
--

DROP TABLE IF EXISTS `invoice_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_counter` (
  `series` varchar(20) NOT NULL,
  `year` int NOT NULL,
  `last_number` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`series`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_counter`
--

/*!40000 ALTER TABLE `invoice_counter` DISABLE KEYS */;
INSERT INTO `invoice_counter` VALUES ('FAC',2026,5);
/*!40000 ALTER TABLE `invoice_counter` ENABLE KEYS */;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notification_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
INSERT INTO `notification` VALUES (1,3,'artist_approved','Félicitations ! Ta demande a été acceptée, tu es maintenant un Artiste !','/my-subscription',1,'2026-07-23 14:08:36'),(3,3,'new_order','Nouvelle commande : Caricature chien','/commandes/2',1,'2026-07-23 16:33:04'),(4,6,'payment_captured','Paiement de 60.00 € débité — commande acceptée.','/commandes/2',1,'2026-07-23 16:33:55'),(5,6,'order_status','Commande #2 : Acceptée','/commandes/2',1,'2026-07-23 16:33:55'),(6,6,'order_status','Commande #2 : En cours de création','/commandes/2',1,'2026-07-23 16:34:06'),(7,6,'order_status','Commande #2 : Livrée - En attente de validation','/commandes/2',1,'2026-07-23 16:34:32'),(8,3,'order_status','Commande #2 : Terminée','/commandes/2',1,'2026-07-23 16:35:05'),(9,3,'new_review','Nouvel avis (5⭐) sur ta boutique','/boutiques/pop-s-art',1,'2026-07-23 16:35:28');
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;

--
-- Table structure for table `order_message`
--

DROP TABLE IF EXISTS `order_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_message_order` (`order_id`),
  KEY `fk_order_message_sender` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_message`
--

/*!40000 ALTER TABLE `order_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_message` ENABLE KEYS */;

--
-- Table structure for table `order_service_base`
--

DROP TABLE IF EXISTS `order_service_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_service_base` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `service_base_id` int DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT '',
  `label` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_order_service_base_order` (`order_id`),
  KEY `fk_order_service_base_base` (`service_base_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_service_base`
--

/*!40000 ALTER TABLE `order_service_base` DISABLE KEYS */;
INSERT INTO `order_service_base` VALUES (1,2,6,'Caricature','Moyenne'),(2,2,1,'Matériaux','Feutre');
/*!40000 ALTER TABLE `order_service_base` ENABLE KEYS */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `shop_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `total_price` int NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `commission_amount` int NOT NULL DEFAULT '0',
  `status` enum('quote_requested','price_proposed','pending','accepted','rejected','in_progress','delivered','completed','cancelled') NOT NULL DEFAULT 'pending',
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `delivery_file` varchar(255) DEFAULT NULL,
  `shipping_name` varchar(150) DEFAULT NULL,
  `shipping_address_line1` varchar(255) DEFAULT NULL,
  `shipping_address_line2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_postal_code` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `billing_name` varchar(150) DEFAULT NULL,
  `billing_address_line1` varchar(255) DEFAULT NULL,
  `billing_address_line2` varchar(255) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_postal_code` varchar(20) DEFAULT NULL,
  `billing_country` varchar(100) DEFAULT NULL,
  `invoice_number` varchar(30) DEFAULT NULL,
  `invoiced_at` datetime DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `fk_order_client` (`client_id`),
  KEY `fk_order_shop` (`shop_id`),
  KEY `fk_order_service` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (2,6,1,1,'Caricature chien','J\'aimerais une caricature de mon chien jouant avec sa balle, je vous laisse choisir les autres éléments de décor',6000,0.00,0,'completed','pi_3TwNg2CHvrrlwjMD1i0obAkE','1bce692095076e17fa63a2ca6579e471.webp',NULL,NULL,NULL,NULL,NULL,NULL,'Mary Poppins','3, rue du test',NULL,'Toulon','83000','France','CMD-2026-0001','2026-07-23 16:33:51',0,'2026-07-23 16:33:04'),(4,8,1,NULL,'Portrait aquarelle du chat','Commande de démonstration — Portrait aquarelle du chat.',8500,0.00,0,'completed','pi_seed_204cf2412489',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Julie Bernard','14 rue des Fleurs',NULL,'Nantes','44000','France','FAC-2026-00001','2026-06-12 06:52:13',0,'2026-06-12 06:52:13'),(5,9,1,NULL,'Illustration de couple','Commande de démonstration — Illustration de couple.',12000,0.00,0,'completed','pi_seed_254e5f666bc2',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Thomas Leroy','3 allée du Parc',NULL,'Bordeaux','33000','France','FAC-2026-00002','2026-06-24 06:52:13',0,'2026-06-24 06:52:13'),(6,10,1,NULL,'Character design complet','Commande de démonstration — Character design complet.',18000,0.00,0,'delivered','pi_seed_ae1ae49ba6ba',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Camille Petit','27 avenue Jean Jaurès',NULL,'Toulouse','31000','France','FAC-2026-00003','2026-07-08 06:52:13',0,'2026-07-08 06:52:13'),(7,8,1,NULL,'Commission chibi','Commande de démonstration — Commission chibi.',3500,0.00,0,'in_progress','pi_seed_7a63b7024faa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Julie Bernard','14 rue des Fleurs',NULL,'Nantes','44000','France','FAC-2026-00004','2026-07-15 06:52:13',0,'2026-07-15 06:52:13'),(8,9,1,NULL,'Portrait de famille format A3','Commande de démonstration — Portrait de famille format A3.',9000,0.00,0,'accepted','pi_seed_a9eb725459fd',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Thomas Leroy','3 allée du Parc',NULL,'Bordeaux','33000','France','FAC-2026-00005','2026-07-19 06:52:13',0,'2026-07-19 06:52:13'),(9,10,1,NULL,'Sketch rapide personnage','Commande de démonstration — Sketch rapide personnage.',2500,0.00,0,'pending','pi_seed_d2fb8683eab0',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-21 06:52:13'),(10,8,1,NULL,'Illustration bannière Twitch','Commande de démonstration — Illustration bannière Twitch.',7000,0.00,0,'quote_requested',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-07-22 06:52:13');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;

--
-- Table structure for table `password_reset`
--

DROP TABLE IF EXISTS `password_reset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `fk_password_reset_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset`
--

/*!40000 ALTER TABLE `password_reset` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset` ENABLE KEYS */;

--
-- Table structure for table `portfolio_image`
--

DROP TABLE IF EXISTS `portfolio_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolio_image` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shop_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `label` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_portfolio_image_shop` (`shop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio_image`
--

/*!40000 ALTER TABLE `portfolio_image` DISABLE KEYS */;
INSERT INTO `portfolio_image` VALUES (1,1,'8a43c3dfca59f8110fcd7c048a87e478.png',0,NULL,'2026-07-23 16:18:07'),(2,1,'ac881d56f7597246012ff5d6df5c260a.png',1,NULL,'2026-07-23 16:18:07'),(3,1,'15e92cee570a4ab8a1caa7dbf673b82c.png',2,NULL,'2026-07-23 16:18:07');
/*!40000 ALTER TABLE `portfolio_image` ENABLE KEYS */;

--
-- Table structure for table `raffle_entry`
--

DROP TABLE IF EXISTS `raffle_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `raffle_entry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shop_id` int NOT NULL,
  `type` enum('boutiques','homepage') NOT NULL DEFAULT 'boutiques',
  `period` varchar(10) NOT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `amount_paid` int DEFAULT NULL,
  `status` enum('entered','selected','not_selected','cancelled') NOT NULL DEFAULT 'entered',
  `featured_until` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_shop_type_period` (`shop_id`,`type`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raffle_entry`
--

/*!40000 ALTER TABLE `raffle_entry` DISABLE KEYS */;
/*!40000 ALTER TABLE `raffle_entry` ENABLE KEYS */;

--
-- Table structure for table `rate_limit_attempt`
--

DROP TABLE IF EXISTS `rate_limit_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limit_attempt` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(190) NOT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_limit_lookup` (`identifier`,`action`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limit_attempt`
--

/*!40000 ALTER TABLE `rate_limit_attempt` DISABLE KEYS */;
INSERT INTO `rate_limit_attempt` VALUES (1,'ip:127.0.0.1','register','2026-07-23 13:42:34'),(2,'ip:127.0.0.1','register','2026-07-23 14:05:22'),(3,'ip:127.0.0.1','register','2026-07-23 14:05:28'),(4,'ip:127.0.0.1','register','2026-07-23 16:21:49');
/*!40000 ALTER TABLE `rate_limit_attempt` ENABLE KEYS */;

--
-- Table structure for table `remember_token`
--

DROP TABLE IF EXISTS `remember_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remember_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_remember_token_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remember_token`
--

/*!40000 ALTER TABLE `remember_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `remember_token` ENABLE KEYS */;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reporter_id` int NOT NULL,
  `reportable_type` enum('shop','review') NOT NULL,
  `reportable_id` int NOT NULL,
  `reason` enum('plagiat','contenu_inapproprie','arnaque','commentaire_inapproprie','spam','autre') NOT NULL DEFAULT 'autre',
  `message` text,
  `status` enum('pending','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `resolved_by` int DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_report_reporter` (`reporter_id`),
  KEY `fk_report_resolved_by` (`resolved_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report`
--

/*!40000 ALTER TABLE `report` DISABLE KEYS */;
/*!40000 ALTER TABLE `report` ENABLE KEYS */;

--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `review` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  CONSTRAINT `chk_review_rating` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `review`
--

/*!40000 ALTER TABLE `review` DISABLE KEYS */;
INSERT INTO `review` VALUES (1,2,5,'Super artiste !','2026-07-23 16:35:28');
/*!40000 ALTER TABLE `review` ENABLE KEYS */;

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shop_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `base_price` int NOT NULL,
  `delivery_days` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_service_shop` (`shop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service`
--

/*!40000 ALTER TABLE `service` DISABLE KEYS */;
INSERT INTO `service` VALUES (1,1,'Caricature','Base 1 personnage + 3 éléments de décor','bc437e96ab1813313655c04019855113.png',6000,4,1,'2026-07-23 16:26:42');
/*!40000 ALTER TABLE `service` ENABLE KEYS */;

--
-- Table structure for table `service_base`
--

DROP TABLE IF EXISTS `service_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_base` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT '',
  `label` varchar(150) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_service_base_service` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_base`
--

/*!40000 ALTER TABLE `service_base` DISABLE KEYS */;
INSERT INTO `service_base` VALUES (1,1,'Matériaux','Feutre','2026-07-23 16:26:42'),(2,1,'Matériaux','Peinture gouache','2026-07-23 16:26:42'),(3,1,'Matériaux','Aquarelle','2026-07-23 16:26:42'),(4,1,'Matériaux','Crayon noir et blanc','2026-07-23 16:26:42'),(5,1,'Caricature','Légère','2026-07-23 16:26:42'),(6,1,'Caricature','Moyenne','2026-07-23 16:26:42'),(7,1,'Caricature','Forte','2026-07-23 16:26:42');
/*!40000 ALTER TABLE `service_base` ENABLE KEYS */;

--
-- Table structure for table `service_option`
--

DROP TABLE IF EXISTS `service_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_option` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `label` varchar(150) NOT NULL,
  `extra_price` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_service_option_service` (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_option`
--

/*!40000 ALTER TABLE `service_option` DISABLE KEYS */;
INSERT INTO `service_option` VALUES (1,1,'Ajout 1 personnage',2000),(2,1,'Ajout 1 élément de décor',1000);
/*!40000 ALTER TABLE `service_option` ENABLE KEYS */;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setting` (
  `key` varchar(100) NOT NULL,
  `value` text,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES ('company_address','15 rue de l\'Atelier\n75011 Paris, France','2026-07-24 08:52:13'),('company_legal','SAS au capital de 50 000 € — RCS Paris 987 654 321','2026-07-24 08:52:13'),('company_name','Toile Marketplace','2026-07-24 08:52:13'),('company_siret','987 654 321 00015','2026-07-24 08:52:13'),('company_vat','','2026-07-24 08:52:13'),('contact_email','contact@toile.fr','2026-07-24 08:52:13');
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;

--
-- Table structure for table `shop`
--

DROP TABLE IF EXISTS `shop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shop` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `bio` text,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_pinterest` varchar(255) DEFAULT NULL,
  `social_tiktok` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `styles` json DEFAULT NULL,
  `types` json DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_quotes` tinyint(1) NOT NULL DEFAULT '1',
  `plan_selected` tinyint(1) NOT NULL DEFAULT '0',
  `monetization_type` enum('subscription','commission') NOT NULL DEFAULT 'commission',
  `stripe_account_id` varchar(255) DEFAULT NULL,
  `stripe_payouts_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shop`
--

/*!40000 ALTER TABLE `shop` DISABLE KEYS */;
INSERT INTO `shop` VALUES (1,3,'Pop\'s Art','pop-s-art','Passionnée d\'art, je suis à votre disposition pour réaliser vos demandes',NULL,NULL,NULL,NULL,'8962934f34a334e8a860019cefd15406.png','[\"réaliste\"]','[\"illustrateur\", \"designer graphique\"]',1,1,1,'commission',NULL,0,'2026-07-23 14:18:11');
/*!40000 ALTER TABLE `shop` ENABLE KEYS */;

--
-- Table structure for table `shop_subscription`
--

DROP TABLE IF EXISTS `shop_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shop_subscription` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shop_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `status` enum('active','cancelled','past_due') NOT NULL DEFAULT 'active',
  `current_period_start` datetime NOT NULL,
  `current_period_end` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_id` (`shop_id`),
  KEY `fk_shop_subscription_plan` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shop_subscription`
--

/*!40000 ALTER TABLE `shop_subscription` DISABLE KEYS */;
INSERT INTO `shop_subscription` VALUES (1,1,3,'sub_1TwLQyCHvrrlwjMDULmIeXy1','active','2026-07-23 14:09:04','2026-08-23 14:09:04','2026-07-23 14:18:11');
/*!40000 ALTER TABLE `shop_subscription` ENABLE KEYS */;

--
-- Table structure for table `subscription_invoice`
--

DROP TABLE IF EXISTS `subscription_invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_invoice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(30) DEFAULT NULL,
  `shop_id` int NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `amount` int NOT NULL,
  `stripe_invoice_id` varchar(255) NOT NULL,
  `period_start` datetime NOT NULL,
  `period_end` datetime NOT NULL,
  `paid_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stripe_invoice_id` (`stripe_invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `fk_subscription_invoice_shop` (`shop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_invoice`
--

/*!40000 ALTER TABLE `subscription_invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription_invoice` ENABLE KEYS */;

--
-- Table structure for table `subscription_plan`
--

DROP TABLE IF EXISTS `subscription_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_plan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` int NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '10.00',
  `max_services` int NOT NULL DEFAULT '3',
  `max_portfolio_images` int NOT NULL DEFAULT '5',
  `max_options_per_service` int NOT NULL DEFAULT '2',
  `stripe_price_id` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plan`
--

/*!40000 ALTER TABLE `subscription_plan` DISABLE KEYS */;
INSERT INTO `subscription_plan` VALUES (1,'Commission',0,10.00,3,5,2,NULL,'2026-07-23 13:36:59'),(2,'Essentiel',1490,5.00,10,15,5,'price_1TqAUxCHvrrlwjMD7A31tt6O','2026-07-23 13:36:59'),(3,'Pro',2990,0.00,9999,9999,9999,'price_1TqAVUCHvrrlwjMDQd8ZCEbx','2026-07-23 13:36:59');
/*!40000 ALTER TABLE `subscription_plan` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `provider` enum('credentials','google','pinterest') NOT NULL DEFAULT 'credentials',
  `provider_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `role` enum('user','artist','admin') NOT NULL DEFAULT 'user',
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `artist_request_status` enum('pending','approved','rejected') DEFAULT NULL,
  `artist_display_name` varchar(150) DEFAULT NULL,
  `requested_shop_name` varchar(150) DEFAULT NULL,
  `artist_contact_email` varchar(180) DEFAULT NULL,
  `artist_presentation` text,
  `artist_motivation` text,
  `artist_terms_accepted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@toile.fr','Admin','$2y$10$mX9W5NNTGLztNylK7blhee8yZr3Xd3gnCQpsYHEgLVBF.xYVV7iwm','credentials',NULL,NULL,NULL,'a7bd743a988d9323bb5c6cb37497fb71.png',NULL,NULL,NULL,NULL,NULL,NULL,'admin',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-23 13:42:34'),(3,'pauline.hiez@laplateforme.io','Pop\'s Art','$2y$10$ye9LocNFcnHfXb3dFydYRuB3xvsmqCzi4WINyzBd7D5zeONxdhovy','credentials',NULL,'cus_UwDsj9I9Zqs928',NULL,'b9770e66a08048da4fb9eb58f22ff5e3.png',NULL,NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Pop\'s Art','Pop\'s Art','pauline.hiez@laplateforme.io','Passionnée d\'art depuis longtemps, je souhaite proposer mes services au passionnés.','Je souhaite ouvrir une boutique pour mettre mon sens artistique au service des autres','2026-07-23 14:08:16','2026-07-23 14:05:22'),(6,'client@test.com','Mary Poppins','$2y$10$XuCIWzdIoO7RKWepspVhbOebbei992Y74vpHJBcDZMksGY0vAwm4.','credentials',NULL,'cus_UwGCRdKRdysnXX',NULL,'default.png',NULL,NULL,NULL,NULL,NULL,NULL,'user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-23 16:21:49'),(8,'julie.bernard@demo.test','Julie Bernard','$2y$10$8BuAsGvpdsPqSFgOpK2wzuAGVwCGOfTFlQ1c4NvElskyMkJCSMnBK','credentials',NULL,NULL,NULL,'default.png',NULL,'14 rue des Fleurs',NULL,'Nantes','44000','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:52:13'),(9,'thomas.leroy@demo.test','Thomas Leroy','$2y$10$8BuAsGvpdsPqSFgOpK2wzuAGVwCGOfTFlQ1c4NvElskyMkJCSMnBK','credentials',NULL,NULL,NULL,'default.png',NULL,'3 allée du Parc',NULL,'Bordeaux','33000','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:52:13'),(10,'camille.petit@demo.test','Camille Petit','$2y$10$8BuAsGvpdsPqSFgOpK2wzuAGVwCGOfTFlQ1c4NvElskyMkJCSMnBK','credentials',NULL,NULL,NULL,'default.png',NULL,'27 avenue Jean Jaurès',NULL,'Toulouse','31000','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 08:52:13');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-24 16:14:39
