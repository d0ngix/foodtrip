CREATE DATABASE  IF NOT EXISTS `foodtrip` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `foodtrip`;
-- MySQL dump 10.13  Distrib 5.6.13, for osx10.6 (i386)
--
-- Host: 127.0.0.1    Database: foodtrip
-- ------------------------------------------------------
-- Server version	5.6.14

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_uuid` varchar(50) NOT NULL,
  `longitude` float DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `address_name` varchar(45) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',NULL,NULL,NULL,NULL,NULL,NULL,'test123','2016-05-12 22:49:00','2016-05-14 12:27:56'),(3,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-12 22:52:30',NULL),(4,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',NULL,1123,NULL,NULL,NULL,NULL,NULL,'2016-05-12 22:53:54',NULL),(6,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',1123.1,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-12 23:20:05',NULL),(7,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ',NULL,'Singapore','Philippines','home','2016-05-12 23:20:31',NULL),(8,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ',NULL,'Singapore','Philippines','home','2016-05-12 23:21:37',NULL),(9,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ',NULL,'Singapore','Philippines','home','2016-05-14 18:24:21',NULL),(10,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ',NULL,'Singapore','Philippines','home','2016-05-14 18:24:36',NULL),(11,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ',NULL,'Singapore','Philippines','Office','2016-05-14 18:45:50','2016-05-14 12:35:03'),(12,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ',NULL,'Singapore','Philippines','Office','2016-05-14 20:35:15',NULL),(13,'9fcc6c4e-1ee1-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ',NULL,'Singapore','Philippines','Office','2016-05-21 07:26:58',NULL),(14,'9fcc6c4e-1ee1-11e6-b5fc-d40599f4f9104',123.12,123.12,'the quick brown ','address2','Singapore','Philippines','Office','2016-05-22 15:17:53',NULL),(15,'9fcc6c4e-1ee1-11e6-b5fc-d40599f4f9041',123.12,123.12,'the quick brown ','address2','Singapore','Philippines','Office','2016-05-22 15:18:39',NULL),(16,'9fcc6c4e-1ee1-11e6-b5fc-d40599f4f9041',123.12,123.12,'the quick brown ','address2','Singapore','Philippines','Office','2016-05-22 15:18:42',NULL),(17,'9fcc6c4e-1ee1-11e6-b5fc-d40599f4f904',123.12,123.12,'the quick brown ','address2','Singapore','Philippines','Office','2016-05-22 15:24:41',NULL);
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `addresses_BINS` BEFORE INSERT ON `addresses` FOR EACH ROW
BEGIN 
    SET NEW.created = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `addresses_BUPD` BEFORE UPDATE ON `addresses` FOR EACH ROW
BEGIN 
    SET NEW.modified = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `menu_categories`
--

DROP TABLE IF EXISTS `menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` varchar(45) DEFAULT NULL,
  `image` varchar(45) DEFAULT NULL,
  `is_active` enum('0','1') DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_categories`
--

