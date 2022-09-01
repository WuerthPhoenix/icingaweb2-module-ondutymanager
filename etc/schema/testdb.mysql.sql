-- MySQL dump 10.17  Distrib 10.3.13-MariaDB, for Linux (x86_64)
--
-- Host: mariadb.neteyelocal    Database: ondutymanager
-- ------------------------------------------------------
-- Server version	10.3.13-MariaDB

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
-- Table structure for table `color`
--

DROP TABLE IF EXISTS `color`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `color` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `color`
--

LOCK TABLES `color` WRITE;
/*!40000 ALTER TABLE `color` DISABLE KEYS */;
INSERT INTO `color` VALUES (1,'orange','#ff7000'),(2,'light-blue','#3f9fff'),(3,'dark-blue','#005ab4'),(4,'gray','#8e8e8e'),(5,'light-green','#abd636'),(6,'light-red','#d6184a'),(7,'white','white'),(8,'creme','#fdf4e3');
/*!40000 ALTER TABLE `color` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `team_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `user_name` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calendar_week` int(10) unsigned NOT NULL,
  `calendar_year` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_schedule_template` (`template_id`),
  KEY `fk_schedule_icinga_user` (`user_id`),
  KEY `fk_schedule_icinga_team` (`team_id`),
  CONSTRAINT `fk_schedule_icinga_team` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`),
  CONSTRAINT `fk_schedule_icinga_user` FOREIGN KEY (`user_id`) REFERENCES `director`.`icinga_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_schedule_template` FOREIGN KEY (`template_id`) REFERENCES `template` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3628 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team`
--

DROP TABLE IF EXISTS `team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_weekday` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_cycle_time` time NOT NULL,
  `usergroup_id` int(10) unsigned DEFAULT NULL,
  `holiday_template_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_team_icinga_usergroup` (`usergroup_id`),
  KEY `fk_team_template` (`holiday_template_id`),
  CONSTRAINT `fk_team_icinga_usergroup` FOREIGN KEY (`usergroup_id`) REFERENCES `director`.`icinga_usergroup` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_team_template` FOREIGN KEY (`holiday_template_id`) REFERENCES `template` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team`
--

LOCK TABLES `team` WRITE;
/*!40000 ALTER TABLE `team` DISABLE KEYS */;
INSERT INTO `team` VALUES (1,'Team Bernd','Saturday','07:00:00',1,NULL),(2,'Team Angela','Saturday','07:00:00',2,NULL);
/*!40000 ALTER TABLE `team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_icinga_timeperiod`
--

DROP TABLE IF EXISTS `team_icinga_timeperiod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_icinga_timeperiod` (
  `team_id` int(10) unsigned NOT NULL,
  `icinga_timeperiod_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`team_id`,`icinga_timeperiod_id`),
  KEY `fk_team_icinga_timeperiod_team` (`team_id`),
  KEY `fk_team_icinga_timeperiod_icinga_timeperiod` (`icinga_timeperiod_id`),
  CONSTRAINT `fk_team_icinga_timeperiod_icinga_timeperiod` FOREIGN KEY (`icinga_timeperiod_id`) REFERENCES `director`.`icinga_timeperiod` (`id`),
  CONSTRAINT `fk_team_icinga_timeperiod_team` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `template`
--

DROP TABLE IF EXISTS `template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_id` int(10) unsigned NOT NULL,
  `team_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_template_team` (`team_id`),
  KEY `fk_template_color` (`color_id`),
  CONSTRAINT `fk_template_color` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`),
  CONSTRAINT `fk_template_team` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template`
--

LOCK TABLES `template` WRITE;
/*!40000 ALTER TABLE `template` DISABLE KEYS */;
INSERT INTO `template` VALUES (2,'Abw. Bereitschaft Sa./So.',2,1),(3,'Bereitschaft',3,1),(4,'Normalschicht / Backup',4,1),(5,'Spätschicht',5,1),(7,'Normalschicht',7,1),(8,'Frühschicht',8,1),(10,'Bereitschaft',6,2);
/*!40000 ALTER TABLE `template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timetemplate`
--

DROP TABLE IF EXISTS `timetemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timetemplate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned NOT NULL,
  `weekday` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_timetemplate_team` (`template_id`),
  CONSTRAINT `fk_timetemplate_team` FOREIGN KEY (`template_id`) REFERENCES `template` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timetemplate`
--

LOCK TABLES `timetemplate` WRITE;
/*!40000 ALTER TABLE `timetemplate` DISABLE KEYS */;
INSERT INTO `timetemplate` VALUES (12,2,'Saturday','07:00:00'),(13,2,'Sunday','00:00:00'),(14,2,'Monday','00:00:00'),(15,8,'Monday','05:00:00'),(16,4,'Monday','08:00:00'),(17,7,'Monday','13:00:00'),(18,5,'Monday','16:00:00'),(19,3,'Monday','21:30:00'),(20,3,'Tuesday','00:00:00'),(21,8,'Tuesday','05:00:00'),(22,4,'Tuesday','08:00:00'),(23,7,'Tuesday','13:00:00'),(24,5,'Tuesday','16:00:00'),(25,3,'Tuesday','21:30:00'),(26,3,'Wednesday','00:00:00'),(27,8,'Wednesday','05:00:00'),(28,4,'Wednesday','08:00:00'),(29,7,'Wednesday','13:00:00'),(30,5,'Wednesday','16:00:00'),(31,3,'Wednesday','21:30:00'),(32,3,'Thursday','00:00:00'),(33,8,'Thursday','05:00:00'),(34,4,'Thursday','08:00:00'),(35,5,'Thursday','16:00:00'),(36,7,'Thursday','13:00:00'),(37,3,'Thursday','21:30:00'),(38,3,'Friday','00:00:00'),(39,8,'Friday','05:00:00'),(40,4,'Friday','08:00:00'),(41,5,'Friday','13:00:00'),(43,3,'Friday','21:30:00'),(44,3,'Saturday','00:00:00'),(45,10,'Tuesday','00:00:00');
/*!40000 ALTER TABLE `timetemplate` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-08-25 13:53:58
