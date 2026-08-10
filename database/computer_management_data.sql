/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.18-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: computer_management
-- ------------------------------------------------------
-- Server version	10.11.18-MariaDB-0+deb12u1

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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(10) unsigned DEFAULT NULL,
  `old_value` mediumtext DEFAULT NULL COMMENT 'JSON snapshot of the old state',
  `new_value` mediumtext DEFAULT NULL COMMENT 'JSON snapshot of the new state',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_user` (`user_id`),
  KEY `idx_logs_table_record` (`table_name`,`record_id`),
  KEY `idx_logs_created` (`created_at`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Activity audit log';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES
(1,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 09:42:55'),
(2,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 09:44:14'),
(3,1,'admin','Logged out','users',1,NULL,NULL,'127.0.0.1','2026-08-09 09:44:54'),
(4,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 09:45:15'),
(5,1,'admin','Added user \"ram\" with role staff','users',2,NULL,'{\"username\":\"ram\",\"full_name\":\"System Administrator\",\"email\":\"admin@example.com\",\"role\":\"staff\",\"status\":\"active\"}','127.0.0.1','2026-08-09 09:45:53'),
(6,2,'ram','Logged in','users',2,NULL,NULL,'127.0.0.1','2026-08-09 09:47:16'),
(7,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 09:47:19'),
(8,2,'ram','Updated own profile','users',2,NULL,NULL,'127.0.0.1','2026-08-09 09:48:37'),
(9,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 09:52:55'),
(10,1,'admin','Added computer PC001','computers',1,NULL,'{\"computer_id\":\"PC001\",\"cpu\":\"\",\"ram\":\"\",\"storage_capacity\":\"\",\"remarks\":\"idk\",\"monitor_condition\":\"Not Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\",\"lab_id\":1}','127.0.0.1','2026-08-09 09:58:47'),
(11,1,'admin','Added computer PC001','computers',2,NULL,'{\"computer_id\":\"PC001\",\"cpu\":\"\",\"ram\":\"\",\"storage_capacity\":\"\",\"remarks\":\"\",\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Working\",\"lab_id\":2}','127.0.0.1','2026-08-09 09:58:59'),
(12,1,'admin','Added computer PC001','computers',3,NULL,'{\"computer_id\":\"PC001\",\"cpu\":\"\",\"ram\":\"\",\"storage_capacity\":\"\",\"remarks\":\"\",\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Working\",\"lab_id\":3}','127.0.0.1','2026-08-09 09:59:08'),
(13,1,'admin','Added computer PC002','computers',4,NULL,'{\"computer_id\":\"PC002\",\"cpu\":\"\",\"ram\":\"\",\"storage_capacity\":\"\",\"remarks\":\"\",\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Working\",\"lab_id\":3}','127.0.0.1','2026-08-09 09:59:13'),
(14,1,'admin','Duplicated computer PC001 as PC002','computers',5,'PC001','PC002','127.0.0.1','2026-08-09 09:59:18'),
(15,1,'admin','Duplicated computer PC002 as PC003','computers',6,'PC002','PC003','127.0.0.1','2026-08-09 09:59:22'),
(16,1,'admin','Duplicated computer PC003 as PC004','computers',7,'PC003','PC004','127.0.0.1','2026-08-09 09:59:24'),
(17,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Not Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 09:58:47\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Working\"}','127.0.0.1','2026-08-09 10:03:42'),
(18,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:03:42\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:05:43'),
(19,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:07:44'),
(20,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:08:02'),
(21,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:08:17'),
(22,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:08:46'),
(23,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:09:28'),
(24,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:09:41'),
(25,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:09:56'),
(26,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:10:44'),
(27,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 10:10:57'),
(28,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:05:43\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:22:07'),
(29,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:38:28'),
(30,3,'pwtest','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:05:43\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:38:35'),
(31,3,'pwtest','Reported issue #7 for computer PC001','issues',7,NULL,'{\"category\":\"Monitor\",\"description\":\"Test issue from automation\"}','127.0.0.1','2026-08-09 10:38:35'),
(32,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":3,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:38:35\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:38:47'),
(33,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 10:40:36'),
(34,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:41:20'),
(35,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:38:47\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:42:44'),
(36,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:45:53'),
(37,3,'pwtest','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:38:47\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:45:58'),
(38,3,'pwtest','Reported issue #8 for computer PC001','issues',8,NULL,'{\"category\":\"Other\",\"description\":\"Screen flickers when turning on\"}','127.0.0.1','2026-08-09 10:45:58'),
(39,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 10:47:02'),
(40,3,'pwtest','Updated computer PC001 (condition/status)','computers',2,'{\"id\":2,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":2,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Working\",\"remarks\":\"\",\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-08-09 09:58:59\",\"updated_at\":\"2026-08-09 09:58:59\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:47:04'),
(41,3,'pwtest','Reported issue #9 for computer PC001','issues',9,NULL,'{\"category\":\"Other\",\"description\":\"Computer status set to \\\"Not Working\\\".\"}','127.0.0.1','2026-08-09 10:47:04'),
(42,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":3,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:45:58\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:51:24'),
(43,2,'ram','Reported issue #10 for computer PC001','issues',10,NULL,'{\"category\":\"Other\",\"description\":\"Computer status set to \\\"Not Working\\\".\"}','127.0.0.1','2026-08-09 10:51:24'),
(44,1,'admin','Updated issue #10 to \"resolved\"','issues',10,'{\"id\":10,\"computer_id\":1,\"reported_by\":2,\"issue_category\":\"Other\",\"description\":\"Computer status set to \\\"Not Working\\\".\",\"status\":\"open\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":null,\"created_at\":\"2026-08-09 10:51:24\",\"updated_at\":\"2026-08-09 10:51:24\"}','{\"id\":10,\"computer_id\":1,\"reported_by\":2,\"issue_category\":\"Other\",\"description\":\"Computer status set to \\\"Not Working\\\".\",\"status\":\"resolved\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":\"\",\"created_at\":\"2026-08-09 10:51:24\",\"updated_at\":\"2026-08-09 10:51:24\"}','127.0.0.1','2026-08-09 10:53:47'),
(45,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:51:24\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Working\"}','127.0.0.1','2026-08-09 10:58:00'),
(46,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:58:00\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Not Working\"}','127.0.0.1','2026-08-09 10:58:55'),
(47,2,'ram','Reported issue #11 for computer PC001','issues',11,NULL,'{\"category\":\"Other\",\"description\":\"Computer status set to \\\"Not Working\\\".\"}','127.0.0.1','2026-08-09 10:58:55'),
(48,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 11:01:43'),
(49,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 11:03:38'),
(50,2,'ram','Updated computer PC001 (condition/status)','computers',1,'{\"id\":1,\"computer_id\":\"PC001\",\"asset_number\":null,\"computer_name\":null,\"lab_id\":1,\"department\":null,\"desk_number\":null,\"cpu\":\"\",\"cpu_condition\":\"Working\",\"motherboard\":null,\"ram\":\"\",\"ram_slots\":null,\"storage_type\":null,\"storage_capacity\":\"\",\"graphics_card\":null,\"power_supply\":null,\"monitor_brand\":null,\"monitor_size\":null,\"monitor_serial\":null,\"monitor_condition\":\"Working\",\"keyboard_brand\":null,\"keyboard_condition\":\"Working\",\"mouse_brand\":null,\"mouse_condition\":\"Working\",\"ups\":null,\"ups_battery_status\":null,\"printer_connected\":\"No\",\"scanner_connected\":\"No\",\"speaker\":\"No\",\"webcam\":\"No\",\"lan_status\":\"Working\",\"wifi_status\":\"Not Available\",\"bluetooth_status\":\"Not Available\",\"ip_address\":null,\"mac_address\":null,\"windows_version\":null,\"windows_license\":null,\"office_version\":null,\"office_license\":null,\"antivirus\":null,\"purchase_date\":null,\"warranty_expiry\":null,\"vendor\":null,\"invoice_number\":null,\"last_service_date\":null,\"next_service_date\":null,\"status\":\"Not Working\",\"remarks\":\"idk\",\"created_by\":1,\"updated_by\":2,\"created_at\":\"2026-08-09 09:58:47\",\"updated_at\":\"2026-08-09 10:58:55\"}','{\"monitor_condition\":\"Working\",\"keyboard_condition\":\"Working\",\"mouse_condition\":\"Working\",\"cpu_condition\":\"Working\",\"status\":\"Has Some Issues\"}','127.0.0.1','2026-08-09 11:03:42'),
(51,2,'ram','Reported issue #12 for computer PC001','issues',12,NULL,'{\"category\":\"Other\",\"description\":\"Computer status set to \\\"Has Some Issues\\\".\"}','127.0.0.1','2026-08-09 11:03:42'),
(52,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 11:06:10'),
(53,1,'admin','Logged in','users',1,NULL,NULL,'127.0.0.1','2026-08-09 11:12:49'),
(54,3,'pwtest','Logged in','users',3,NULL,NULL,'127.0.0.1','2026-08-09 11:13:00'),
(55,1,'admin','Updated issue #7 to \"resolved\"','issues',7,'{\"id\":7,\"computer_id\":1,\"reported_by\":3,\"issue_category\":\"Monitor\",\"description\":\"Test issue from automation\",\"status\":\"open\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":null,\"created_at\":\"2026-08-09 10:38:35\",\"updated_at\":\"2026-08-09 10:38:35\"}','{\"id\":7,\"computer_id\":1,\"reported_by\":3,\"issue_category\":\"Monitor\",\"description\":\"Test issue from automation\",\"status\":\"resolved\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":\"\",\"created_at\":\"2026-08-09 10:38:35\",\"updated_at\":\"2026-08-09 10:38:35\"}','127.0.0.1','2026-08-09 11:13:51'),
(56,1,'admin','Updated issue #12 to \"open\"','issues',12,'{\"id\":12,\"computer_id\":1,\"reported_by\":2,\"issue_category\":\"Other\",\"description\":\"Computer status set to \\\"Has Some Issues\\\".\",\"status\":\"open\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":null,\"created_at\":\"2026-08-09 11:03:42\",\"updated_at\":\"2026-08-09 11:03:42\"}','{\"id\":12,\"computer_id\":1,\"reported_by\":2,\"issue_category\":\"Other\",\"description\":\"Computer status set to \\\"Has Some Issues\\\".\",\"status\":\"open\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":\"\",\"created_at\":\"2026-08-09 11:03:42\",\"updated_at\":\"2026-08-09 11:03:42\"}','127.0.0.1','2026-08-09 11:14:01'),
(57,1,'admin','Updated issue #12 to \"resolved\"','issues',12,'{\"id\":12,\"computer_id\":1,\"reported_by\":2,\"issue_category\":\"Other\",\"description\":\"Computer status set to \\\"Has Some Issues\\\".\",\"status\":\"open\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":\"\",\"created_at\":\"2026-08-09 11:03:42\",\"updated_at\":\"2026-08-09 11:14:01\"}','{\"id\":12,\"computer_id\":1,\"reported_by\":2,\"issue_category\":\"Other\",\"description\":\"Computer status set to \\\"Has Some Issues\\\".\",\"status\":\"resolved\",\"fixed_by\":null,\"fixed_at\":null,\"fix_notes\":\"\",\"created_at\":\"2026-08-09 11:03:42\",\"updated_at\":\"2026-08-09 11:14:01\"}','127.0.0.1','2026-08-09 11:14:12');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `computer_photos`
--

DROP TABLE IF EXISTS `computer_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `computer_photos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `computer_id` int(10) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_photos_computer` (`computer_id`),
  CONSTRAINT `fk_photos_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer photos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `computer_photos`
--

LOCK TABLES `computer_photos` WRITE;
/*!40000 ALTER TABLE `computer_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `computer_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `computers`
--

DROP TABLE IF EXISTS `computers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `computers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `computer_id` varchar(50) NOT NULL COMMENT 'Unique human readable computer code (e.g. LAB1-PC001)',
  `asset_number` varchar(50) DEFAULT NULL,
  `computer_name` varchar(100) DEFAULT NULL,
  `lab_id` int(10) unsigned DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `desk_number` varchar(50) DEFAULT NULL,
  `cpu` varchar(150) DEFAULT NULL,
  `cpu_condition` varchar(50) DEFAULT NULL,
  `motherboard` varchar(150) DEFAULT NULL,
  `ram` varchar(50) DEFAULT NULL,
  `ram_slots` varchar(20) DEFAULT NULL,
  `storage_type` varchar(50) DEFAULT NULL,
  `storage_capacity` varchar(50) DEFAULT NULL,
  `graphics_card` varchar(150) DEFAULT NULL,
  `power_supply` varchar(100) DEFAULT NULL,
  `monitor_brand` varchar(100) DEFAULT NULL,
  `monitor_size` varchar(20) DEFAULT NULL,
  `monitor_serial` varchar(100) DEFAULT NULL,
  `monitor_condition` varchar(50) DEFAULT NULL,
  `keyboard_brand` varchar(100) DEFAULT NULL,
  `keyboard_condition` varchar(50) DEFAULT NULL,
  `mouse_brand` varchar(100) DEFAULT NULL,
  `mouse_condition` varchar(50) DEFAULT NULL,
  `ups` varchar(100) DEFAULT NULL,
  `ups_battery_status` varchar(50) DEFAULT NULL,
  `printer_connected` enum('Yes','No') NOT NULL DEFAULT 'No',
  `scanner_connected` enum('Yes','No') NOT NULL DEFAULT 'No',
  `speaker` enum('Yes','No') NOT NULL DEFAULT 'No',
  `webcam` enum('Yes','No') NOT NULL DEFAULT 'No',
  `lan_status` enum('Working','Not Working','Not Available') NOT NULL DEFAULT 'Working',
  `wifi_status` enum('Working','Not Working','Not Available') NOT NULL DEFAULT 'Not Available',
  `bluetooth_status` enum('Working','Not Working','Not Available') NOT NULL DEFAULT 'Not Available',
  `ip_address` varchar(45) DEFAULT NULL,
  `mac_address` varchar(20) DEFAULT NULL,
  `windows_version` varchar(100) DEFAULT NULL,
  `windows_license` varchar(100) DEFAULT NULL,
  `office_version` varchar(100) DEFAULT NULL,
  `office_license` varchar(100) DEFAULT NULL,
  `antivirus` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `vendor` varchar(150) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `last_service_date` date DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `status` enum('Working','Not Working','Has Some Issues') NOT NULL DEFAULT 'Working',
  `remarks` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_computers_computer_id` (`computer_id`),
  KEY `idx_computers_lab_code` (`lab_id`,`computer_id`),
  KEY `idx_computers_asset_number` (`asset_number`),
  KEY `idx_computers_name` (`computer_name`),
  KEY `idx_computers_lab` (`lab_id`),
  KEY `idx_computers_status` (`status`),
  KEY `idx_computers_department` (`department`),
  KEY `idx_computers_cpu` (`cpu`),
  KEY `idx_computers_ip` (`ip_address`),
  KEY `idx_computers_mac` (`mac_address`),
  KEY `idx_computers_purchase_date` (`purchase_date`),
  KEY `idx_computers_created_at` (`created_at`),
  KEY `fk_computers_created_by` (`created_by`),
  KEY `fk_computers_updated_by` (`updated_by`),
  CONSTRAINT `fk_computers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_computers_lab` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_computers_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer inventory';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `computers`
