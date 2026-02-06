-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: localhost    Database: cpt_dev
-- ------------------------------------------------------
-- Server version	5.5.5-10.5.29-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account` (
  `acco_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `acco_email` varchar(300) NOT NULL,
  `acco_password` varchar(64) NOT NULL,
  `acco_status` tinyint(4) NOT NULL DEFAULT 1,
  `acco_role` enum('admin','student','professor') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_login` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`acco_id`),
  UNIQUE KEY `acco_email_UNIQUE` (`acco_email`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account`
--

LOCK TABLES `account` WRITE;
/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `account` VALUES (100,'ikerantonioplumaamaro@gmail.com','4f6a9042509812f7918f33168e267cc67049c770df1c09bb8b23a84a31a7b987',1,'professor','2026-01-18 05:19:16',0),(108,'admin@cpt.ipn.mx','0a2029967180a22bc58925237d3307476b0f5b2d05474527a2c0e151acd586b1',1,'admin','2026-01-21 05:05:05',0),(111,'iplumaa2100@alumno.ipn.mx','5974b43cd8b0211a36ae11fc2fb0b0ad5531d74e0f505456a48bdb6910bac3c0',1,'student','2026-01-22 05:57:20',0),(112,'cordovamario193@gmail.com','b5df267d6470b834f53b2b10af2160d4ded6200b5da6b0e47233077bc8db6ff0',1,'professor','2026-01-22 06:03:39',0),(114,'mcordovacsx@gmail.com','6e5f48b5fca96c0c61fa0fc87d88e221d82ffd5b4d073c9ac543777350deb3b0',1,'professor','2026-01-22 18:01:12',0),(116,'arianatorres2924@gmail.com','dda199f37a961be7bb532645714ec08e58797eafce75f9e31aa3627d642f1d74',1,'student','2026-01-25 05:16:23',0),(118,'cordovasusy94@gmail.com','dda199f37a961be7bb532645714ec08e58797eafce75f9e31aa3627d642f1d74',1,'student','2026-01-25 11:58:53',0),(119,'electroconnor1234@alumno.ipn.mx','5e51239fe4d99b284925e0648aead413b96e9acdb2dc4b15ae6bef34e25b7874',1,'student','2026-01-26 05:21:42',1),(120,'mcordovac2100@alumno.ipn.mx','d4559a33001a4fdf8c006fb037f02eb613ef4e61f8809990cbd0b2c04bd93f7a',1,'student','2026-01-26 21:12:51',0);
/*!40000 ALTER TABLE `account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assigment`
--

DROP TABLE IF EXISTS `assigment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assigment` (
  `id_assigment` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(240) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `file_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_class` int(10) unsigned NOT NULL,
  `id_professor` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_assigment`,`id_class`),
  KEY `fk_assigment_class_idx` (`id_class`),
  KEY `fk_assigment_professor_idx` (`id_professor`),
  CONSTRAINT `fk_assigment_class` FOREIGN KEY (`id_class`) REFERENCES `class` (`id_class`) ON UPDATE CASCADE,
  CONSTRAINT `fk_assigment_professor` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_professor`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assigment`
--

LOCK TABLES `assigment` WRITE;
/*!40000 ALTER TABLE `assigment` DISABLE KEYS */;
INSERT INTO `assigment` VALUES (1,'Hola, tarea preuba','Esta es la primer tarea que se puede ver','2026-01-25 01:29:34','https://sales360dev.n2a.mx:63822/CPT/uploads/protocols/10/protocol_6971f40d2a2bf5.51300843.pdf','2025-01-01 06:00:00',8,11),(4,'Prueba con interfaz','','2026-02-06 09:35:00','https://sales360dev.n2a.mx:63822/CPT/api/v1/uploads/assigments/?file_name=assignment_69758f7fa83e81.67519670.pdf&id_class=8&id_professor=9','2026-01-25 03:35:27',8,9),(5,'flkadsflkds','fklasdjflkdas','2026-02-04 09:37:00',NULL,'2026-01-25 03:38:36',8,9),(6,'Hola, es una tarea JAJALOL','Acá hagan','2026-03-02 15:26:00',NULL,'2026-01-25 21:26:14',8,3),(7,'Estado del arte','Acá subirás ...','2026-01-29 09:32:00',NULL,'2026-01-26 21:33:09',9,9);
/*!40000 ALTER TABLE `assigment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assigment_submission`
--

DROP TABLE IF EXISTS `assigment_submission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assigment_submission` (
  `id_assigment_submission` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_url` varchar(45) NOT NULL,
  `grade` float NOT NULL DEFAULT 0,
  `id_assigment` int(10) unsigned NOT NULL,
  `graded_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id_student` int(10) unsigned NOT NULL,
  `feedback` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_assigment_submission`),
  KEY `fk_assigment_submission_assigment_idx` (`id_assigment`),
  KEY `fk_assigment_submission_student_idx` (`id_student`),
  CONSTRAINT `fk_assigment_submission_assigment` FOREIGN KEY (`id_assigment`) REFERENCES `assigment` (`id_assigment`) ON UPDATE CASCADE,
  CONSTRAINT `fk_assigment_submission_student` FOREIGN KEY (`id_student`) REFERENCES `fp_student` (`id_fp_student`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assigment_submission`
--

LOCK TABLES `assigment_submission` WRITE;
/*!40000 ALTER TABLE `assigment_submission` DISABLE KEYS */;
/*!40000 ALTER TABLE `assigment_submission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_events` (
  `id_calendar_events` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stage` enum('upload_protocols','assign_reviewers','judge_protocols','re-upload_protocols','select_protocols','protocol_presentations','grade_protocols','second_protocol_presentations','grade_second_protocols') NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id_career` int(10) unsigned NOT NULL,
  `spring_semester` tinyint(4) NOT NULL,
  `year` int(11) NOT NULL,
  PRIMARY KEY (`id_calendar_events`),
  KEY `fk_calendary_career_idx` (`id_career`),
  CONSTRAINT `fk_calendary_career` FOREIGN KEY (`id_career`) REFERENCES `career` (`id_career`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
INSERT INTO `calendar_events` VALUES (1,'upload_protocols','2026-01-01 06:00:00','2027-01-01 06:00:00',1,1,2025),(2,'upload_protocols','2026-01-01 06:00:00','2027-01-01 06:00:00',1,0,2025);
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career`
--

DROP TABLE IF EXISTS `career`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career` (
  `id_career` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `career` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_career`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career`
--

LOCK TABLES `career` WRITE;
/*!40000 ALTER TABLE `career` DISABLE KEYS */;
INSERT INTO `career` VALUES (1,'Inteligencia Artificial','2025-11-24 05:31:52');
/*!40000 ALTER TABLE `career` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class`
--

DROP TABLE IF EXISTS `class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class` (
  `id_class` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `id_career` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_class`),
  KEY `fk_class_career_idx` (`id_career`),
  CONSTRAINT `fk_class_career` FOREIGN KEY (`id_career`) REFERENCES `career` (`id_career`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class`
--

LOCK TABLES `class` WRITE;
/*!40000 ALTER TABLE `class` DISABLE KEYS */;
INSERT INTO `class` VALUES (1,'Clase prueba',1,'2026-01-22 07:28:07'),(6,'Clase Interfaz',1,'2026-01-22 17:55:38'),(7,'Clase con 0',1,'2026-01-22 17:57:14'),(8,'Clase con 2 después',1,'2026-01-22 18:09:59'),(9,'Trabajo terminal I',1,'2026-01-26 21:31:31');
/*!40000 ALTER TABLE `class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_professor`
--

DROP TABLE IF EXISTS `class_professor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_professor` (
  `id_class_professor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_class` int(10) unsigned NOT NULL,
  `id_professor` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_class_professor`),
  KEY `fk_class_professor_professor_idx` (`id_professor`),
  KEY `fk_class_professor_class_idx` (`id_class`),
  CONSTRAINT `fk_class_professor_class` FOREIGN KEY (`id_class`) REFERENCES `class` (`id_class`) ON UPDATE CASCADE,
  CONSTRAINT `fk_class_professor_professor` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_professor`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_professor`
--

LOCK TABLES `class_professor` WRITE;
/*!40000 ALTER TABLE `class_professor` DISABLE KEYS */;
INSERT INTO `class_professor` VALUES (1,1,11),(2,1,3),(6,6,3),(7,6,9),(8,6,11),(21,8,3),(22,8,9),(23,9,3),(24,9,9);
/*!40000 ALTER TABLE `class_professor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `final_project`
--

DROP TABLE IF EXISTS `final_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `final_project` (
  `id_final_project` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_career` int(10) unsigned NOT NULL,
  `title` varchar(256) NOT NULL,
  `abstract` text NOT NULL,
  `status` enum('PENDING','UNDER_REVIEW','APPROVED','REJECTED') DEFAULT NULL,
  PRIMARY KEY (`id_final_project`),
  KEY `fk_final_project_career_idx` (`id_career`),
  CONSTRAINT `fk_final_project_career` FOREIGN KEY (`id_career`) REFERENCES `career` (`id_career`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `final_project`
--

LOCK TABLES `final_project` WRITE;
/*!40000 ALTER TABLE `final_project` DISABLE KEYS */;
INSERT INTO `final_project` VALUES (7,1,'Sistema de analisis de inventarios','Un sistema que analice el inventario','UNDER_REVIEW'),(8,1,'Algoritmos bioinspirados','Bios','UNDER_REVIEW'),(9,1,'algoritmos bioinspirados para redes neuronales','Este es un proyecto para bla...','APPROVED');
/*!40000 ALTER TABLE `final_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fp_advisor`
--

DROP TABLE IF EXISTS `fp_advisor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fp_advisor` (
  `id_fp_advisor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_professor` int(10) unsigned NOT NULL,
  `id_final_project` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_fp_advisor`),
  KEY `fk_fp_advisor_final_project_idx` (`id_final_project`),
  KEY `fk_fp_advisor_professor_idx` (`id_professor`),
  CONSTRAINT `fk_fp_advisor_final_project` FOREIGN KEY (`id_final_project`) REFERENCES `final_project` (`id_final_project`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fp_advisor_professor` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_professor`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fp_advisor`
--

LOCK TABLES `fp_advisor` WRITE;
/*!40000 ALTER TABLE `fp_advisor` DISABLE KEYS */;
INSERT INTO `fp_advisor` VALUES (5,9,7),(6,13,7),(11,9,8),(12,11,8),(15,11,9),(16,3,9);
/*!40000 ALTER TABLE `fp_advisor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fp_change`
--

DROP TABLE IF EXISTS `fp_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fp_change` (
  `id_fp_change` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_final_project` int(10) unsigned NOT NULL,
  `stage` int(10) unsigned NOT NULL DEFAULT 1,
  `file_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_fp_change`),
  KEY `fk_fp_change_final_project_idx` (`id_final_project`),
  CONSTRAINT `fk_fp_change_final_project` FOREIGN KEY (`id_final_project`) REFERENCES `final_project` (`id_final_project`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fp_change`
--

LOCK TABLES `fp_change` WRITE;
/*!40000 ALTER TABLE `fp_change` DISABLE KEYS */;
INSERT INTO `fp_change` VALUES (3,7,1,'/CPT/uploads/protocols/12/protocol_6975a913938f19.98018765.pdf','2026-01-25 05:24:35'),(5,8,1,'/CPT/uploads/protocols/14/protocol_69767a6e49f855.42882250.pdf','2026-01-25 20:17:50'),(10,8,2,'/CPT/uploads/protocols/14/protocol_69767df02012a4.40554735.pdf','2026-01-25 20:32:48'),(11,9,1,'/CPT/uploads/protocols/11/protocol_6977da05df6b83.79549677.pdf','2026-01-26 21:17:57'),(12,9,2,'/CPT/uploads/protocols/11/protocol_6977dc76afff87.44899673.pdf','2026-01-26 21:28:22');
/*!40000 ALTER TABLE `fp_change` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fp_change_review`
--

DROP TABLE IF EXISTS `fp_change_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fp_change_review` (
  `id_fp_change_review` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_professor` int(10) unsigned NOT NULL,
  `id_fp_change` int(10) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `file_url` text NOT NULL,
  `reviewer_pdf_url` varchar(500) DEFAULT NULL,
  `grade` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_fp_change_review`),
  KEY `fk_fp_change_rewiew_change_idx` (`id_fp_change`),
  KEY `fk_fp_change_review_professor_idx` (`id_professor`),
  CONSTRAINT `fk_fp_change_review_change` FOREIGN KEY (`id_fp_change`) REFERENCES `fp_change` (`id_fp_change`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fp_change_review_professor` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_professor`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fp_change_review`
--

LOCK TABLES `fp_change_review` WRITE;
/*!40000 ALTER TABLE `fp_change_review` DISABLE KEYS */;
INSERT INTO `fp_change_review` VALUES (28,11,3,NULL,'/CPT/uploads/protocols/12/protocol_6975a913938f19.98018765.pdf',NULL,NULL,'2026-01-25 08:54:49'),(29,9,3,NULL,'/CPT/uploads/protocols/12/protocol_6975a913938f19.98018765.pdf',NULL,NULL,'2026-01-25 08:54:49'),(30,3,3,NULL,'/CPT/uploads/protocols/12/protocol_6975a913938f19.98018765.pdf',NULL,NULL,'2026-01-25 08:54:49'),(37,11,5,'Bien','/CPT/uploads/protocols/14/protocol_69767a6e49f855.42882250.pdf','/CPT/uploads/reviews/dictamen_37_69767b6a7251d.pdf',1,'2026-01-25 20:18:40'),(38,13,5,'','/CPT/uploads/protocols/14/protocol_69767a6e49f855.42882250.pdf','/CPT/uploads/reviews/dictamen_38_69767b986273d.pdf',0,'2026-01-25 20:18:40'),(39,9,5,'No aprobado por tonto','/CPT/uploads/protocols/14/protocol_69767a6e49f855.42882250.pdf','/CPT/uploads/reviews/dictamen_39_69767aefbeea2.pdf',0,'2026-01-25 20:18:40'),(43,11,10,NULL,'/CPT/uploads/protocols/14/protocol_69767df02012a4.40554735.pdf',NULL,NULL,'2026-01-25 20:57:15'),(44,13,10,NULL,'/CPT/uploads/protocols/14/protocol_69767df02012a4.40554735.pdf',NULL,NULL,'2026-01-25 20:57:15'),(45,9,10,NULL,'/CPT/uploads/protocols/14/protocol_69767df02012a4.40554735.pdf',NULL,NULL,'2026-01-25 20:57:15'),(46,9,11,'eres el mejor','/CPT/uploads/protocols/11/protocol_6977da05df6b83.79549677.pdf','/CPT/uploads/reviews/dictamen_46_6977db6f6885a.pdf',1,'2026-01-26 21:21:48'),(47,11,11,'no aceptado','/CPT/uploads/protocols/11/protocol_6977da05df6b83.79549677.pdf','/CPT/uploads/reviews/dictamen_47_6977dbe9b8872.pdf',0,'2026-01-26 21:21:48'),(48,3,11,'no','/CPT/uploads/protocols/11/protocol_6977da05df6b83.79549677.pdf','/CPT/uploads/reviews/dictamen_48_6977dc0b98d52.pdf',0,'2026-01-26 21:21:48'),(49,9,12,'perfecto','/CPT/uploads/protocols/11/protocol_6977dc76afff87.44899673.pdf','/CPT/uploads/reviews/dictamen_49_6977dcb81f8ce.pdf',1,'2026-01-26 21:28:22'),(50,11,12,'todo bien','/CPT/uploads/protocols/11/protocol_6977dc76afff87.44899673.pdf','/CPT/uploads/reviews/dictamen_50_6977dcb37a51c.pdf',1,'2026-01-26 21:28:22'),(51,3,12,'bien','/CPT/uploads/protocols/11/protocol_6977dc76afff87.44899673.pdf','/CPT/uploads/reviews/dictamen_51_6977dcdf30eb1.pdf',1,'2026-01-26 21:28:22');
/*!40000 ALTER TABLE `fp_change_review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fp_judge`
--

DROP TABLE IF EXISTS `fp_judge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fp_judge` (
  `id_fp_judge` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_final_project` int(10) unsigned NOT NULL,
  `id_professor` int(10) unsigned NOT NULL,
  `rating` float NOT NULL,
  `extra` tinyint(4) NOT NULL DEFAULT 0,
  `stage` enum('1','2') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_fp_judge`),
  KEY `fk_fp_judge_professor_idx` (`id_professor`),
  KEY `fk_fp_judge_final_project_idx` (`id_final_project`),
  CONSTRAINT `fk_fp_judge_final_project` FOREIGN KEY (`id_final_project`) REFERENCES `final_project` (`id_final_project`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fp_judge_professor` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_professor`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fp_judge`
--

LOCK TABLES `fp_judge` WRITE;
/*!40000 ALTER TABLE `fp_judge` DISABLE KEYS */;
/*!40000 ALTER TABLE `fp_judge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fp_reviewer`
--

DROP TABLE IF EXISTS `fp_reviewer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fp_reviewer` (
  `id_fp_reviewer` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_final_project` int(10) unsigned NOT NULL,
  `id_professor` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_fp_reviewer`),
  KEY `fk_fp_reviewer_professor_idx` (`id_professor`),
  KEY `fk_fp_reviewer_final_project_idx` (`id_final_project`),
  CONSTRAINT `fk_fp_reviewer_final_project` FOREIGN KEY (`id_final_project`) REFERENCES `final_project` (`id_final_project`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fp_reviewer_professor` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_professor`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fp_reviewer`
--

LOCK TABLES `fp_reviewer` WRITE;
/*!40000 ALTER TABLE `fp_reviewer` DISABLE KEYS */;
/*!40000 ALTER TABLE `fp_reviewer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fp_student`
--

DROP TABLE IF EXISTS `fp_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fp_student` (
  `id_fp_student` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_final_project` int(10) unsigned NOT NULL,
  `id_student` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_fp_student`),
  KEY `fk_fp_student_final_project_idx` (`id_final_project`),
  KEY `fk_fp_student_student_idx` (`id_student`),
  CONSTRAINT `fk_fp_student_final_project` FOREIGN KEY (`id_final_project`) REFERENCES `final_project` (`id_final_project`) ON UPDATE CASCADE,
  CONSTRAINT `fk_fp_student_student` FOREIGN KEY (`id_student`) REFERENCES `student` (`id_student`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fp_student`
--

LOCK TABLES `fp_student` WRITE;
/*!40000 ALTER TABLE `fp_student` DISABLE KEYS */;
INSERT INTO `fp_student` VALUES (7,7,12),(8,8,14),(9,9,11);
/*!40000 ALTER TABLE `fp_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pending_token`
--

DROP TABLE IF EXISTS `pending_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pending_token` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `acco_email` varchar(300) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `duration` int(11) NOT NULL DEFAULT 3600,
  `token` varchar(256) NOT NULL,
  `type` enum('PASSWORD') NOT NULL DEFAULT 'PASSWORD',
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pending_token`
--

LOCK TABLES `pending_token` WRITE;
/*!40000 ALTER TABLE `pending_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `pending_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `professor`
--

DROP TABLE IF EXISTS `professor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `professor` (
  `id_professor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `acco_id` int(10) unsigned NOT NULL,
  `is_president` tinyint(4) NOT NULL DEFAULT 0,
  `academia` varchar(256) NOT NULL,
  `level_of_education` enum('master''s','bachelor''s','doctorate') NOT NULL DEFAULT 'bachelor''s',
  `curp` varchar(18) NOT NULL,
  `name` varchar(512) NOT NULL,
  `is_advisor` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_professor`),
  UNIQUE KEY `acco_id_UNIQUE` (`acco_id`),
  UNIQUE KEY `curp_UNIQUE` (`curp`),
  KEY `fk_professor_account_idx` (`acco_id`),
  CONSTRAINT `fk_professor_account` FOREIGN KEY (`acco_id`) REFERENCES `account` (`acco_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `professor`
--

LOCK TABLES `professor` WRITE;
/*!40000 ALTER TABLE `professor` DISABLE KEYS */;
INSERT INTO `professor` VALUES (3,100,1,'Computación','doctorate','PUAI030822HTLLMKA2','José Garcia',1),(9,108,0,'Computación','doctorate','PUAI030822HTLLMKA3','Administrador',1),(11,112,1,'Computo','master\'s','COCM031027HTLRLRA3','Fernando Cordova Calva',1),(13,114,0,'Computo','doctorate','COCM031027HTLRLRA7','Saul Cordova Calva',1),(15,116,0,'Computo','doctorate','COCM031027HTLRLRA4','Axel Cordova Calva',0);
/*!40000 ALTER TABLE `professor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student` (
  `id_student` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_career` int(10) unsigned NOT NULL,
  `acco_id` int(10) unsigned NOT NULL,
  `school_id_number` varchar(10) NOT NULL,
  `id_class` int(10) unsigned DEFAULT NULL,
  `name` varchar(512) NOT NULL,
  `curp` varchar(18) NOT NULL,
  PRIMARY KEY (`id_student`),
  UNIQUE KEY `school_id_number_UNIQUE` (`school_id_number`),
  UNIQUE KEY `curp_UNIQUE` (`curp`),
  KEY `fk_student_career_idx` (`id_career`),
  KEY `fk_student_account_idx` (`acco_id`),
  KEY `fk_student_class_idx` (`id_class`),
  CONSTRAINT `fk_student_account` FOREIGN KEY (`acco_id`) REFERENCES `account` (`acco_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_student_career` FOREIGN KEY (`id_career`) REFERENCES `career` (`id_career`) ON UPDATE CASCADE,
  CONSTRAINT `fk_student_class` FOREIGN KEY (`id_class`) REFERENCES `class` (`id_class`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student`
--

LOCK TABLES `student` WRITE;
/*!40000 ALTER TABLE `student` DISABLE KEYS */;
INSERT INTO `student` VALUES (11,1,111,'2022710222',8,'Iker Antonio Pluma Amaro','PUAI030822HTLLMKA5'),(12,1,116,'2023311500',6,'Axel Cordova Calva','CURP123456HTLRLA3'),(14,1,118,'2023311560',6,'Susana Cordova Calva','CURP123456HTLRLA7'),(15,1,119,'2022710223',9,'Iker Antonio Pluma Amaro','PUAI030822HTLLMKA2'),(16,1,120,'2022311400',NULL,'Mario Cordova Cordova','PUAI030822HTLLMKA1');
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-06  4:18:12
