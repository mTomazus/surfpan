
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
DROP TABLE IF EXISTS `admin_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_audit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) unsigned NOT NULL,
  `action` varchar(50) NOT NULL,
  `target_id` int(11) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_charges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_charges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) DEFAULT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `comp_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `participants_count` int(11) DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `status` enum('pending','paid','failed','refunded','canceled') NOT NULL DEFAULT 'pending',
  `provider` enum('everypay','stripe','manual') NOT NULL DEFAULT 'everypay',
  `payment_ref` varchar(128) DEFAULT NULL,
  `gateway_receipt_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_org` (`organizer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `product_id` (`product_id`),
  KEY `billing_charges_ibfk_4` (`user_id`),
  KEY `fk_bc_comp` (`comp_id`),
  CONSTRAINT `billing_charges_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `billing_charges_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `billing_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `billing_charges_ibfk_3` FOREIGN KEY (`comp_id`) REFERENCES `comp_name` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `billing_charges_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `comp_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bc_comp` FOREIGN KEY (`comp_id`) REFERENCES `comp_name` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=223 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_pass_uses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_pass_uses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL,
  `charge_id` int(11) NOT NULL,
  `competition_id` int(11) DEFAULT NULL,
  `used_by_account` int(11) DEFAULT NULL,
  `used_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_charge` (`charge_id`),
  KEY `idx_org` (`organization_id`),
  KEY `idx_comp` (`competition_id`),
  CONSTRAINT `billing_pass_uses_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `billing_pass_uses_ibfk_2` FOREIGN KEY (`charge_id`) REFERENCES `billing_charges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bpu_charge` FOREIGN KEY (`charge_id`) REFERENCES `billing_charges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bpu_comp` FOREIGN KEY (`competition_id`) REFERENCES `comp_name` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bpu_org` FOREIGN KEY (`organization_id`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_months` tinyint(4) DEFAULT 1,
  `price` decimal(8,2) DEFAULT NULL,
  `currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `features_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_json`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `billing_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL,
  `product_code` varchar(64) NOT NULL,
  `status` enum('trialing','active','past_due','canceled','paused') NOT NULL,
  `period_months` tinyint(3) unsigned NOT NULL,
  `price_cents` int(11) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `current_period_start` datetime NOT NULL,
  `current_period_end` datetime NOT NULL,
  `trial_end` datetime DEFAULT NULL,
  `cancel_at_period_end` tinyint(1) NOT NULL DEFAULT 0,
  `canceled_at` datetime DEFAULT NULL,
  `payment_method_token` varchar(128) DEFAULT NULL,
  `billing_email` varchar(255) DEFAULT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_active_org` (`organization_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_competition_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_competition_divisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `custom_name` varchar(100) DEFAULT NULL,
  `elimination_format` enum('single','double','robin','second_chance') DEFAULT NULL,
  `elimination_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`elimination_config`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ccd` (`competition_id`,`division_id`),
  KEY `competition_id` (`competition_id`),
  KEY `division_id` (`division_id`),
  CONSTRAINT `comp_competition_divisions_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `comp_name` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comp_competition_divisions_ibfk_2` FOREIGN KEY (`division_id`) REFERENCES `comp_divisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ccd_competition` FOREIGN KEY (`competition_id`) REFERENCES `comp_name` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ccd_division` FOREIGN KEY (`division_id`) REFERENCES `comp_divisions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_divisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `gender` enum('male','female','mixed') DEFAULT NULL,
  `age` enum('u10','u11','u12','u13','u14','u15','u16','u17','u18','u19','u20','u21','open','veteran') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_elimination_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_elimination_profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(120) NOT NULL,
  `applies_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`applies_json`)),
  `structure_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`structure_json`)),
  `rules_version` varchar(16) NOT NULL DEFAULT '2025.1',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_final_standings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_final_standings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp_id` int(11) NOT NULL,
  `division` varchar(100) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `final_score` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_fs_comp` (`comp_id`),
  KEY `fk_fs_participant` (`participant_id`),
  CONSTRAINT `fk_fs_comp` FOREIGN KEY (`comp_id`) REFERENCES `comp_name` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fs_participant` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_heat_advancement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_heat_advancement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_heat_id` int(11) NOT NULL,
  `finish_position` int(11) NOT NULL,
  `to_heat_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_adv_from_heat` (`from_heat_id`),
  KEY `fk_adv_to_heat` (`to_heat_id`),
  CONSTRAINT `comp_heat_advancement_ibfk_1` FOREIGN KEY (`from_heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comp_heat_advancement_ibfk_2` FOREIGN KEY (`to_heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_adv_from_heat` FOREIGN KEY (`from_heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_adv_to_heat` FOREIGN KEY (`to_heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_heat_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_heat_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `heat_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `jersey_color` enum('white','red','green','blue') NOT NULL,
  `seeded_from` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hp_slot` (`heat_id`,`participant_id`),
  UNIQUE KEY `uq_hp_jersey` (`heat_id`,`jersey_color`),
  KEY `heat_id` (`heat_id`),
  KEY `participant_id` (`participant_id`),
  CONSTRAINT `comp_heat_participants_ibfk_1` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comp_heat_participants_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hp_heat` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hp_participant` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1427 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_heat_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_heat_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `heat_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `total_score` decimal(5,2) NOT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hr` (`heat_id`,`participant_id`),
  KEY `heat_id` (`heat_id`),
  KEY `participant_id` (`participant_id`),
  CONSTRAINT `comp_heat_results_ibfk_1` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comp_heat_results_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`),
  CONSTRAINT `fk_hr_heat` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hr_participant` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=383 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_heat_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_heat_structure` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(11) NOT NULL,
  `elimination_profile_code` varchar(64) NOT NULL,
  `seed_algorithm` enum('rating','snake','random') NOT NULL,
  `seed_value` int(11) DEFAULT NULL,
  `rules_version` varchar(16) NOT NULL,
  `structure_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`structure_json`)),
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`),
  CONSTRAINT `comp_heat_structure_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `comp_divisions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_heats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_heats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp_id` int(11) NOT NULL,
  `round` enum('Final','Round 1','Round 2','Repechage','Repechage 1','Repechage 2','Semi Final','Quarter Final') NOT NULL,
  `heat_number` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `division` enum('Male U12','Male U15','Male U18','Female U12','Female U15','Female U18','Male Open','Female Open','Male Veteran','Female Veteran','Male U13','Male U14','Male U16','Male U17','Female U13','Female U14','Female U16','Female U17','Male U21','Female U21','Male U10','Female U10','Male U11','Female U11','Mixed Adult') NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_min` smallint(5) unsigned NOT NULL DEFAULT 20,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','scheduled','running','finished') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `comp_id` (`comp_id`),
  KEY `idx_comp_status_time` (`comp_id`,`status`,`start_time`),
  KEY `idx_status_time` (`status`,`start_time`),
  KEY `idx_comp_sort` (`comp_id`,`sort_order`),
  CONSTRAINT `comp_heats_ibfk_1` FOREIGN KEY (`comp_id`) REFERENCES `comp_name` (`id`),
  CONSTRAINT `fk_heats_comp` FOREIGN KEY (`comp_id`) REFERENCES `comp_name` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1003 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_judge_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_judge_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `heat_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `wave_number` int(11) NOT NULL,
  `score` decimal(4,2) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_score` (`heat_id`,`wave_number`,`participant_id`,`judge_id`),
  KEY `heat_id` (`heat_id`),
  KEY `judge_id` (`judge_id`),
  KEY `participant_id` (`participant_id`),
  CONSTRAINT `comp_judge_scores_ibfk_1` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comp_judge_scores_ibfk_2` FOREIGN KEY (`judge_id`) REFERENCES `comp_judges` (`id`),
  CONSTRAINT `comp_judge_scores_ibfk_3` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`),
  CONSTRAINT `fk_scores_heat` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_scores_judge` FOREIGN KEY (`judge_id`) REFERENCES `comp_judges` (`id`),
  CONSTRAINT `fk_scores_participant` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1474 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_judges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_judges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(155) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(19) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('judge','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'judge',
  `organizer_id` int(11) NOT NULL DEFAULT 1,
  `trongate_user_id` int(11) NOT NULL,
  `last_login` int(11) NOT NULL DEFAULT 0,
  `num_logins` tinyint(1) NOT NULL DEFAULT 0,
  `lockout_time` int(11) NOT NULL DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `trongate_user_id` (`trongate_user_id`),
  KEY `comp_judges_ibfk_2` (`organizer_id`),
  CONSTRAINT `comp_judges_ibfk_1` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`),
  CONSTRAINT `comp_judges_ibfk_2` FOREIGN KEY (`organizer_id`) REFERENCES `comp_organizations` (`id`),
  CONSTRAINT `fk_comp_judges_trongate` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `location` varchar(150) NOT NULL,
  `year` year(4) NOT NULL,
  `poster_url` varchar(100) NOT NULL,
  `status` enum('created','scheduled','open','closed','generated','running','finished') NOT NULL DEFAULT 'created',
  `billing_status` enum('not_required','pending','paid') NOT NULL DEFAULT 'not_required',
  `elimination_format` enum('single','double') DEFAULT 'double',
  `entry_type` enum('free entry','entry fee') DEFAULT 'free entry',
  `entry_fee` decimal(10,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `billing_tier` enum('free_12','paid_24','paid_50','paid_100','event_pass') DEFAULT NULL,
  `billing_participants_locked` int(11) DEFAULT NULL,
  `billing_charge_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comp_name_ibfk_1` (`organizer_id`),
  KEY `billing_charge_id` (`billing_charge_id`),
  CONSTRAINT `comp_name_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `comp_organizations` (`id`),
  CONSTRAINT `comp_name_ibfk_2` FOREIGN KEY (`billing_charge_id`) REFERENCES `billing_charges` (`id`),
  CONSTRAINT `fk_comp_name_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_org_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_org_accounts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(155) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','admin') NOT NULL DEFAULT 'owner',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `num_logins` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lockout_time` tinyint(11) NOT NULL,
  `trongate_user_id` int(11) NOT NULL,
  `last_login` tinyint(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `organization_id` (`organization_id`),
  KEY `comp_org_accounts_ibfk_2` (`trongate_user_id`),
  CONSTRAINT `comp_org_accounts_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comp_org_accounts_ibfk_2` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_org_judges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_org_judges` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `role` enum('judge','head_judge','video_judge') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'judge',
  `status` enum('active','pending_invite','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `invited_by` int(11) DEFAULT NULL COMMENT 'organizer_id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_org` (`user_id`,`organization_id`),
  KEY `idx_org_status` (`organization_id`,`status`),
  KEY `idx_user` (`user_id`),
  KEY `idx_invited_by` (`invited_by`),
  CONSTRAINT `comp_org_judges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `comp_judges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comp_org_judges_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comp_org_judges_ibfk_3` FOREIGN KEY (`invited_by`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_oj_judge` FOREIGN KEY (`user_id`) REFERENCES `comp_judges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_oj_organization` FOREIGN KEY (`organization_id`) REFERENCES `comp_organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `organization` varchar(255) NOT NULL,
  `role` enum('organizer','other') NOT NULL DEFAULT 'organizer',
  `address` varchar(200) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `company_code` int(15) DEFAULT NULL,
  `status` enum('active','inactive','suspended','private') NOT NULL DEFAULT 'active',
  `phone` varchar(25) NOT NULL,
  `email` varchar(150) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `logo` varchar(255) DEFAULT NULL,
  `timezone` varchar(64) NOT NULL DEFAULT 'Europe/Vilnius',
  `username` varchar(75) NOT NULL,
  `password` varchar(255) NOT NULL,
  `num_logins` tinyint(1) NOT NULL DEFAULT 0,
  `lockout_time` int(11) NOT NULL DEFAULT 0,
  `last_login` int(11) NOT NULL DEFAULT 0,
  `trongate_user_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `slug` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_comp_org_slug` (`slug`),
  KEY `trongate_user_id` (`trongate_user_id`),
  CONSTRAINT `comp_organizations_ibfk_1` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`),
  CONSTRAINT `fk_comp_orgs_trongate` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `comp_id` int(11) NOT NULL,
  `division_id` int(11) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `status` enum('confirmed','paid','pending') NOT NULL DEFAULT 'pending',
  `billing_charge_id` int(11) DEFAULT NULL,
  `waiver_accepted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comp_id` (`comp_id`),
  KEY `billing_charge_id` (`billing_charge_id`),
  KEY `fk_participants_user` (`user_id`),
  KEY `fk_participants_division` (`division_id`),
  CONSTRAINT `comp_participants_ibfk_1` FOREIGN KEY (`comp_id`) REFERENCES `comp_name` (`id`),
  CONSTRAINT `comp_participants_ibfk_2` FOREIGN KEY (`division_id`) REFERENCES `comp_divisions` (`id`),
  CONSTRAINT `comp_participants_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `comp_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comp_participants_ibfk_4` FOREIGN KEY (`billing_charge_id`) REFERENCES `billing_charges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_participants_comp` FOREIGN KEY (`comp_id`) REFERENCES `comp_name` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_participants_division` FOREIGN KEY (`division_id`) REFERENCES `comp_divisions` (`id`),
  CONSTRAINT `fk_participants_user` FOREIGN KEY (`user_id`) REFERENCES `comp_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_roles` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` enum('participant','judge','organizer','admin') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_schedule` (
  `id` int(11) NOT NULL,
  `heat_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `num_logins` int(1) NOT NULL DEFAULT 0,
  `lockout_time` int(11) NOT NULL DEFAULT 0,
  `last_login` int(11) NOT NULL,
  `date_joined` datetime NOT NULL,
  `trongate_user_id` int(11) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `comp_users_ibfk_1` (`trongate_user_id`),
  CONSTRAINT `comp_users_ibfk_1` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`),
  CONSTRAINT `fk_comp_users_trongate` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_users_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_users_profiles` (
  `user_id` int(11) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `country` varchar(150) NOT NULL,
  `club_name` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `comp_users_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `comp_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_users_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `comp_users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `comp_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comp_users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `comp_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comp_wave_averages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comp_wave_averages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `heat_id` int(11) NOT NULL,
  `wave_number` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `avg_score` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wavg` (`heat_id`,`wave_number`,`participant_id`),
  KEY `heat_id` (`heat_id`),
  KEY `comp_wave_averages_ibfk_2` (`participant_id`),
  CONSTRAINT `comp_wave_averages_ibfk_1` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comp_wave_averages_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`),
  CONSTRAINT `fk_wavg_heat` FOREIGN KEY (`heat_id`) REFERENCES `comp_heats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wavg_participant` FOREIGN KEY (`participant_id`) REFERENCES `comp_participants` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=926 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `competition_judges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `competition_judges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `role` enum('judge','head_judge','video_judge') DEFAULT 'judge',
  `status` enum('active','pending invite','removed','accepted','declined') DEFAULT 'pending invite',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_comp_judge` (`competition_id`,`judge_id`),
  KEY `fk_cj_judge` (`judge_id`),
  CONSTRAINT `competition_judges_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `comp_name` (`id`),
  CONSTRAINT `competition_judges_ibfk_2` FOREIGN KEY (`judge_id`) REFERENCES `comp_judges` (`id`),
  CONSTRAINT `fk_cj_competition` FOREIGN KEY (`competition_id`) REFERENCES `comp_name` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cj_judge` FOREIGN KEY (`judge_id`) REFERENCES `comp_judges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trongate_administrators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trongate_administrators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(65) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `trongate_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_admins_trongate_user` (`trongate_user_id`),
  CONSTRAINT `fk_admins_trongate_user` FOREIGN KEY (`trongate_user_id`) REFERENCES `trongate_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trongate_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trongate_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` text DEFAULT NULL,
  `date_created` int(11) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL,
  `target_table` varchar(125) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `code` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trongate_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trongate_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url_string` varchar(255) DEFAULT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `page_body` text DEFAULT NULL,
  `date_created` int(11) DEFAULT NULL,
  `last_updated` int(11) DEFAULT NULL,
  `published` tinyint(1) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trongate_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trongate_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(125) DEFAULT NULL,
  `user_id` int(11) DEFAULT 0,
  `expiry_date` int(11) DEFAULT NULL,
  `code` varchar(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_tokens_user` (`user_id`),
  CONSTRAINT `fk_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `trongate_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1432 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trongate_user_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trongate_user_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_title` varchar(125) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trongate_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trongate_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) DEFAULT NULL,
  `user_level_id` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