LOCK TABLES `menu_categories` WRITE;
/*!40000 ALTER TABLE `menu_categories` DISABLE KEYS */;
INSERT INTO `menu_categories` VALUES (1,NULL,1,'Lunch','Menu for lunch',NULL,'1',NULL,NULL),(2,1,1,'Rice Meal','All about rice',NULL,'1',NULL,'2016-05-23 04:10:33'),(3,1,1,'Pasta Meal','Pasta meal',NULL,'1',NULL,'2016-05-23 04:33:59');
/*!40000 ALTER TABLE `menu_categories` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `menu_categories_BINS` BEFORE INSERT ON `menu_categories` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
	SET NEW.created = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `menu_categories_BUPD` BEFORE UPDATE ON `menu_categories` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
	SET NEW.modified = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `menu_category_matrix`
--

DROP TABLE IF EXISTS `menu_category_matrix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_category_matrix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `menu_category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_category_matrix`
--

LOCK TABLES `menu_category_matrix` WRITE;
/*!40000 ALTER TABLE `menu_category_matrix` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_category_matrix` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_images`
--

DROP TABLE IF EXISTS `menu_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `path` varchar(45) NOT NULL,
  `is_primary` tinyint(2) DEFAULT '0',
  `name` varchar(45) NOT NULL,
  `extension` varchar(45) DEFAULT NULL,
  `mime` varchar(45) DEFAULT NULL,
  `size` varchar(45) DEFAULT NULL,
  `md5` varchar(45) DEFAULT NULL,
  `dimensions` varchar(45) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_images`
--

LOCK TABLES `menu_images` WRITE;
/*!40000 ALTER TABLE `menu_images` DISABLE KEYS */;
INSERT INTO `menu_images` VALUES (1,1,'',NULL,'2011-08-13 15.59.13.jpg','jpg','image/jpeg','211298','3ca81a26f686672a9e84209579854d26','{\"width\":875,\"height\":584}','2016-05-23 22:00:13',NULL),(2,1,'public/img//menu/',NULL,'2011-08-13 15.59.13.jpg','jpg','image/jpeg','211298','3ca81a26f686672a9e84209579854d26','{\"width\":875,\"height\":584}','2016-05-23 22:02:29',NULL),(3,1,'public/img//menu/',NULL,'2012-02-10 13.13.44.jpg','jpg','image/jpeg','46550','f89e1386fdd85217a130c283202a2e0b','{\"width\":359,\"height\":280}','2016-05-23 22:02:49',NULL),(4,2,'public/img//menu/',1,'2-2012-02-10 13.13.44.jpg','jpg','image/jpeg','46550','f89e1386fdd85217a130c283202a2e0b','{\"width\":359,\"height\":280}','2016-05-23 22:17:25',NULL),(5,2,'public/img//menu/',1,'2_2012-02-10 13.13.44.jpg','jpg','image/jpeg','46550','f89e1386fdd85217a130c283202a2e0b','{\"width\":359,\"height\":280}','2016-05-23 22:17:45',NULL);
/*!40000 ALTER TABLE `menu_images` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `menu_images_BINS` BEFORE INSERT ON `menu_images` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
	SET NEW.created = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `menu_images_BUPD` BEFORE UPDATE ON `menu_images` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
	SET NEW.modified = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `price` decimal(11,2) DEFAULT NULL,
  `discount` decimal(3,2) DEFAULT NULL,
  `is_active` tinyint(2) DEFAULT '1',
  `likes` text,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,1,'Pork Adobo','Pork Adobo with special seasoning that will sooth your taste. the quick brown fox jumps over the lazy dog over my head near the river bank.',50.00,0.00,1,NULL,'2016-05-23 18:02:52','2016-05-23 10:28:08'),(2,1,'Chicken Adobo','Pork Adobo with special seasoning that will sooth your taste. the quick brown fox jumps over the lazy dog over my head near the river bank.',50.00,0.00,1,NULL,'2016-05-23 18:05:22','2016-05-23 10:28:21'),(3,1,'Lumpia Shanghai','Pork Adobo with special seasoning that will sooth your taste. the quick brown fox jumps over the lazy dog over my head near the river bank.',60.00,0.00,1,NULL,'2016-05-23 18:27:53',NULL);
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `menu_details_BINS` BEFORE INSERT ON `menus` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
	SET NEW.created = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `menu_details_BUPD` BEFORE UPDATE ON `menus` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
	SET NEW.modified = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `user_devices`
--

DROP TABLE IF EXISTS `user_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `device_id` varchar(50) DEFAULT NULL,
  `device_name` varchar(50) DEFAULT NULL,
  `model` varchar(45) DEFAULT NULL,
  `os` varchar(45) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_devices`
--

LOCK TABLES `user_devices` WRITE;
/*!40000 ALTER TABLE `user_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_devices` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `user_devices_BINS` BEFORE INSERT ON `user_devices` FOR EACH ROW
BEGIN 
    SET NEW.modified = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `user_social_media`
--

DROP TABLE IF EXISTS `user_social_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_social_media` (
  `id` int(11) NOT NULL,
  `user_uuid` varchar(40) DEFAULT NULL,
  `facebook` varchar(45) DEFAULT NULL,
  `twitter` varchar(45) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_social_media`
--

LOCK TABLES `user_social_media` WRITE;
/*!40000 ALTER TABLE `user_social_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_social_media` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `user_social_media_BINS` BEFORE INSERT ON `user_social_media` FOR EACH ROW
BEGIN 
    SET NEW.created = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(50) DEFAULT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(500) NOT NULL,
  `first_name` varchar(45) DEFAULT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `gender` enum('M','F') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `photo` varchar(256) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `uuid_UNIQUE` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'d0ngix@yahoo.com8','d0ngix777-123123',NULL,NULL,'M','1982-02-15','97545389',NULL,NULL,'2016-05-01 14:48:25'),(2,'5eb17f4c-0fa0-11e6-b5fc-d40599f4f904','d0ngix@yahoo.com','$2y$12$QZPriNvtS2M9s3FaMM7ynuqFwuYus2Jn6FBlRJPZVn.ED93JJkoyK',NULL,NULL,'M','1982-02-15','97545389123321','{\"name\":\"5eb17f4c-0fa0-11e6-b5fc-d40599f4f904.jpg\",\"extension\":\"jpg\",\"mime\":\"image\\/jpeg\",\"size\":187965,\"md5\":\"452260729074642dda0849e155237e16\",\"dimensions\":\"{\\\"width\\\":525,\\\"height\\\":350}\",\"path\":\"public\\/img\\/\\/user\\/\"}',NULL,'2016-05-23 14:08:43'),(3,'5c5178c6-0fa3-11e6-b5fc-d40599f4f904','d0ngix@yahoo.com1','$2y$12$shG27PEAzz/TEI9FDQlbYuK7mdLyO/JaQ/o8x3282oUI05tjR24Ki',NULL,NULL,'M','1982-02-15','97545389',NULL,NULL,NULL),(4,'6b13f9b0-0fa3-11e6-b5fc-d40599f4f904','d0ngix@yahoo.com2','$2y$12$eigkYMfQvKTXTGn2.vuSDelKRjw2ZO6BZWxlM1yVhkKqbLx4HPdQa',NULL,NULL,'M','1982-02-15','97545389',NULL,NULL,NULL),(5,'7c322e74-0fa3-11e6-b5fc-d40599f4f904','d0ngix@yahoo.com3','$2y$12$P6k3xnLYC3ANnIuNnco5UeKzuo6qbPakT/T.cAvG8e1iP2Og7Ecwq',NULL,NULL,'M','1982-02-15','97545389',NULL,NULL,NULL),(6,'942ea96c-0fa3-11e6-b5fc-d40599f4f904','d0ngix@yahoo.com4','$2y$12$wVd0Yto3cB/1p5uOGere1eFJ7yOx.zdk2Vhfb2h7oFvnbX5zjoLvq',NULL,NULL,'M','1982-02-15','97545389',NULL,NULL,NULL),(7,'7cc6ea52-0fab-11e6-b5fc-d40599f4f904','d0ngix@yahoo.com5','$2y$12$XTYhCzNmyRiUrJx/FcVzz.JZhfhIwGDmDI3kof59GcN0bKIB5iiRW',NULL,NULL,'M','1982-02-15','97545381',NULL,'2016-05-01 22:46:28',NULL),(8,'6eeba3de-0fae-11e6-b5fc-d40599f4f904','d0ngix@gmail.com','$2y$12$iKZWH1WfYQ5iIL42Oyvph.ARYVq10AK8RqgG8pErvvyqwQchL/EDS','d0ngix','mabulay','M','1982-02-15','97545389',NULL,'2016-05-01 23:07:34',NULL),(9,'63d029ea-1a79-11e6-b5fc-d40599f4f904','d0ngix@gmail.com1','$2y$12$pz4ocAjepczPbBd1HCzyzuRC6IIgm4MZO6xvWeFIeoMGdPAMttysK','d0ngix','mabulay','M','1982-02-15','97545389',NULL,'2016-05-15 16:45:34',NULL),(10,'9fcc6c4e-1ee1-11e6-b5fc-d40599f4f904','d0ngix@gmail.com2','$2y$12$Remn8WM9JacwXLJ373NIre90G8TfOlBpevuSw5KIv5.E7Lr4qF832','d0ngix','mabulay','M','1982-02-15','97545389',NULL,'2016-05-21 07:21:47',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `users_BINS` BEFORE INSERT ON `users` FOR EACH ROW
BEGIN 
    SET NEW.uuid = UUID(); 
    SET NEW.created = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `users_BUPD` BEFORE UPDATE ON `users` FOR EACH ROW
BEGIN 
    SET NEW.modified = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `logo` varchar(45) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid_UNIQUE` (`uuid`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (1,'5cc68750-20ca-11e6-b5fc-d40599f4f904','Suki Carenderia','x@y.com',NULL,'2016-05-23 17:40:19',NULL);
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `vendors_BINS` BEFORE INSERT ON `vendors` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
    SET NEW.uuid = UUID(); 
    SET NEW.created = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `vendors_BUPD` BEFORE UPDATE ON `vendors` FOR EACH ROW
-- Edit trigger body code below this line. Do not edit lines above this one
BEGIN
	SET NEW.modified = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-05-23 23:14:08