--

LOCK TABLES `computers` WRITE;
/*!40000 ALTER TABLE `computers` DISABLE KEYS */;
INSERT INTO `computers` VALUES
(1,'PC001',NULL,NULL,1,NULL,NULL,'','Working',NULL,'',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'Working',NULL,'Working',NULL,'Working',NULL,NULL,'No','No','No','No','Working','Not Available','Not Available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Has Some Issues','idk',1,2,'2026-08-09 09:58:47','2026-08-09 11:03:42'),
(2,'PC001',NULL,NULL,2,NULL,NULL,'','Working',NULL,'',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'Working',NULL,'Working',NULL,'Working',NULL,NULL,'No','No','No','No','Working','Not Available','Not Available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Not Working','',1,3,'2026-08-09 09:58:59','2026-08-09 10:47:04'),
(3,'PC001',NULL,NULL,3,NULL,NULL,'','Working',NULL,'',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'Working',NULL,'Working',NULL,'Working',NULL,NULL,'No','No','No','No','Working','Not Available','Not Available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Working','',1,1,'2026-08-09 09:59:08','2026-08-09 09:59:08'),
(4,'PC002',NULL,NULL,3,NULL,NULL,'','Working',NULL,'',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'Working',NULL,'Working',NULL,'Working',NULL,NULL,'No','No','No','No','Working','Not Available','Not Available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Working','',1,1,'2026-08-09 09:59:13','2026-08-09 09:59:13'),
(5,'PC002',NULL,NULL,1,NULL,NULL,'','Working',NULL,'',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'Not Working',NULL,'Working',NULL,'Working',NULL,NULL,'No','No','No','No','Working','Not Available','Not Available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Not Working','idk',1,1,'2026-08-09 09:59:18','2026-08-09 09:59:18'),
(6,'PC003',NULL,NULL,1,NULL,NULL,'','Working',NULL,'',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'Not Working',NULL,'Working',NULL,'Working',NULL,NULL,'No','No','No','No','Working','Not Available','Not Available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Not Working','idk',1,1,'2026-08-09 09:59:22','2026-08-09 09:59:22'),
(7,'PC004',NULL,NULL,1,NULL,NULL,'','Working',NULL,'',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'Not Working',NULL,'Working',NULL,'Working',NULL,NULL,'No','No','No','No','Working','Not Available','Not Available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Not Working','idk',1,1,'2026-08-09 09:59:24','2026-08-09 09:59:24');
/*!40000 ALTER TABLE `computers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issues`
--

DROP TABLE IF EXISTS `issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `issues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `computer_id` int(10) unsigned DEFAULT NULL,
  `reported_by` int(10) unsigned DEFAULT NULL,
  `issue_category` varchar(50) NOT NULL DEFAULT 'Other',
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  `fixed_by` int(10) unsigned DEFAULT NULL,
  `fixed_at` datetime DEFAULT NULL,
  `fix_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_issues_computer` (`computer_id`),
  KEY `idx_issues_reporter` (`reported_by`),
  KEY `idx_issues_status` (`status`),
  KEY `fk_issues_fixer` (`fixed_by`),
  CONSTRAINT `fk_issues_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_issues_fixer` FOREIGN KEY (`fixed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_issues_reporter` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer issues reported by users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issues`
--

LOCK TABLES `issues` WRITE;
/*!40000 ALTER TABLE `issues` DISABLE KEYS */;
INSERT INTO `issues` VALUES
(7,1,3,'Monitor','Test issue from automation','resolved',1,'2026-08-09 11:13:51','','2026-08-09 10:38:35','2026-08-09 11:13:51'),
(8,1,3,'Other','Screen flickers when turning on','open',NULL,NULL,NULL,'2026-08-09 10:45:58','2026-08-09 10:45:58'),
(9,2,3,'Other','Computer status set to \"Not Working\".','open',NULL,NULL,NULL,'2026-08-09 10:47:04','2026-08-09 10:47:04'),
(10,1,2,'Other','Computer status set to \"Not Working\".','resolved',1,'2026-08-09 10:53:47','','2026-08-09 10:51:24','2026-08-09 10:53:47'),
(11,1,2,'Other','Computer status set to \"Not Working\".','open',NULL,NULL,NULL,'2026-08-09 10:58:55','2026-08-09 10:58:55'),
(12,1,2,'Other','Computer status set to \"Has Some Issues\".','resolved',1,'2026-08-09 11:14:12','','2026-08-09 11:03:42','2026-08-09 11:14:12');
/*!40000 ALTER TABLE `issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `labs`
--

DROP TABLE IF EXISTS `labs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `labs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_labs_name` (`name`),
  KEY `idx_labs_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Computer labs / rooms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `labs`
--

LOCK TABLES `labs` WRITE;
/*!40000 ALTER TABLE `labs` DISABLE KEYS */;
INSERT INTO `labs` VALUES
(1,'Lab 1','Building A, Ground Floor','General purpose computer lab','active','2026-08-09 09:42:18','2026-08-09 09:42:18'),
(2,'Lab 2','Building A, First Floor','Computer lab for programming classes','active','2026-08-09 09:42:18','2026-08-09 09:42:18'),
(3,'Lab 3','Building B, Ground Floor','Multimedia lab','active','2026-08-09 09:42:18','2026-08-09 09:42:18'),
(4,'Office','Administration Block','Staff office computers','active','2026-08-09 09:42:18','2026-08-09 09:42:18'),
(5,'Library','Central Library','Library browsing terminals','active','2026-08-09 09:42:18','2026-08-09 09:42:18');
/*!40000 ALTER TABLE `labs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Site settings (key/value)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
(1,'site_name','Computer Lab Management System'),
(2,'logo',''),
(3,'theme_color','#0d6efd');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `profile_photo` varchar(255) DEFAULT NULL,
  `theme` enum('light','dark') NOT NULL DEFAULT 'light' COMMENT 'Interface theme (light or dark)',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System user accounts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','System Administrator','admin@example.com','$2y$10$/hqxYV4wOInNsicpYjQTJ.xs7ncDhxmhQmUjnPQByDN1Fbvq.7xhK','super_admin','active',NULL,'light','2026-08-09 11:12:49','2026-08-09 09:42:18','2026-08-09 11:12:49'),
(2,'ram','Ram singh','admin@example.com','$2y$10$E0.yfQEkdMCKf9wesnQIyODMM7mao.nAy/7Mq7cjc9s1aWS/tCmMW','staff','active','u2_20260809094837_eaf44d72.gif','light','2026-08-09 09:47:16','2026-08-09 09:45:53','2026-08-09 09:48:37'),
(3,'pwtest','PW Test','pwtest@test.local','$2y$10$.GYdQpCCgcrzX0Iam6sOA.KWz1/0fRz6BT7dz.W1cIB4yzm3CpdIS','staff','active',NULL,'light','2026-08-09 11:13:00','2026-08-09 10:06:22','2026-08-09 11:13:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'computer_management'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-09 11:33:11
