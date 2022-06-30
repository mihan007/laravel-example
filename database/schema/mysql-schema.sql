/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `u_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inn` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `index` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accountant` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seal_img` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_sign` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accountant_sign` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `bank` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bik` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `k_account` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `r_account` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amocrm_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_amocrm_config_id` int(10) unsigned NOT NULL,
  `lead_id` int(10) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(10) unsigned NOT NULL,
  `old_status_id` int(10) unsigned DEFAULT NULL,
  `price` int(11) DEFAULT '0',
  `responsible_user_id` int(10) unsigned NOT NULL,
  `last_modified` datetime NOT NULL,
  `modified_user_id` int(10) unsigned NOT NULL,
  `created_user_id` int(10) unsigned NOT NULL,
  `date_create` datetime NOT NULL,
  `pipeline_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `target_set_at` datetime DEFAULT NULL COMMENT 'When lead set in target status',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amocrm_pipelines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_amocrm_config_id` int(10) unsigned NOT NULL,
  `pipeline_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `amocrm_pipelines_company_amocrm_config_id_foreign` (`company_amocrm_config_id`),
  CONSTRAINT `amocrm_pipelines_company_amocrm_config_id_foreign` FOREIGN KEY (`company_amocrm_config_id`) REFERENCES `company_amocrm_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amocrm_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_amocrm_config_id` int(10) unsigned NOT NULL,
  `status_id` int(10) unsigned NOT NULL COMMENT 'Amocrm status id',
  `type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status type',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `amocrm_statuses_company_amocrm_config_id_foreign` (`company_amocrm_config_id`),
  CONSTRAINT `amocrm_statuses_company_amocrm_config_id_foreign` FOREIGN KEY (`company_amocrm_config_id`) REFERENCES `company_amocrm_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approved_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roistat_company_config_id` int(10) unsigned NOT NULL,
  `for_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channel_reasons_of_rejections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) unsigned NOT NULL,
  `reasons_of_rejection_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_reasons_of_rejections_channel_id_foreign` (`channel_id`),
  KEY `channel_reasons_of_rejections_reasons_of_rejection_id_foreign` (`reasons_of_rejection_id`),
  CONSTRAINT `channel_reasons_of_rejections_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `channel_reasons_of_rejections_reasons_of_rejection_id_foreign` FOREIGN KEY (`reasons_of_rejection_id`) REFERENCES `reasons_of_rejections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `channels_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) unsigned DEFAULT NULL,
  `public_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Uuid for public access to company',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_for_graph` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Check company for display some information in graphs',
  `lead_cost` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Set current price for lead price',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prepayment` tinyint(4) NOT NULL DEFAULT '0',
  `amount_limit` bigint(20) NOT NULL DEFAULT '0',
  `date_stop_leads` datetime DEFAULT NULL,
  `free_period` tinyint(4) NOT NULL DEFAULT '0',
  `balance_limit` bigint(20) NOT NULL DEFAULT '800',
  `application_moderation_period` int(11) DEFAULT NULL,
  `manage_subscription_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approve_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Approve description',
  `balance_stop` tinyint(4) DEFAULT NULL,
  `balance_send_notification` tinyint(4) DEFAULT '0',
  `account_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies_channel_id_foreign` (`channel_id`),
  CONSTRAINT `companies_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_amocrm_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `subdomain` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Amocrm subdomain',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_amocrm_configs_company_id_foreign` (`company_id`),
  CONSTRAINT `company_amocrm_configs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_replacement_database_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_replacement_database_configs_company_id_foreign` (`company_id`),
  CONSTRAINT `company_replacement_database_configs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_date` date DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `balance` int(11) NOT NULL,
  `target_leads` int(11) NOT NULL,
  `target_profit` int(11) NOT NULL,
  `target_percent` double NOT NULL,
  `cpl` double NOT NULL,
  `costs` int(11) NOT NULL,
  `yandex_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roistat_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `target_all` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company_report_start_at_end_at_index` (`start_at`,`end_at`),
  KEY `company_report_channel_id_index` (`channel_id`),
  KEY `company_report_company_id_index` (`company_id`),
  KEY `company_report_report_date_index` (`report_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_role_users` (
  `company_id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `company_role_users_company_id_foreign` (`company_id`),
  CONSTRAINT `company_role_users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_company_admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_manage_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approve_all_pending_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_settings_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `disable_all_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_manage_links_email_unique` (`email`),
  UNIQUE KEY `email_manage_links_approve_all_pending_key_unique` (`approve_all_pending_key`),
  UNIQUE KEY `email_manage_links_notification_settings_key_unique` (`notification_settings_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_notification_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('disabled','pending_approve','approved') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_changed_at` datetime NOT NULL,
  `disable_link_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_notification_settings_disable_link_key_unique` (`disable_link_key`),
  UNIQUE KEY `email_notification_settings_email_notification_type_unique` (`company_id`,`email`,`notification_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT 'Attach company',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Store where need send message',
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'What kind of message',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_notifications_company_id_foreign` (`company_id`),
  CONSTRAINT `email_notifications_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `finance_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Report status',
  `lead_count` int(11) NOT NULL DEFAULT '0' COMMENT 'Amount of target leads',
  `paid` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Amount that company paid us',
  `lead_cost` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'One lead costs',
  `to_pay` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Amount that company have to pay to us',
  `for_date` date NOT NULL COMMENT 'Report period',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `instructions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Новая инструкция',
  `path_to_view` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` int(11) NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lidogenerator_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lidogenerator_subscriptions_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` int(10) unsigned NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_id_notifiable_type_index` (`notifiable_id`,`notifiable_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `payment_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_inn` bigint(20) DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `information` text COLLATE utf8mb4_unicode_ci,
  `account_number` bigint(20) DEFAULT NULL,
  `proxy_leads_id` int(11) NOT NULL DEFAULT '0',
  `source_of_changes` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_transactions_company_id_foreign` (`company_id`),
  CONSTRAINT `payment_transactions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `finance_report_id` int(10) unsigned NOT NULL,
  `amount` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Amount of payment',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pl_approved_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_lead_setting_id` int(10) unsigned NOT NULL,
  `for_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pl_email_recipients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_lead_setting_id` int(10) unsigned NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Type of email',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pl_report_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_lead_id` int(10) unsigned NOT NULL,
  `company_confirmed` tinyint(4) NOT NULL DEFAULT '1',
  `reasons_of_rejection_id` int(10) unsigned DEFAULT NULL COMMENT 'Rejection reason',
  `company_comment` text COLLATE utf8mb4_unicode_ci,
  `admin_confirmed` tinyint(4) NOT NULL DEFAULT '1',
  `admin_comment` text COLLATE utf8mb4_unicode_ci,
  `total_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `not_before_called_counter` int(11) DEFAULT NULL,
  `photo_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moderation_status` tinyint(4) DEFAULT NULL,
  `is_send` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pl_report_leads_reasons_of_rejection_id_foreign` (`reasons_of_rejection_id`),
  CONSTRAINT `pl_report_leads_reasons_of_rejection_id_foreign` FOREIGN KEY (`reasons_of_rejection_id`) REFERENCES `reasons_of_rejections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pl_yc_companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `yclients_setting_id` int(10) unsigned NOT NULL,
  `yclients_company_id` int(10) unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `primary` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If current company is primary for management in proxy leads',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pl_yc_company_staffs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pl_yc_company_id` int(10) unsigned NOT NULL,
  `staff_id` int(10) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Employer name',
  `primary` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is user primary for management in proxy lead module',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pl_yc_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `yclients_setting_id` int(10) unsigned NOT NULL,
  `proxy_lead_id` int(10) unsigned NOT NULL,
  `record_id` int(10) unsigned NOT NULL,
  `visit_attendance` tinyint(4) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proxy_lead_goal_counters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `target` int(11) NOT NULL DEFAULT '0',
  `not_target` int(11) NOT NULL DEFAULT '0',
  `not_confirmed` int(11) NOT NULL DEFAULT '0',
  `user_not_confirmed` int(11) NOT NULL DEFAULT '0',
  `admin_not_confirmed` int(11) NOT NULL DEFAULT '0',
  `for_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `lead_cost` decimal(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proxy_lead_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `public_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bitrix_webhook` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bitrix integration webhook',
  `match_phone` json DEFAULT NULL,
  `match_name` json DEFAULT NULL,
  `match_info` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proxy_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_lead_setting_id` int(10) unsigned NOT NULL,
  `cost` int(11) DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Новая заявка',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `ym_counter` int(11) DEFAULT NULL,
  `advertising_platform` varchar(3000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tag` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'This tag helps understand who is this lead attach in advertising system',
  `service_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'id service',
  `extra` json DEFAULT NULL COMMENT 'Extra data as json',
  `is_free` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `proxy_leads_service_id_index` (`service_id`),
  KEY `ad_service_id` (`proxy_lead_setting_id`,`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rc_avito_analytics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roistat_company_config_id` int(10) unsigned NOT NULL,
  `visit_count` int(11) NOT NULL DEFAULT '0',
  `visits_to_leads` double NOT NULL DEFAULT '0',
  `lead_count` int(11) NOT NULL DEFAULT '0',
  `visits_cost` double NOT NULL DEFAULT '0',
  `cost_per_click` double NOT NULL DEFAULT '0',
  `cost_per_lead` double NOT NULL DEFAULT '0',
  `for_date` date DEFAULT NULL COMMENT 'Attach date',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roi_comp_conf_id2` (`roistat_company_config_id`),
  CONSTRAINT `roi_comp_conf_id2` FOREIGN KEY (`roistat_company_config_id`) REFERENCES `roistat_company_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rc_balance_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL COMMENT 'Panel company id',
  `project_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Roistat project id',
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Roistat api key',
  `limit_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Minimum amount',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rc_balance_configs_company_id_foreign` (`company_id`),
  CONSTRAINT `rc_balance_configs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rc_balance_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rc_balance_config_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of operation',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Type of operation',
  `system_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'System name of operation',
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Roistat project id',
  `sum` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Operation amount',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `virtual_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reasons_of_rejections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reconclications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_lead_setting_id` int(10) unsigned NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of the reconclication sender',
  `period` date NOT NULL COMMENT 'Reconclication period',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reconclications_proxy_lead_setting_id_foreign` (`proxy_lead_setting_id`),
  CONSTRAINT `reconclications_proxy_lead_setting_id_foreign` FOREIGN KEY (`proxy_lead_setting_id`) REFERENCES `proxy_lead_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request_loggers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `get` json NOT NULL,
  `post` json NOT NULL,
  `raw_post` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_analytics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roistat_company_config_id` int(10) unsigned NOT NULL,
  `visitCount` int(11) NOT NULL DEFAULT '0',
  `visits2leads` double NOT NULL DEFAULT '0',
  `leadCount` int(11) NOT NULL DEFAULT '0',
  `visitsCost` double NOT NULL DEFAULT '0',
  `costPerClick` double NOT NULL DEFAULT '0',
  `costPerLead` double NOT NULL DEFAULT '0',
  `for_date` date DEFAULT NULL COMMENT 'Attach date',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roi_comp_conf_id1` (`roistat_company_config_id`),
  CONSTRAINT `roi_comp_conf_id1` FOREIGN KEY (`roistat_company_config_id`) REFERENCES `roistat_company_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_analytics_dimension_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roistat_company_config_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_active` smallint(6) NOT NULL DEFAULT '1' COMMENT 'Set active status for mian analytic dimension',
  `is_google_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Status for activate roistat google analytics',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roi_comp_conf_id` (`roistat_company_config_id`),
  CONSTRAINT `roi_comp_conf_id` FOREIGN KEY (`roistat_company_config_id`) REFERENCES `roistat_company_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_company_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `roistat_project_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `timezone` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '+0300',
  `google_limit_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Minimum google amount',
  `max_lead_price` decimal(10,2) DEFAULT NULL COMMENT 'Maximum lead price',
  `max_costs` decimal(10,2) DEFAULT NULL COMMENT 'Maximum costs for yesterday',
  `avito_visits_limit` int(11) DEFAULT NULL COMMENT 'Avito minimum visits limit',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roistat_company_configs_company_id_foreign` (`company_id`),
  CONSTRAINT `roistat_company_configs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_google_analytics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roistat_company_config_id` int(10) unsigned NOT NULL,
  `visitCount` int(11) NOT NULL DEFAULT '0',
  `visits2leads` double NOT NULL DEFAULT '0',
  `leadCount` int(11) NOT NULL DEFAULT '0',
  `visitsCost` double NOT NULL DEFAULT '0',
  `costPerClick` double NOT NULL DEFAULT '0',
  `costPerLead` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roi_comp_conf_id3` (`roistat_company_config_id`),
  CONSTRAINT `roi_comp_conf_id3` FOREIGN KEY (`roistat_company_config_id`) REFERENCES `roistat_company_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_proxy_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `roistat_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text` text COLLATE utf8mb4_unicode_ci,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roistat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creation_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roistat_proxy_leads_company_id_foreign` (`company_id`),
  CONSTRAINT `roistat_proxy_leads_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_proxy_leads_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roistat_company_config_id` int(10) unsigned NOT NULL,
  `roistat_proxy_lead_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text` text COLLATE utf8mb4_unicode_ci,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roistat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_date` date NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `admin_confirmed` smallint(6) NOT NULL DEFAULT '0',
  `admin_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Admin comment for lead report',
  `user_confirmed` tinyint(4) NOT NULL DEFAULT '1',
  `user_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'User comment for lead report',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roi_comp_conf_id4` (`roistat_company_config_id`),
  KEY `roi_prox_lead_id` (`roistat_proxy_lead_id`),
  CONSTRAINT `roi_comp_conf_id4` FOREIGN KEY (`roistat_company_config_id`) REFERENCES `roistat_company_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `roi_comp_conf_id5` FOREIGN KEY (`roistat_company_config_id`) REFERENCES `roistat_company_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `roi_prox_lead_id` FOREIGN KEY (`roistat_proxy_lead_id`) REFERENCES `roistat_proxy_leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_reconciliations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `roistat_company_config_id` int(10) unsigned NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of reconciliation',
  `period` date NOT NULL COMMENT 'For what period it was created',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roistat_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `visitCount` int(11) NOT NULL DEFAULT '0',
  `leadCount` int(11) NOT NULL DEFAULT '0',
  `saleCount` int(11) NOT NULL DEFAULT '0',
  `revenue` int(11) NOT NULL DEFAULT '0',
  `profit` int(11) NOT NULL DEFAULT '0',
  `marketingCosts` int(11) NOT NULL DEFAULT '0',
  `salesCosts` int(11) NOT NULL DEFAULT '0',
  `cv1` double NOT NULL DEFAULT '0',
  `cv2` double NOT NULL DEFAULT '0',
  `cpc` double NOT NULL DEFAULT '0',
  `cpl` double NOT NULL DEFAULT '0',
  `cpo` double NOT NULL DEFAULT '0',
  `averageRevenue` int(11) NOT NULL DEFAULT '0',
  `roi` double NOT NULL DEFAULT '0',
  `for_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roistat_statistics_company_id_foreign` (`company_id`),
  CONSTRAINT `roistat_statistics_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_user` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_task_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'task name',
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  UNIQUE KEY `sessions_id_unique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Site ulr',
  `mobile_score` int(11) NOT NULL DEFAULT '0' COMMENT 'Pagespeed mobile score',
  `mobile_usability` int(11) NOT NULL DEFAULT '0' COMMENT 'Pagespeed mobile usability',
  `desktop_score` int(11) NOT NULL DEFAULT '0' COMMENT 'Pagespeed desktop score',
  `last_pagespeed_sync` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Last date of pagespeed synchronization',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sites_company_id_foreign` (`company_id`),
  CONSTRAINT `sites_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smsru_balances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smsru_company_config_id` int(10) unsigned NOT NULL,
  `balance` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smsru_conf_id` (`smsru_company_config_id`),
  CONSTRAINT `smsru_conf_id` FOREIGN KEY (`smsru_company_config_id`) REFERENCES `smsru_company_configs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smsru_company_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `api_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smsru_company_configs_company_id_foreign` (`company_id`),
  CONSTRAINT `smsru_company_configs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tinkoff_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request` json NOT NULL,
  `response` json DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tinkoff_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `account` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `inn` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `total_company_costs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Amounts of costs per month',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `total_company_costs_company_id_foreign` (`company_id`),
  CONSTRAINT `total_company_costs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `total_day_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT 'amount of leads in special day',
  `for_date` date DEFAULT NULL COMMENT 'Attach date',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_activations` (
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `user_activations_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yandex_direct_balances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `yandex_direct_balances_company_id_foreign` (`company_id`),
  CONSTRAINT `yandex_direct_balances_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yandex_direct_company_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `yandex_auth_key` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `yandex_login` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `token_life_time` int(11) NOT NULL DEFAULT '0',
  `token_added_on` datetime DEFAULT NULL,
  `limit_amount` decimal(10,2) DEFAULT '0.00' COMMENT 'Minimum amount',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `yandex_direct_company_configs_company_id_foreign` (`company_id`),
  CONSTRAINT `yandex_direct_company_configs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yandex_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `wallet_number` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret_key` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `webhook_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_yandex_wallet` tinyint(1) DEFAULT NULL,
  `is_bank_card` tinyint(1) DEFAULT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yclients_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `proxy_lead_setting_id` int(10) unsigned NOT NULL,
  `login` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Yclients user login',
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Yclients user password',
  `partner_api_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Yclients partner api key',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is setting active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zadarma_company_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `secret` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zadarma_company_configs_company_id_foreign` (`company_id`),
  CONSTRAINT `zadarma_company_configs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` VALUES (3,'2017_04_21_073315_create_user_activations_table',1);
INSERT INTO `migrations` VALUES (4,'2017_04_24_060816_create_companies_table',1);
INSERT INTO `migrations` VALUES (5,'2017_04_25_034517_create_yandex_direct_company_configs_table',1);
INSERT INTO `migrations` VALUES (6,'2017_04_25_035440_AddForeignKeyForYandexDirectCompanyConfig',1);
INSERT INTO `migrations` VALUES (7,'2017_04_26_061107_create_yandex_direct_balances_table',2);
INSERT INTO `migrations` VALUES (8,'2017_04_28_092350_create_roistat_company_configs_table',3);
INSERT INTO `migrations` VALUES (9,'2017_05_02_062143_create_roistat_statistics_table',3);
INSERT INTO `migrations` VALUES (10,'2017_05_02_072026_create_roistat_proxy_leads_table',3);
INSERT INTO `migrations` VALUES (11,'2017_05_05_093500_AddTimezone',4);
INSERT INTO `migrations` VALUES (12,'2017_05_05_113738_create_roistat_analytics_dimension_values_table',4);
INSERT INTO `migrations` VALUES (13,'2017_05_08_165919_create_roistat_analytics_table',4);
INSERT INTO `migrations` VALUES (14,'2017_05_10_094357_create_smsru_company_configs_table',5);
INSERT INTO `migrations` VALUES (15,'2017_05_10_105651_create_smsru_balances_table',5);
INSERT INTO `migrations` VALUES (16,'2017_05_12_033955_create_email_notifications_table',6);
INSERT INTO `migrations` VALUES (17,'2017_05_15_032415_AddMinimumLimitAmountForYandexConfig',6);
INSERT INTO `migrations` VALUES (18,'2017_05_16_041657_AddGoogleActiveStatusForRoistatDimensionValues',7);
INSERT INTO `migrations` VALUES (19,'2017_05_16_050401_create_roistat_google_analytics_table',7);
INSERT INTO `migrations` VALUES (20,'2017_05_16_071116_AddRoistatGoogleLimitInRoistatConfig',8);
INSERT INTO `migrations` VALUES (21,'2017_05_17_052745_create_roistat_rroxy_leads_reports_table',9);
INSERT INTO `migrations` VALUES (22,'2017_06_01_064243_create_company_replacement_database_configs_table',10);
INSERT INTO `migrations` VALUES (23,'2017_06_23_062649_create_instructions_table',11);
INSERT INTO `migrations` VALUES (24,'2017_07_10_103050_create_lidogenerator_subscriptions_table',12);
INSERT INTO `migrations` VALUES (25,'2017_08_02_095855_change_default_values_in_roistat_analytics',13);
INSERT INTO `migrations` VALUES (26,'2017_09_04_094831_FixMistakesInRoistatGoogleAnalytics',14);
INSERT INTO `migrations` VALUES (27,'2017_09_18_063318_create_roistat_balance_config',15);
INSERT INTO `migrations` VALUES (28,'2017_09_18_101439_create_rc_balance_transactions_table',15);
INSERT INTO `migrations` VALUES (29,'2017_09_25_065846_create_total_day_leads_table',16);
INSERT INTO `migrations` VALUES (30,'2017_09_25_070208_add_check_company_for_graph',16);
INSERT INTO `migrations` VALUES (31,'2017_09_28_091604_create_total_company_costs_table',17);
INSERT INTO `migrations` VALUES (32,'2017_09_28_150147_set_avtive_status_for_main_analytic',18);
INSERT INTO `migrations` VALUES (33,'2017_09_29_122945_add_new_notificate_condition',19);
INSERT INTO `migrations` VALUES (34,'2017_10_03_212244_add_date_for_total_day_leads',20);
INSERT INTO `migrations` VALUES (35,'2017_10_10_171309_add_date_for_roistat_analytics',21);
INSERT INTO `migrations` VALUES (36,'2017_10_11_100239_create_rc_avito_analytics_table',22);
INSERT INTO `migrations` VALUES (37,'2017_10_11_112037_add_avito_visits_limit_into_roistat_company_cofings_table',22);
INSERT INTO `migrations` VALUES (38,'2018_03_23_122357_change_confirmation_elements_in_roistat_proxy_leads_reports',23);
INSERT INTO `migrations` VALUES (39,'2018_03_26_114907_add_public_name_for_company_in_companies_table',24);
INSERT INTO `migrations` VALUES (40,'2018_03_26_143346_create_approved_reports_table',24);
INSERT INTO `migrations` VALUES (41,'2018_03_27_124446_soft_deleting_companies',25);
INSERT INTO `migrations` VALUES (42,'2018_04_02_211818_create_sites_table',26);
INSERT INTO `migrations` VALUES (43,'2018_04_03_125212_fix_default_admin_value_in_roistat_proxy_leads_reports',27);
INSERT INTO `migrations` VALUES (44,'2018_04_04_103548_create_notifications_table',28);
INSERT INTO `migrations` VALUES (45,'2018_05_16_103927_create_company_amocrm_configs_table',29);
INSERT INTO `migrations` VALUES (46,'2018_05_16_122135_create_amocrm_pipelines_table',29);
INSERT INTO `migrations` VALUES (47,'2018_05_16_122553_create_amocrm_statuses_table',29);
INSERT INTO `migrations` VALUES (48,'2018_05_16_123746_create_amocrm_leads_table',29);
INSERT INTO `migrations` VALUES (49,'2018_06_04_113203_create_proxy_lead_settings_table',30);
INSERT INTO `migrations` VALUES (50,'2018_06_04_140307_create_proxy_leads_table',30);
INSERT INTO `migrations` VALUES (51,'2018_06_05_092343_create_yclients_settings_table',30);
INSERT INTO `migrations` VALUES (52,'2018_06_05_141517_create_pl_email_recipients_table',30);
INSERT INTO `migrations` VALUES (53,'2018_06_06_084624_create_pl_yc_companies_table',30);
INSERT INTO `migrations` VALUES (54,'2018_06_06_114725_create_pl_yc_company_staffs_table',30);
INSERT INTO `migrations` VALUES (55,'2018_06_07_165636_create_pl_yc_records_table',30);
INSERT INTO `migrations` VALUES (56,'2018_06_14_142310_create_jobs_table',31);
INSERT INTO `migrations` VALUES (57,'2018_06_14_142326_create_failed_jobs_table',31);
INSERT INTO `migrations` VALUES (58,'2018_06_15_084121_create_channels_table',32);
INSERT INTO `migrations` VALUES (59,'2018_06_15_084331_add_channel_to_company',32);
INSERT INTO `migrations` VALUES (60,'2018_06_15_161123_add_soft_delete_to_proxy_lead_table',32);
INSERT INTO `migrations` VALUES (61,'2018_06_15_162313_create_pl_report_leads_table',32);
INSERT INTO `migrations` VALUES (62,'2018_06_19_111425_create_pl_approved_reports_table',32);
INSERT INTO `migrations` VALUES (63,'2018_06_25_154151_add_type_to_pl_email_recipients',33);
INSERT INTO `migrations` VALUES (64,'2018_07_10_105652_create_reasons_of_rejections_table',34);
INSERT INTO `migrations` VALUES (65,'2018_07_10_110053_create_channel_reasons_of_rejections_table',34);
INSERT INTO `migrations` VALUES (66,'2018_07_10_124035_add_reasons_of_rejections_into_pl_report_leads_table',34);
INSERT INTO `migrations` VALUES (67,'2018_07_24_125218_create_reconclications_table',35);
INSERT INTO `migrations` VALUES (68,'2018_08_06_100554_change_proxy_lead_comment_length',36);
INSERT INTO `migrations` VALUES (69,'2018_08_08_140406_create_proxy_lead_goal_counters_table',37);
INSERT INTO `migrations` VALUES (70,'2018_08_23_121717_add_lead_cost_into_company',37);
INSERT INTO `migrations` VALUES (71,'2018_08_23_140705_create_finance_reports_table',37);
INSERT INTO `migrations` VALUES (72,'2018_08_24_082616_create_payments_table',37);
INSERT INTO `migrations` VALUES (73,'2018_08_29_091635_create_roistat_reconciliations_table',37);
INSERT INTO `migrations` VALUES (74,'2018_10_21_211216_create_schedule_task_logs_table',38);
INSERT INTO `migrations` VALUES (75,'2018_11_07_160820_add_tag_to_proxy_leads_table',39);
INSERT INTO `migrations` VALUES (76,'2018_11_14_095530_add_advertising_platform_to_proxy_leads',40);
INSERT INTO `migrations` VALUES (77,'2019_01_25_033843_add__attribute__json_2_proxy_leads',41);
INSERT INTO `migrations` VALUES (78,'2019_02_08_233343_add_reasons_of_rejections_records',42);
INSERT INTO `migrations` VALUES (79,'2019_06_15_144028_add__zadarma_config',43);
INSERT INTO `migrations` VALUES (80,'2019_06_17_203926_add_service_id',43);
INSERT INTO `migrations` VALUES (81,'2019_06_19_140524_create_request_loggers_table',44);
INSERT INTO `migrations` VALUES (82,'2019_10_21_221717_extend_link_at_proxy_lead',45);
INSERT INTO `migrations` VALUES (83,'2019_11_24_195111_add_bitrix_webhook_for_proxy_lead_settings',46);
INSERT INTO `migrations` VALUES (84,'2019_09_06_191926_add_field_balans_to_company_table',47);
INSERT INTO `migrations` VALUES (85,'2019_09_10_210205_create_payment_transaction_table',47);
INSERT INTO `migrations` VALUES (86,'2019_09_18_002601_change_type_filed_balance_to_company_table',47);
INSERT INTO `migrations` VALUES (87,'2019_09_18_011321_add_field_prepayment_to_companies_table',47);
INSERT INTO `migrations` VALUES (88,'2019_09_19_234644_add_new_field_limit_ammount_to_companies_table',47);
INSERT INTO `migrations` VALUES (89,'2019_09_27_015440_add_filed_inforamtion_to_payment_transaction_table',47);
INSERT INTO `migrations` VALUES (90,'2019_09_27_145945_change_type_field_company_inn_to_payment_transaction_table',47);
INSERT INTO `migrations` VALUES (91,'2019_09_28_152453_add_field_date_stop_leads_to_company_table',47);
INSERT INTO `migrations` VALUES (92,'2019_09_28_170351_add_free_period_to_company_table',47);
INSERT INTO `migrations` VALUES (93,'2019_09_28_175055_add_field_balance_limit_to_company_table',47);
INSERT INTO `migrations` VALUES (94,'2019_10_02_184346_change_default_balance_limit_to_companies_table',47);
INSERT INTO `migrations` VALUES (95,'2019_10_02_190738_change_add_default_balance_limit_to_companies_table',47);
INSERT INTO `migrations` VALUES (96,'2019_10_03_085955_entrust_setup_tables',47);
INSERT INTO `migrations` VALUES (97,'2019_10_05_204141_create_company_roles_table',47);
INSERT INTO `migrations` VALUES (98,'2019_10_06_152929_create_session_table',47);
INSERT INTO `migrations` VALUES (99,'2019_10_06_154308_create_sessions_table',47);
INSERT INTO `migrations` VALUES (100,'2019_10_06_165455_add_field_lead_cost_to_proxy_lead_goal_counters_table',47);
INSERT INTO `migrations` VALUES (101,'2019_10_06_191443_add_field_application_moderation_period_to_copanies_table',47);
INSERT INTO `migrations` VALUES (102,'2019_10_09_164307_add_field_not_before_called_to_lead_table',47);
INSERT INTO `migrations` VALUES (103,'2019_10_10_211628_add_field_company_id_to_users_table',47);
INSERT INTO `migrations` VALUES (104,'2019_10_20_190016_add_email_notification_settings',47);
INSERT INTO `migrations` VALUES (105,'2019_10_20_190943_add_email_manage_links',47);
INSERT INTO `migrations` VALUES (106,'2019_10_20_203525_add_unique_key_for_email_notification_settings',47);
INSERT INTO `migrations` VALUES (107,'2019_10_20_214007_add_unsubscribe_all_to_email_links',47);
INSERT INTO `migrations` VALUES (108,'2019_11_04_145126_add_photo_url_to_proxy_lead_table',47);
INSERT INTO `migrations` VALUES (109,'2019_11_07_234941_add_field_moderation_status_to_report_lead_table',47);
INSERT INTO `migrations` VALUES (110,'2019_11_10_160541_add_is_admin_field_to_notification',47);
INSERT INTO `migrations` VALUES (111,'2019_11_10_162239_add_one_more_email_notification_setting_type',47);
INSERT INTO `migrations` VALUES (112,'2019_11_10_164617_add_company_id_email_notification_setting',47);
INSERT INTO `migrations` VALUES (113,'2019_11_10_172634_add_company_admin_table',47);
INSERT INTO `migrations` VALUES (114,'2019_11_10_201211_add_manage_subscription_key_to_company',47);
INSERT INTO `migrations` VALUES (115,'2019_12_16_16311242_add_approve_description_to_company',47);
INSERT INTO `migrations` VALUES (116,'2020_02_05_230302_add_field_balance_stop_to_companies_table',47);
INSERT INTO `migrations` VALUES (117,'2020_02_18_233524_add_field_to_company_balanse_send_notification',48);
INSERT INTO `migrations` VALUES (118,'2020_02_19_135301_add_proxy_leads_column',48);
INSERT INTO `migrations` VALUES (119,'2020_03_02_194852_add_payment_transactions_column',49);
INSERT INTO `migrations` VALUES (120,'2020_03_03_204300_add_pl_report_leads_column',50);
INSERT INTO `migrations` VALUES (121,'2020_03_12_174734_add_email_notification_settings_column',51);
INSERT INTO `migrations` VALUES (122,'2020_03_19_161828_change_attempts',52);
INSERT INTO `migrations` VALUES (123,'2020_04_03_134039_create_tinkoff_logs_table',53);
INSERT INTO `migrations` VALUES (124,'2020_04_19_175418_add_proxy_lead_index_by_platform_and_id',54);
INSERT INTO `migrations` VALUES (125,'2020_04_26_182803_add_source_of_changes_to_payment_transaction',55);
INSERT INTO `migrations` VALUES (126,'2020_04_28_193703_change_text_for_removed_proxy_lead',56);
INSERT INTO `migrations` VALUES (127,'2020_05_07_162206_fix_user',57);
INSERT INTO `migrations` VALUES (128,'2020_05_03_165832_company_report',58);
INSERT INTO `migrations` VALUES (129,'2020_05_03_190243_add_indexes_to_company_report',58);
INSERT INTO `migrations` VALUES (130,'2020_05_03_193356_add_more_indexes_to_company_report',58);
INSERT INTO `migrations` VALUES (131,'2020_05_06_122254_add_report_date_to_company_report',58);
INSERT INTO `migrations` VALUES (132,'2020_05_13_164407_add_target_all_to_company_report',58);
INSERT INTO `migrations` VALUES (133,'2020_06_01_034017_create_accounts_table',58);
INSERT INTO `migrations` VALUES (134,'2020_06_01_034349_create_account_users_table',58);
INSERT INTO `migrations` VALUES (135,'2020_06_01_160736_create_account_companies_table',58);
INSERT INTO `migrations` VALUES (136,'2020_06_04_005705_create_yandex_settings_table',58);
INSERT INTO `migrations` VALUES (137,'2020_06_09_233048_create_account_settings',58);
INSERT INTO `migrations` VALUES (138,'2020_06_09_233433_create_about_companies',58);
INSERT INTO `migrations` VALUES (139,'2020_06_10_184621_move_report_date_column',58);
INSERT INTO `migrations` VALUES (140,'2020_06_11_000735_create_tinkoff_settings_table',58);
INSERT INTO `migrations` VALUES (141,'2020_06_12_175052_make_source_of_changes_in_payment_transactions_nullable',58);
INSERT INTO `migrations` VALUES (142,'2020_06_17_175406_add_inn_to_tinkoff_settings_table',58);
INSERT INTO `migrations` VALUES (143,'2020_06_17_175721_add_account_id_to_companies_table',58);
INSERT INTO `migrations` VALUES (144,'2020_06_17_175756_drop_account_companies_table',58);
INSERT INTO `migrations` VALUES (145,'2020_06_17_185203_add_account_id_to_tinkoff_logs_table',58);
INSERT INTO `migrations` VALUES (146,'2020_07_02_183347_add_account_to_channel',58);
INSERT INTO `migrations` VALUES (147,'2020_07_16_175221_modify_yandex_settings',58);
INSERT INTO `migrations` VALUES (148,'2020_07_22_182632_modify_tinkoff_settings',58);
INSERT INTO `migrations` VALUES (149,'2020_07_22_182942_modify_tinkoff_settings2',58);
INSERT INTO `migrations` VALUES (150,'2020_07_22_183053_modify_account_settings',58);
INSERT INTO `migrations` VALUES (151,'2020_07_22_183232_modify_about_settings',58);
INSERT INTO `migrations` VALUES (152,'2020_07_25_005013_modify_proxy_lead_settings',58);
INSERT INTO `migrations` VALUES (153,'2020_07_28_164551_add_cost_to_proxy_lead_table',58);
INSERT INTO `migrations` VALUES (154,'2020_08_20_005820_default_value',59);
INSERT INTO `migrations` VALUES (155,'2020_09_01_213520_default_name_for_roles',60);
