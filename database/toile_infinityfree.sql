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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_log`
--

/*!40000 ALTER TABLE `email_log` DISABLE KEYS */;
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
INSERT INTO `invoice_counter` VALUES ('FAC',2026,15);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_service_base`
--

/*!40000 ALTER TABLE `order_service_base` DISABLE KEYS */;
INSERT INTO `order_service_base` VALUES (1,1,1,'Format','A4'),(2,1,3,'Cadrage','Buste'),(3,2,5,'Format','A4'),(4,2,7,'Utilisation','Personnel'),(5,3,9,'Vue','Face'),(6,3,12,'Niveau de détail','Standard'),(7,4,14,'Format','A4'),(8,4,16,'Utilisation','Personnel'),(9,5,18,'Vue','Face'),(10,5,21,'Niveau de détail','Standard'),(11,6,23,'Style','Chibi'),(12,7,25,'Vue','Face'),(13,7,28,'Niveau de détail','Standard'),(14,8,30,'Style','Chibi'),(15,9,32,'Ambiance','Jour'),(16,9,34,'Format','Paysage'),(17,10,36,'Style','Chibi'),(18,11,38,'Ambiance','Jour'),(19,11,40,'Format','Paysage'),(20,12,42,'Emplacement','Bras'),(21,12,45,'Style','Fin'),(22,13,47,'Ambiance','Jour'),(23,13,49,'Format','Paysage'),(24,14,51,'Emplacement','Bras'),(25,14,54,'Style','Fin'),(26,15,56,'Plateforme','Twitch'),(27,16,59,'Emplacement','Bras'),(28,16,62,'Style','Fin'),(29,17,64,'Plateforme','Twitch'),(30,18,67,'Taille','16x16'),(31,19,70,'Plateforme','Twitch'),(32,20,73,'Taille','16x16'),(33,21,76,'Format','A4'),(34,21,78,'Cadrage','Buste'),(35,22,80,'Taille','16x16'),(36,23,83,'Format','A4'),(37,23,85,'Cadrage','Buste'),(38,24,87,'Format','A4'),(39,24,89,'Utilisation','Personnel'),(40,25,91,'Format','A4'),(41,25,93,'Cadrage','Buste'),(42,26,95,'Format','A4'),(43,26,97,'Utilisation','Personnel'),(44,27,99,'Vue','Face'),(45,27,102,'Niveau de détail','Standard'),(46,28,104,'Format','A4'),(47,28,106,'Utilisation','Personnel'),(48,29,108,'Vue','Face'),(49,29,111,'Niveau de détail','Standard'),(50,30,113,'Style','Chibi');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,12,1,1,'Portrait de mon chat','Bonjour, je souhaiterais une Portrait numérique : portrait de mon chat. Merci de me dire si c\'est possible !',7000,5.00,350,'completed','pi_demo_20a531fb7e0fb8d6',NULL,'Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France','Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France','FAC-2026-00001','2026-07-24 06:44:40',0,'2026-07-22 06:44:40'),(2,13,1,2,'Illustration de mon perso de JDR','Bonjour, je souhaiterais une Illustration de personnage : illustration de mon perso de jdr. Merci de me dire si c\'est possible !',10500,5.00,525,'completed','pi_demo_d8c75eeffe5cc2a1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France','FAC-2026-00002','2026-07-20 06:44:40',0,'2026-07-18 06:44:40'),(3,14,1,3,'Design de ma mascotte','Bonjour, je souhaiterais une Character design complet : design de ma mascotte. Merci de me dire si c\'est possible !',15000,5.00,750,'delivered','pi_demo_349cd9a93e19f005',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France','FAC-2026-00003','2026-07-16 06:44:40',0,'2026-07-14 06:44:40'),(4,15,2,4,'Chibi de mon couple','Bonjour, je souhaiterais une Illustration de personnage : chibi de mon couple. Merci de me dire si c\'est possible !',8000,10.00,800,'in_progress','pi_demo_e0ba13b7c9c79bf2',NULL,'Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France','Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France','FAC-2026-00004','2026-07-12 06:44:40',0,'2026-07-10 06:44:40'),(5,16,2,5,'Décor pour ma nouvelle','Bonjour, je souhaiterais une Character design complet : décor pour ma nouvelle. Merci de me dire si c\'est possible !',15000,10.00,1500,'accepted','pi_demo_f061de872beb2ff6',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France','FAC-2026-00005','2026-07-08 06:44:40',0,'2026-07-06 06:44:40'),(6,12,2,6,'Tatouage bras loup géométrique','Bonjour, je souhaiterais une Chibi mignon : tatouage bras loup géométrique. Merci de me dire si c\'est possible !',5500,0.00,0,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France',NULL,NULL,0,'2026-07-02 06:44:40'),(7,13,3,7,'Bannière pour ma chaîne Twitch','Bonjour, je souhaiterais une Character design complet : bannière pour ma chaîne twitch. Merci de me dire si c\'est possible !',17500,0.00,0,'quote_requested',NULL,NULL,'Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France','Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France',NULL,NULL,0,'2026-06-28 06:44:40'),(8,14,3,8,'Sprite pour mon jeu indé','Bonjour, je souhaiterais une Chibi mignon : sprite pour mon jeu indé. Merci de me dire si c\'est possible !',3000,0.00,0,'price_proposed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France',NULL,NULL,0,'2026-06-24 06:44:40'),(9,15,3,9,'Portrait de famille','Bonjour, je souhaiterais une Concept d\'environnement : portrait de famille. Merci de me dire si c\'est possible !',12000,0.00,0,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France',NULL,NULL,0,'2026-06-20 06:44:40'),(10,16,4,10,'Fanart de mon OC','Bonjour, je souhaiterais une Chibi mignon : fanart de mon oc. Merci de me dire si c\'est possible !',3000,0.00,0,'rejected',NULL,NULL,'Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France','Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France',NULL,NULL,0,'2026-06-16 06:44:40'),(11,12,4,11,'Logo illustré pour ma boutique','Bonjour, je souhaiterais une Concept d\'environnement : logo illustré pour ma boutique. Merci de me dire si c\'est possible !',14500,0.00,0,'completed','pi_demo_6f497bccfd54b34c',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France','FAC-2026-00006','2026-06-14 06:44:40',0,'2026-06-12 06:44:40'),(12,13,4,12,'Emote pour mon Discord','Bonjour, je souhaiterais une Design de tatouage : emote pour mon discord. Merci de me dire si c\'est possible !',8500,0.00,0,'completed','pi_demo_124f18e6b49f50ff',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France','FAC-2026-00007','2026-06-10 06:44:40',0,'2026-06-08 06:44:40'),(13,14,5,13,'Portrait de mon chat','Bonjour, je souhaiterais une Concept d\'environnement : portrait de mon chat. Merci de me dire si c\'est possible !',12000,5.00,600,'delivered','pi_demo_2dbc8372d15cb4d5',NULL,'Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France','Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France','FAC-2026-00008','2026-06-06 06:44:40',0,'2026-06-04 06:44:40'),(14,15,5,14,'Illustration de mon perso de JDR','Bonjour, je souhaiterais une Design de tatouage : illustration de mon perso de jdr. Merci de me dire si c\'est possible !',6000,5.00,300,'in_progress','pi_demo_4cb3d76550a68c17',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France','FAC-2026-00009','2026-06-02 06:44:40',0,'2026-05-31 06:44:40'),(15,16,5,15,'Design de ma mascotte','Bonjour, je souhaiterais une Bannière / header : design de ma mascotte. Merci de me dire si c\'est possible !',5000,5.00,250,'accepted','pi_demo_4929c81c30eb8622',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France','FAC-2026-00010','2026-05-29 06:44:40',0,'2026-05-27 06:44:40'),(16,12,6,16,'Chibi de mon couple','Bonjour, je souhaiterais une Design de tatouage : chibi de mon couple. Merci de me dire si c\'est possible !',8500,0.00,0,'pending',NULL,NULL,'Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France','Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France',NULL,NULL,0,'2026-05-23 06:44:40'),(17,13,6,17,'Décor pour ma nouvelle','Bonjour, je souhaiterais une Bannière / header : décor pour ma nouvelle. Merci de me dire si c\'est possible !',7500,0.00,0,'quote_requested',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France',NULL,NULL,0,'2026-05-19 06:44:40'),(18,14,6,18,'Tatouage bras loup géométrique','Bonjour, je souhaiterais une Sprite pixel art : tatouage bras loup géométrique. Merci de me dire si c\'est possible !',3500,0.00,0,'price_proposed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France',NULL,NULL,0,'2026-05-15 06:44:40'),(19,15,7,19,'Bannière pour ma chaîne Twitch','Bonjour, je souhaiterais une Bannière / header : bannière pour ma chaîne twitch. Merci de me dire si c\'est possible !',5000,0.00,0,'cancelled',NULL,NULL,'Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France','Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France',NULL,NULL,0,'2026-05-11 06:44:40'),(20,16,7,20,'Sprite pour mon jeu indé','Bonjour, je souhaiterais une Sprite pixel art : sprite pour mon jeu indé. Merci de me dire si c\'est possible !',3500,0.00,0,'rejected',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France',NULL,NULL,0,'2026-05-07 06:44:40'),(21,12,7,21,'Portrait de famille','Bonjour, je souhaiterais une Portrait numérique : portrait de famille. Merci de me dire si c\'est possible !',7000,0.00,0,'completed','pi_demo_53c86d0aba47a578',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France','FAC-2026-00011','2026-05-05 06:44:40',0,'2026-05-03 06:44:40'),(22,13,8,22,'Fanart de mon OC','Bonjour, je souhaiterais une Sprite pixel art : fanart de mon oc. Merci de me dire si c\'est possible !',6000,10.00,600,'completed','pi_demo_651b3cb261d17be0',NULL,'Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France','Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France','FAC-2026-00012','2026-05-01 06:44:40',0,'2026-04-29 06:44:40'),(23,14,8,23,'Logo illustré pour ma boutique','Bonjour, je souhaiterais une Portrait numérique : logo illustré pour ma boutique. Merci de me dire si c\'est possible !',4500,10.00,450,'delivered','pi_demo_5a965e7b110fd820',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France','FAC-2026-00013','2026-04-27 06:44:40',0,'2026-04-25 06:44:40'),(24,15,8,24,'Emote pour mon Discord','Bonjour, je souhaiterais une Illustration de personnage : emote pour mon discord. Merci de me dire si c\'est possible !',8000,10.00,800,'in_progress','pi_demo_94fe3dcec930000b',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France','FAC-2026-00014','2026-04-23 06:44:40',0,'2026-04-21 06:44:40'),(25,16,9,25,'Portrait de mon chat','Bonjour, je souhaiterais une Portrait numérique : portrait de mon chat. Merci de me dire si c\'est possible !',4500,5.00,225,'accepted','pi_demo_5beb1d5546ddb3c0',NULL,'Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France','Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France','FAC-2026-00015','2026-04-19 06:44:40',0,'2026-04-17 06:44:40'),(26,12,9,26,'Illustration de mon perso de JDR','Bonjour, je souhaiterais une Illustration de personnage : illustration de mon perso de jdr. Merci de me dire si c\'est possible !',10500,0.00,0,'pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Julie Bernard','12 rue des Lilas',NULL,'Lyon','69003','France',NULL,NULL,0,'2026-04-13 06:44:40'),(27,13,9,27,'Design de ma mascotte','Bonjour, je souhaiterais une Character design complet : design de ma mascotte. Merci de me dire si c\'est possible !',17500,0.00,0,'quote_requested',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Thomas Leroy','5 avenue Victor Hugo',NULL,'Paris','75016','France',NULL,NULL,0,'2026-04-09 06:44:40'),(28,14,10,28,'Chibi de mon couple','Bonjour, je souhaiterais une Illustration de personnage : chibi de mon couple. Merci de me dire si c\'est possible !',8000,0.00,0,'price_proposed',NULL,NULL,'Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France','Camille Petit','8 boulevard Longchamp',NULL,'Marseille','13001','France',NULL,NULL,0,'2026-04-05 06:44:40'),(29,15,10,29,'Décor pour ma nouvelle','Bonjour, je souhaiterais une Character design complet : décor pour ma nouvelle. Merci de me dire si c\'est possible !',15000,0.00,0,'cancelled',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Antoine Garnier','23 rue du Taur',NULL,'Toulouse','31000','France',NULL,NULL,0,'2026-07-20 06:44:40'),(30,16,10,30,'Tatouage bras loup géométrique','Bonjour, je souhaiterais une Chibi mignon : tatouage bras loup géométrique. Merci de me dire si c\'est possible !',3000,0.00,0,'rejected',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Sophie Martin','3 quai de la Fosse',NULL,'Nantes','44000','France',NULL,NULL,0,'2026-07-16 06:44:40');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio_image`
--

/*!40000 ALTER TABLE `portfolio_image` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limit_attempt`
--

/*!40000 ALTER TABLE `rate_limit_attempt` DISABLE KEYS */;
INSERT INTO `rate_limit_attempt` VALUES (5,'email:pauline-hiez@plateforme.io','login','2026-07-24 16:32:45'),(1,'ip:127.0.0.1','register','2026-07-23 13:42:34'),(2,'ip:127.0.0.1','register','2026-07-23 14:05:22'),(3,'ip:127.0.0.1','register','2026-07-23 14:05:28'),(4,'ip:127.0.0.1','register','2026-07-23 16:21:49');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `review`
--

/*!40000 ALTER TABLE `review` DISABLE KEYS */;
INSERT INTO `review` VALUES (1,1,5,'Travail magnifique, exactement ce que j\'imaginais !','2026-07-25 06:44:40'),(2,2,5,'Très à l\'écoute et super réactif. Je recommande vivement.','2026-07-21 06:44:40'),(3,11,5,'Un rendu au-delà de mes attentes, merci infiniment.','2026-06-15 06:44:40'),(4,12,5,'Superbe qualité et livraison dans les temps.','2026-06-11 06:44:40'),(5,21,5,'Parfait du début à la fin, je reviendrai !','2026-05-06 06:44:40'),(6,22,5,'Belle prestation, quelques allers-retours mais résultat au top.','2026-05-02 06:44:40');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service`
--

/*!40000 ALTER TABLE `service` DISABLE KEYS */;
INSERT INTO `service` VALUES (1,1,'Portrait numérique','Un portrait numérique soigné à partir de ta photo ou de ta description.',NULL,4500,7,1,'2026-01-08 06:44:39'),(2,1,'Illustration de personnage','Illustration complète de ton personnage, pose et couleurs comprises.',NULL,8000,10,1,'2026-01-08 06:44:39'),(3,1,'Character design complet','Conception complète d\'un personnage original avec fiche technique.',NULL,15000,14,1,'2026-01-08 06:44:39'),(4,2,'Illustration de personnage','Illustration complète de ton personnage, pose et couleurs comprises.',NULL,8000,10,1,'2026-01-18 06:44:39'),(5,2,'Character design complet','Conception complète d\'un personnage original avec fiche technique.',NULL,15000,14,1,'2026-01-18 06:44:39'),(6,2,'Chibi mignon','Un adorable chibi de ton personnage, parfait pour les avatars.',NULL,3000,5,1,'2026-01-18 06:44:39'),(7,3,'Character design complet','Conception complète d\'un personnage original avec fiche technique.',NULL,15000,14,1,'2026-01-28 06:44:39'),(8,3,'Chibi mignon','Un adorable chibi de ton personnage, parfait pour les avatars.',NULL,3000,5,1,'2026-01-28 06:44:39'),(9,3,'Concept d\'environnement','Décor d\'ambiance pour ton univers, jeu ou récit.',NULL,12000,12,1,'2026-01-28 06:44:39'),(10,4,'Chibi mignon','Un adorable chibi de ton personnage, parfait pour les avatars.',NULL,3000,5,1,'2026-02-07 06:44:39'),(11,4,'Concept d\'environnement','Décor d\'ambiance pour ton univers, jeu ou récit.',NULL,12000,12,1,'2026-02-07 06:44:39'),(12,4,'Design de tatouage','Un design de tatouage sur-mesure, prêt à être encré.',NULL,6000,8,1,'2026-02-07 06:44:39'),(13,5,'Concept d\'environnement','Décor d\'ambiance pour ton univers, jeu ou récit.',NULL,12000,12,1,'2026-02-17 06:44:39'),(14,5,'Design de tatouage','Un design de tatouage sur-mesure, prêt à être encré.',NULL,6000,8,1,'2026-02-17 06:44:39'),(15,5,'Bannière / header','Une bannière percutante pour tes réseaux ou ta chaîne.',NULL,5000,6,1,'2026-02-17 06:44:39'),(16,6,'Design de tatouage','Un design de tatouage sur-mesure, prêt à être encré.',NULL,6000,8,1,'2026-02-27 06:44:39'),(17,6,'Bannière / header','Une bannière percutante pour tes réseaux ou ta chaîne.',NULL,5000,6,1,'2026-02-27 06:44:39'),(18,6,'Sprite pixel art','Un sprite pixel art propre, animé sur demande.',NULL,3500,6,1,'2026-02-27 06:44:39'),(19,7,'Bannière / header','Une bannière percutante pour tes réseaux ou ta chaîne.',NULL,5000,6,1,'2026-03-09 06:44:40'),(20,7,'Sprite pixel art','Un sprite pixel art propre, animé sur demande.',NULL,3500,6,1,'2026-03-09 06:44:40'),(21,7,'Portrait numérique','Un portrait numérique soigné à partir de ta photo ou de ta description.',NULL,4500,7,1,'2026-03-09 06:44:40'),(22,8,'Sprite pixel art','Un sprite pixel art propre, animé sur demande.',NULL,3500,6,1,'2026-03-19 06:44:40'),(23,8,'Portrait numérique','Un portrait numérique soigné à partir de ta photo ou de ta description.',NULL,4500,7,1,'2026-03-19 06:44:40'),(24,8,'Illustration de personnage','Illustration complète de ton personnage, pose et couleurs comprises.',NULL,8000,10,1,'2026-03-19 06:44:40'),(25,9,'Portrait numérique','Un portrait numérique soigné à partir de ta photo ou de ta description.',NULL,4500,7,1,'2026-03-29 06:44:40'),(26,9,'Illustration de personnage','Illustration complète de ton personnage, pose et couleurs comprises.',NULL,8000,10,1,'2026-03-29 06:44:40'),(27,9,'Character design complet','Conception complète d\'un personnage original avec fiche technique.',NULL,15000,14,1,'2026-03-29 06:44:40'),(28,10,'Illustration de personnage','Illustration complète de ton personnage, pose et couleurs comprises.',NULL,8000,10,1,'2026-04-08 06:44:40'),(29,10,'Character design complet','Conception complète d\'un personnage original avec fiche technique.',NULL,15000,14,1,'2026-04-08 06:44:40'),(30,10,'Chibi mignon','Un adorable chibi de ton personnage, parfait pour les avatars.',NULL,3000,5,1,'2026-04-08 06:44:40');
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
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_base`
--

/*!40000 ALTER TABLE `service_base` DISABLE KEYS */;
INSERT INTO `service_base` VALUES (1,1,'Format','A4','2026-01-08 06:44:39'),(2,1,'Format','A5','2026-01-08 06:44:39'),(3,1,'Cadrage','Buste','2026-01-08 06:44:39'),(4,1,'Cadrage','Plein format','2026-01-08 06:44:39'),(5,2,'Format','A4','2026-01-08 06:44:39'),(6,2,'Format','A3','2026-01-08 06:44:39'),(7,2,'Utilisation','Personnel','2026-01-08 06:44:39'),(8,2,'Utilisation','Commercial','2026-01-08 06:44:39'),(9,3,'Vue','Face','2026-01-08 06:44:39'),(10,3,'Vue','3/4','2026-01-08 06:44:39'),(11,3,'Vue','Dos','2026-01-08 06:44:39'),(12,3,'Niveau de détail','Standard','2026-01-08 06:44:39'),(13,3,'Niveau de détail','Détaillé','2026-01-08 06:44:39'),(14,4,'Format','A4','2026-01-18 06:44:39'),(15,4,'Format','A3','2026-01-18 06:44:39'),(16,4,'Utilisation','Personnel','2026-01-18 06:44:39'),(17,4,'Utilisation','Commercial','2026-01-18 06:44:39'),(18,5,'Vue','Face','2026-01-18 06:44:39'),(19,5,'Vue','3/4','2026-01-18 06:44:39'),(20,5,'Vue','Dos','2026-01-18 06:44:39'),(21,5,'Niveau de détail','Standard','2026-01-18 06:44:39'),(22,5,'Niveau de détail','Détaillé','2026-01-18 06:44:39'),(23,6,'Style','Chibi','2026-01-18 06:44:39'),(24,6,'Style','Semi-chibi','2026-01-18 06:44:39'),(25,7,'Vue','Face','2026-01-28 06:44:39'),(26,7,'Vue','3/4','2026-01-28 06:44:39'),(27,7,'Vue','Dos','2026-01-28 06:44:39'),(28,7,'Niveau de détail','Standard','2026-01-28 06:44:39'),(29,7,'Niveau de détail','Détaillé','2026-01-28 06:44:39'),(30,8,'Style','Chibi','2026-01-28 06:44:39'),(31,8,'Style','Semi-chibi','2026-01-28 06:44:39'),(32,9,'Ambiance','Jour','2026-01-28 06:44:39'),(33,9,'Ambiance','Nuit','2026-01-28 06:44:39'),(34,9,'Format','Paysage','2026-01-28 06:44:39'),(35,9,'Format','Portrait','2026-01-28 06:44:39'),(36,10,'Style','Chibi','2026-02-07 06:44:39'),(37,10,'Style','Semi-chibi','2026-02-07 06:44:39'),(38,11,'Ambiance','Jour','2026-02-07 06:44:39'),(39,11,'Ambiance','Nuit','2026-02-07 06:44:39'),(40,11,'Format','Paysage','2026-02-07 06:44:39'),(41,11,'Format','Portrait','2026-02-07 06:44:39'),(42,12,'Emplacement','Bras','2026-02-07 06:44:39'),(43,12,'Emplacement','Dos','2026-02-07 06:44:39'),(44,12,'Emplacement','Jambe','2026-02-07 06:44:39'),(45,12,'Style','Fin','2026-02-07 06:44:39'),(46,12,'Style','Ombré','2026-02-07 06:44:39'),(47,13,'Ambiance','Jour','2026-02-17 06:44:39'),(48,13,'Ambiance','Nuit','2026-02-17 06:44:39'),(49,13,'Format','Paysage','2026-02-17 06:44:39'),(50,13,'Format','Portrait','2026-02-17 06:44:39'),(51,14,'Emplacement','Bras','2026-02-17 06:44:39'),(52,14,'Emplacement','Dos','2026-02-17 06:44:39'),(53,14,'Emplacement','Jambe','2026-02-17 06:44:39'),(54,14,'Style','Fin','2026-02-17 06:44:39'),(55,14,'Style','Ombré','2026-02-17 06:44:39'),(56,15,'Plateforme','Twitch','2026-02-17 06:44:39'),(57,15,'Plateforme','YouTube','2026-02-17 06:44:39'),(58,15,'Plateforme','Twitter','2026-02-17 06:44:39'),(59,16,'Emplacement','Bras','2026-02-27 06:44:39'),(60,16,'Emplacement','Dos','2026-02-27 06:44:39'),(61,16,'Emplacement','Jambe','2026-02-27 06:44:39'),(62,16,'Style','Fin','2026-02-27 06:44:39'),(63,16,'Style','Ombré','2026-02-27 06:44:39'),(64,17,'Plateforme','Twitch','2026-02-27 06:44:39'),(65,17,'Plateforme','YouTube','2026-02-27 06:44:39'),(66,17,'Plateforme','Twitter','2026-02-27 06:44:39'),(67,18,'Taille','16x16','2026-02-27 06:44:39'),(68,18,'Taille','32x32','2026-02-27 06:44:39'),(69,18,'Taille','64x64','2026-02-27 06:44:39'),(70,19,'Plateforme','Twitch','2026-03-09 06:44:40'),(71,19,'Plateforme','YouTube','2026-03-09 06:44:40'),(72,19,'Plateforme','Twitter','2026-03-09 06:44:40'),(73,20,'Taille','16x16','2026-03-09 06:44:40'),(74,20,'Taille','32x32','2026-03-09 06:44:40'),(75,20,'Taille','64x64','2026-03-09 06:44:40'),(76,21,'Format','A4','2026-03-09 06:44:40'),(77,21,'Format','A5','2026-03-09 06:44:40'),(78,21,'Cadrage','Buste','2026-03-09 06:44:40'),(79,21,'Cadrage','Plein format','2026-03-09 06:44:40'),(80,22,'Taille','16x16','2026-03-19 06:44:40'),(81,22,'Taille','32x32','2026-03-19 06:44:40'),(82,22,'Taille','64x64','2026-03-19 06:44:40'),(83,23,'Format','A4','2026-03-19 06:44:40'),(84,23,'Format','A5','2026-03-19 06:44:40'),(85,23,'Cadrage','Buste','2026-03-19 06:44:40'),(86,23,'Cadrage','Plein format','2026-03-19 06:44:40'),(87,24,'Format','A4','2026-03-19 06:44:40'),(88,24,'Format','A3','2026-03-19 06:44:40'),(89,24,'Utilisation','Personnel','2026-03-19 06:44:40'),(90,24,'Utilisation','Commercial','2026-03-19 06:44:40'),(91,25,'Format','A4','2026-03-29 06:44:40'),(92,25,'Format','A5','2026-03-29 06:44:40'),(93,25,'Cadrage','Buste','2026-03-29 06:44:40'),(94,25,'Cadrage','Plein format','2026-03-29 06:44:40'),(95,26,'Format','A4','2026-03-29 06:44:40'),(96,26,'Format','A3','2026-03-29 06:44:40'),(97,26,'Utilisation','Personnel','2026-03-29 06:44:40'),(98,26,'Utilisation','Commercial','2026-03-29 06:44:40'),(99,27,'Vue','Face','2026-03-29 06:44:40'),(100,27,'Vue','3/4','2026-03-29 06:44:40'),(101,27,'Vue','Dos','2026-03-29 06:44:40'),(102,27,'Niveau de détail','Standard','2026-03-29 06:44:40'),(103,27,'Niveau de détail','Détaillé','2026-03-29 06:44:40'),(104,28,'Format','A4','2026-04-08 06:44:40'),(105,28,'Format','A3','2026-04-08 06:44:40'),(106,28,'Utilisation','Personnel','2026-04-08 06:44:40'),(107,28,'Utilisation','Commercial','2026-04-08 06:44:40'),(108,29,'Vue','Face','2026-04-08 06:44:40'),(109,29,'Vue','3/4','2026-04-08 06:44:40'),(110,29,'Vue','Dos','2026-04-08 06:44:40'),(111,29,'Niveau de détail','Standard','2026-04-08 06:44:40'),(112,29,'Niveau de détail','Détaillé','2026-04-08 06:44:40'),(113,30,'Style','Chibi','2026-04-08 06:44:40'),(114,30,'Style','Semi-chibi','2026-04-08 06:44:40');
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
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_option`
--

/*!40000 ALTER TABLE `service_option` DISABLE KEYS */;
INSERT INTO `service_option` VALUES (1,1,'Arrière-plan détaillé',3000),(2,1,'Fichier haute résolution',1500),(3,2,'Personnage supplémentaire',5000),(4,2,'Fichier source (PSD)',2500),(5,3,'Turnaround complet',6000),(6,3,'Palette alternative',2000),(7,4,'Personnage supplémentaire',5000),(8,4,'Fichier source (PSD)',2500),(9,5,'Turnaround complet',6000),(10,5,'Palette alternative',2000),(11,6,'Accessoire',1000),(12,6,'Mini décor',1500),(13,7,'Turnaround complet',6000),(14,7,'Palette alternative',2000),(15,8,'Accessoire',1000),(16,8,'Mini décor',1500),(17,9,'Variante de cadrage',4000),(18,9,'Fichier haute résolution',2000),(19,10,'Accessoire',1000),(20,10,'Mini décor',1500),(21,11,'Variante de cadrage',4000),(22,11,'Fichier haute résolution',2000),(23,12,'Version couleur',2500),(24,13,'Variante de cadrage',4000),(25,13,'Fichier haute résolution',2000),(26,14,'Version couleur',2500),(27,15,'Déclinaisons réseaux',2000),(28,15,'Logo intégré',3000),(29,16,'Version couleur',2500),(30,17,'Déclinaisons réseaux',2000),(31,17,'Logo intégré',3000),(32,18,'Animation idle',4000),(33,18,'Palette custom',1500),(34,19,'Déclinaisons réseaux',2000),(35,19,'Logo intégré',3000),(36,20,'Animation idle',4000),(37,20,'Palette custom',1500),(38,21,'Arrière-plan détaillé',3000),(39,21,'Fichier haute résolution',1500),(40,22,'Animation idle',4000),(41,22,'Palette custom',1500),(42,23,'Arrière-plan détaillé',3000),(43,23,'Fichier haute résolution',1500),(44,24,'Personnage supplémentaire',5000),(45,24,'Fichier source (PSD)',2500),(46,25,'Arrière-plan détaillé',3000),(47,25,'Fichier haute résolution',1500),(48,26,'Personnage supplémentaire',5000),(49,26,'Fichier source (PSD)',2500),(50,27,'Turnaround complet',6000),(51,27,'Palette alternative',2000),(52,28,'Personnage supplémentaire',5000),(53,28,'Fichier source (PSD)',2500),(54,29,'Turnaround complet',6000),(55,29,'Palette alternative',2000),(56,30,'Accessoire',1000),(57,30,'Mini décor',1500);
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shop`
--

/*!40000 ALTER TABLE `shop` DISABLE KEYS */;
INSERT INTO `shop` VALUES (1,2,'Studio Léa','studio-lea','Portraitiste passionnée, je capture les émotions au plus près du réel.',NULL,NULL,NULL,NULL,'default.png','[\"réaliste\"]','[\"portraitiste\", \"illustrateur\"]',1,1,1,'subscription',NULL,0,'2026-01-08 06:44:39'),(2,3,'Pixel Forge','pixel-forge','Artiste pixel art rétro, je donne vie à tes personnages case par case.',NULL,NULL,NULL,NULL,'default.png','[\"pixel art\"]','[\"illustrateur\", \"character designer\"]',1,1,1,'commission',NULL,0,'2026-01-18 06:44:39'),(3,4,'Chibi Corner','chibi-corner','Univers kawaii et couleurs pastel : je dessine tout en mignon.',NULL,NULL,NULL,NULL,'default.png','[\"chibi\", \"anime\"]','[\"illustrateur\"]',1,1,1,'commission',NULL,0,'2026-01-28 06:44:39'),(4,5,'Concept Lab','concept-lab','Concept artist pour jeux vidéo et films d\'animation.',NULL,NULL,NULL,NULL,'default.png','[\"concept art\"]','[\"concept artist\", \"designer graphique\"]',1,1,1,'subscription',NULL,0,'2026-02-07 06:44:39'),(5,6,'Encre & Réalisme','encre-realisme','Du portrait réaliste au design de tatouage, l\'encre est mon terrain de jeu.',NULL,NULL,NULL,NULL,'default.png','[\"réaliste\"]','[\"portraitiste\", \"tatoueur\"]',1,1,1,'subscription',NULL,0,'2026-02-17 06:44:39'),(6,7,'Anime Dream','anime-dream','Fan d\'anime, je crée des personnages pleins de vie.',NULL,NULL,NULL,NULL,'default.png','[\"anime\", \"chibi\"]','[\"illustrateur\", \"character designer\"]',1,1,1,'commission',NULL,0,'2026-02-27 06:44:39'),(7,8,'Character Base','character-base','Spécialiste du character design, du croquis à la fiche finale.',NULL,NULL,NULL,NULL,'default.png','[\"concept art\", \"anime\"]','[\"character designer\"]',1,1,1,'subscription',NULL,0,'2026-03-09 06:44:40'),(8,9,'Atelier Chloé','atelier-chloe','Illustratrice indépendante, douce et minutieuse.',NULL,NULL,NULL,NULL,'default.png','[\"réaliste\"]','[\"illustrateur\"]',1,1,1,'commission',NULL,0,'2026-03-19 06:44:40'),(9,10,'Retro Pixels','retro-pixels','Pixel art et identité visuelle rétro pour tes projets.',NULL,NULL,NULL,NULL,'default.png','[\"pixel art\"]','[\"designer graphique\", \"illustrateur\"]',1,1,1,'subscription',NULL,0,'2026-03-29 06:44:40'),(10,11,'Ink & Soul','ink-soul','Le tatouage comme récit : chaque pièce raconte une histoire.',NULL,NULL,NULL,NULL,'default.png','[\"réaliste\", \"concept art\"]','[\"tatoueur\", \"illustrateur\"]',1,1,1,'commission',NULL,0,'2026-04-08 06:44:40');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shop_subscription`
--

/*!40000 ALTER TABLE `shop_subscription` DISABLE KEYS */;
INSERT INTO `shop_subscription` VALUES (1,1,2,NULL,'active','2026-06-27 06:44:39','2026-08-27 06:44:39','2026-01-08 06:44:39'),(2,2,1,NULL,'active','2026-06-27 06:44:39','2026-08-27 06:44:39','2026-01-18 06:44:39'),(3,3,1,NULL,'active','2026-06-27 06:44:39','2026-08-27 06:44:39','2026-01-28 06:44:39'),(4,4,3,NULL,'active','2026-06-27 06:44:39','2026-08-27 06:44:39','2026-02-07 06:44:39'),(5,5,2,NULL,'active','2026-06-27 06:44:39','2026-08-27 06:44:39','2026-02-17 06:44:39'),(6,6,1,NULL,'active','2026-06-27 06:44:39','2026-08-27 06:44:39','2026-02-27 06:44:39'),(7,7,3,NULL,'active','2026-06-27 06:44:40','2026-08-27 06:44:40','2026-03-09 06:44:40'),(8,8,1,NULL,'active','2026-06-27 06:44:40','2026-08-27 06:44:40','2026-03-19 06:44:40'),(9,9,2,NULL,'active','2026-06-27 06:44:40','2026-08-27 06:44:40','2026-03-29 06:44:40'),(10,10,1,NULL,'active','2026-06-27 06:44:40','2026-08-27 06:44:40','2026-04-08 06:44:40');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'pauline.hiez@laplateforme.io','Pauline','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:39','default.png',NULL,NULL,NULL,NULL,NULL,NULL,'admin',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-27 08:44:39'),(2,'lea.moreau@demo.test','Léa Moreau','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:39','default.png','Portraitiste passionnée, je capture les émotions au plus près du réel.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Léa Moreau',NULL,NULL,NULL,NULL,NULL,'2026-01-08 06:44:39'),(3,'hugo.bernard@demo.test','Hugo Bernard','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:39','default.png','Artiste pixel art rétro, je donne vie à tes personnages case par case.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Hugo Bernard',NULL,NULL,NULL,NULL,NULL,'2026-01-18 06:44:39'),(4,'manon.petit@demo.test','Manon Petit','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:39','default.png','Univers kawaii et couleurs pastel : je dessine tout en mignon.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Manon Petit',NULL,NULL,NULL,NULL,NULL,'2026-01-28 06:44:39'),(5,'nathan.dubois@demo.test','Nathan Dubois','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:39','default.png','Concept artist pour jeux vidéo et films d\'animation.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Nathan Dubois',NULL,NULL,NULL,NULL,NULL,'2026-02-07 06:44:39'),(6,'camille.roux@demo.test','Camille Roux','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:39','default.png','Du portrait réaliste au design de tatouage, l\'encre est mon terrain de jeu.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Camille Roux',NULL,NULL,NULL,NULL,NULL,'2026-02-17 06:44:39'),(7,'emma.laurent@demo.test','Emma Laurent','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:39','default.png','Fan d\'anime, je crée des personnages pleins de vie.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Emma Laurent',NULL,NULL,NULL,NULL,NULL,'2026-02-27 06:44:39'),(8,'lucas.girard@demo.test','Lucas Girard','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png','Spécialiste du character design, du croquis à la fiche finale.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Lucas Girard',NULL,NULL,NULL,NULL,NULL,'2026-03-09 06:44:40'),(9,'chloe.fontaine@demo.test','Chloé Fontaine','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png','Illustratrice indépendante, douce et minutieuse.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Chloé Fontaine',NULL,NULL,NULL,NULL,NULL,'2026-03-19 06:44:40'),(10,'theo.mercier@demo.test','Théo Mercier','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png','Pixel art et identité visuelle rétro pour tes projets.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Théo Mercier',NULL,NULL,NULL,NULL,NULL,'2026-03-29 06:44:40'),(11,'sarah.lopez@demo.test','Sarah Lopez','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png','Le tatouage comme récit : chaque pièce raconte une histoire.',NULL,NULL,NULL,NULL,NULL,'artist',0,'approved','Sarah Lopez',NULL,NULL,NULL,NULL,NULL,'2026-04-08 06:44:40'),(12,'julie.bernard@demo.test','Julie Bernard','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png',NULL,'12 rue des Lilas',NULL,'Lyon','69003','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-27 06:44:40'),(13,'thomas.leroy@demo.test','Thomas Leroy','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png',NULL,'5 avenue Victor Hugo',NULL,'Paris','75016','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-09 06:44:40'),(14,'camille.petit@demo.test','Camille Petit','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png',NULL,'8 boulevard Longchamp',NULL,'Marseille','13001','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-19 06:44:40'),(15,'antoine.garnier@demo.test','Antoine Garnier','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png',NULL,'23 rue du Taur',NULL,'Toulouse','31000','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-29 06:44:40'),(16,'sophie.martin@demo.test','Sophie Martin','$2y$10$ecpE00aTCuEehhgW5kGQyu6WSZRqG.W2ywIGdZeVuYSENJfK8mSNi','credentials',NULL,NULL,'2026-07-27 08:44:40','default.png',NULL,'3 quai de la Fosse',NULL,'Nantes','44000','France','user',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-08 06:44:40');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-27  8:44:55
