/*M!999999\- enable the sandbox mode */ 

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
DROP TABLE IF EXISTS `achievement_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievement_types` (
  `achievement_key` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ru` varchar(100) NOT NULL,
  `description_en` varchar(255) DEFAULT NULL,
  `description_ru` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`achievement_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Типы достижений пользователя (геймификация)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_actions_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_actions_log` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) NOT NULL,
  `action_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_type_key` varchar(100) NOT NULL,
  `target_entity_type_key` varchar(100) DEFAULT NULL,
  `target_entity_id` bigint(20) DEFAULT NULL,
  `details_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details_json`)),
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_adminlog_action_time` (`action_type_key`,`action_timestamp`),
  KEY `fk_adminlog_user` (`admin_user_id`),
  CONSTRAINT `fk_adminlog_user` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог действий администраторов';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ai_match_analysis_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_match_analysis_tasks` (
  `task_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `model_id` int(11) NOT NULL,
  `user_prompt_template_id` int(11) DEFAULT NULL,
  `system_prompt_template_id` int(11) DEFAULT NULL,
  `analysis_type_key` varchar(100) NOT NULL,
  `status_key` varchar(50) DEFAULT 'pending_prompt',
  `priority` tinyint(4) DEFAULT 5,
  `generated_system_prompt` text DEFAULT NULL,
  `generated_user_prompt` mediumtext DEFAULT NULL,
  `raw_ai_response` mediumtext DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `api_call_parameters_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`api_call_parameters_json`)),
  `token_usage_prompt` int(11) DEFAULT NULL,
  `token_usage_completion` int(11) DEFAULT NULL,
  `processing_time_ms` int(11) DEFAULT NULL,
  `prompt_generated_at` timestamp NULL DEFAULT NULL,
  `sent_to_ai_at` timestamp NULL DEFAULT NULL,
  `response_received_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`task_id`),
  KEY `idx_task_status_priority_created` (`status_key`,`priority`,`created_at`),
  KEY `idx_task_match_model_type` (`match_id`,`model_id`,`analysis_type_key`),
  KEY `fk_aimatchtasks_model` (`model_id`),
  KEY `fk_aimatchtasks_userprompt` (`user_prompt_template_id`),
  KEY `fk_aimatchtasks_systemprompt` (`system_prompt_template_id`),
  CONSTRAINT `fk_aimatchtasks_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aimatchtasks_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aimatchtasks_systemprompt` FOREIGN KEY (`system_prompt_template_id`) REFERENCES `ai_prompt_templates` (`template_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_aimatchtasks_userprompt` FOREIGN KEY (`user_prompt_template_id`) REFERENCES `ai_prompt_templates` (`template_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Задачи на анализ матчей для ИИ и их статусы';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ai_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_models` (
  `model_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `model_key` varchar(50) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `params_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`params_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`model_id`),
  UNIQUE KEY `idx_model_key_unique` (`model_key`),
  KEY `fk_aimodels_provider` (`provider_id`),
  CONSTRAINT `fk_aimodels_provider` FOREIGN KEY (`provider_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Список моделей ИИ (LLM, ML и др.)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ai_parsed_llm_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_parsed_llm_predictions` (
  `llm_prediction_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) NOT NULL,
  `match_id` bigint(20) NOT NULL,
  `model_id` int(11) NOT NULL,
  `predicted_market_id` int(11) DEFAULT NULL,
  `predicted_outcome_key` varchar(100) DEFAULT NULL,
  `predicted_parameter_value` varchar(100) DEFAULT NULL,
  `predicted_value_text_ru` varchar(1000) DEFAULT NULL,
  `predicted_value_text_en` varchar(1000) DEFAULT NULL,
  `predicted_value_numeric_min` decimal(10,3) DEFAULT NULL,
  `predicted_value_numeric_max` decimal(10,3) DEFAULT NULL,
  `confidence_score` decimal(5,4) DEFAULT NULL,
  `probability_distribution_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`probability_distribution_json`)),
  `reasoning_summary_ru` text DEFAULT NULL,
  `reasoning_summary_en` text DEFAULT NULL,
  `strategy_suggestion_ru` text DEFAULT NULL,
  `strategy_suggestion_en` text DEFAULT NULL,
  `potential_risks_identified_ru` text DEFAULT NULL,
  `potential_risks_identified_en` text DEFAULT NULL,
  `key_factors_highlighted_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`key_factors_highlighted_json`)),
  `parsed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`llm_prediction_id`),
  UNIQUE KEY `idx_task_id_unique_llm_pred` (`task_id`),
  KEY `idx_llm_pred_match_model_market` (`match_id`,`model_id`,`predicted_market_id`),
  KEY `fk_aillmpred_model` (`model_id`),
  KEY `fk_aillmpred_market` (`predicted_market_id`),
  CONSTRAINT `fk_aillmpred_market` FOREIGN KEY (`predicted_market_id`) REFERENCES `betting_markets` (`market_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_aillmpred_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aillmpred_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aillmpred_task` FOREIGN KEY (`task_id`) REFERENCES `ai_match_analysis_tasks` (`task_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Структурированные прогнозы от LLM ИИ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ai_prediction_accuracy_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_prediction_accuracy_log` (
  `accuracy_log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `prediction_source_type_key` varchar(50) NOT NULL COMMENT 'Ключ источника (llm_prediction, ml_prediction, ensemble_prediction, user_prediction)',
  `prediction_source_id` bigint(20) NOT NULL COMMENT 'ID из соответствующей таблицы прогнозов',
  `match_id` bigint(20) NOT NULL,
  `model_id` int(11) DEFAULT NULL,
  `market_evaluated_id` int(11) NOT NULL,
  `predicted_outcome_key` varchar(100) NOT NULL,
  `predicted_parameter_value` varchar(100) DEFAULT NULL,
  `actual_outcome_key` varchar(100) NOT NULL,
  `actual_parameter_value` varchar(100) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `accuracy_score_value` decimal(10,4) DEFAULT NULL,
  `accuracy_score_type_key` varchar(50) DEFAULT NULL,
  `evaluation_notes_ru` text DEFAULT NULL,
  `evaluation_notes_en` text DEFAULT NULL,
  `evaluated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`accuracy_log_id`),
  KEY `idx_accuracy_pred_source` (`prediction_source_type_key`,`prediction_source_id`),
  KEY `idx_accuracy_match_model_market` (`match_id`,`model_id`,`market_evaluated_id`),
  KEY `fk_aipredaccuracy_model` (`model_id`),
  KEY `fk_aipredaccuracy_market` (`market_evaluated_id`),
  CONSTRAINT `fk_aipredaccuracy_market` FOREIGN KEY (`market_evaluated_id`) REFERENCES `betting_markets` (`market_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aipredaccuracy_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_aipredaccuracy_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Оценка точности прогнозов ИИ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ai_prompt_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_prompt_templates` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `sport_id` tinyint(3) unsigned DEFAULT NULL,
  `model_key` varchar(50) NOT NULL,
  `language_code` char(5) NOT NULL,
  `prompt_text` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`template_id`),
  KEY `fk_aiprompttemplates_sport` (`sport_id`),
  CONSTRAINT `fk_aiprompttemplates_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Шаблоны подсказок (prompts) для ИИ анализа';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `betting_markets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `betting_markets` (
  `market_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `market_key` varchar(100) NOT NULL,
  `market_name_en` varchar(100) NOT NULL,
  `market_name_ru` varchar(100) NOT NULL,
  `market_category_name_en` varchar(100) DEFAULT NULL,
  `market_category_name_ru` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`market_id`),
  UNIQUE KEY `idx_market_sport_key` (`sport_id`,`market_key`),
  CONSTRAINT `fk_bettingmarkets_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник рынков ставок';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bonus_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bonus_types` (
  `bonus_type_key` varchar(50) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ru` varchar(100) NOT NULL,
  `description_en` varchar(255) DEFAULT NULL,
  `description_ru` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bonus_type_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Типы бонусов и наград';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bookmakers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookmakers` (
  `bookmaker_id` int(11) NOT NULL AUTO_INCREMENT,
  `name_en` varchar(100) NOT NULL,
  `name_ru` varchar(100) NOT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`bookmaker_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник букмекеров';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clubs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clubs` (
  `club_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) NOT NULL,
  `name_ru` varchar(255) NOT NULL,
  `short_name_en` varchar(100) DEFAULT NULL,
  `short_name_ru` varchar(100) DEFAULT NULL,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `country_id` smallint(5) unsigned DEFAULT NULL,
  `founded_year` smallint(5) unsigned DEFAULT NULL,
  `home_venue_id` int(11) DEFAULT NULL,
  `current_coach_id` int(10) unsigned DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `club_photo_generic_url` varchar(255) DEFAULT NULL,
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `slug_ru` varchar(300) DEFAULT NULL,
  `slug_en` varchar(300) DEFAULT NULL,
  `meta_title_ru` varchar(300) DEFAULT NULL,
  `meta_title_en` varchar(300) DEFAULT NULL,
  `meta_description_ru` text DEFAULT NULL,
  `meta_description_en` text DEFAULT NULL,
  `meta_keywords_ru` text DEFAULT NULL,
  `meta_keywords_en` text DEFAULT NULL,
  `source_club_id` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`club_id`),
  UNIQUE KEY `idx_club_slug_ru` (`slug_ru`),
  UNIQUE KEY `idx_club_slug_en` (`slug_en`),
  KEY `idx_club_sport_name_ru` (`sport_id`,`name_ru`(191)),
  KEY `idx_club_sport_name_en` (`sport_id`,`name_en`(191)),
  KEY `fk_clubs_country` (`country_id`),
  KEY `fk_clubs_venue` (`home_venue_id`),
  KEY `fk_clubs_coach` (`current_coach_id`),
  KEY `fk_clubs_source` (`source_id`),
  CONSTRAINT `fk_clubs_coach` FOREIGN KEY (`current_coach_id`) REFERENCES `coaches` (`coach_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_clubs_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_clubs_source` FOREIGN KEY (`source_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_clubs_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`),
  CONSTRAINT `fk_clubs_venue` FOREIGN KEY (`home_venue_id`) REFERENCES `venues` (`venue_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Спортивные клубы';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coaches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coaches` (
  `coach_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name_en` varchar(255) NOT NULL,
  `full_name_ru` varchar(255) DEFAULT NULL,
  `short_name_en` varchar(100) DEFAULT NULL,
  `short_name_ru` varchar(100) DEFAULT NULL,
  `nationality_country_id` smallint(5) unsigned DEFAULT NULL,
  `birth_country_id` smallint(5) unsigned DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `source_coach_id` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`coach_id`),
  UNIQUE KEY `idx_coach_fullname_en_dob` (`full_name_en`,`date_of_birth`),
  KEY `fk_coaches_nationality_country` (`nationality_country_id`),
  KEY `fk_coaches_birth_country` (`birth_country_id`),
  KEY `fk_coaches_source` (`source_id`),
  CONSTRAINT `fk_coaches_birth_country` FOREIGN KEY (`birth_country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_coaches_nationality_country` FOREIGN KEY (`nationality_country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_coaches_source` FOREIGN KEY (`source_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Тренеры';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `content_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_articles` (
  `article_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `article_type_key` varchar(50) NOT NULL COMMENT 'Ключ типа (match_preview, match_review, league_overview, player_profile_extended, betting_guide, news_item, general_seo_page)',
  `related_entity_type_key` varchar(50) DEFAULT NULL,
  `related_entity_id` bigint(20) DEFAULT NULL,
  `title_ru` varchar(500) NOT NULL,
  `title_en` varchar(500) NOT NULL,
  `slug_ru` varchar(550) NOT NULL,
  `slug_en` varchar(550) NOT NULL,
  `content_ru_html` mediumtext DEFAULT NULL,
  `content_en_html` mediumtext DEFAULT NULL,
  `content_ru_markdown` mediumtext DEFAULT NULL,
  `content_en_markdown` mediumtext DEFAULT NULL,
  `excerpt_ru` text DEFAULT NULL,
  `excerpt_en` text DEFAULT NULL,
  `author_user_id` int(11) DEFAULT NULL,
  `author_ai_model_id` int(11) DEFAULT NULL,
  `status_key` varchar(50) DEFAULT 'draft' COMMENT 'draft, published, archived, pending_review',
  `access_level_key` varchar(50) DEFAULT 'free' COMMENT 'Ключ уровня доступа (free, registered_only, premium_subscribers, product_x_access)',
  `published_at` timestamp NULL DEFAULT NULL,
  `featured_image_url` varchar(255) DEFAULT NULL,
  `meta_title_ru` varchar(300) DEFAULT NULL,
  `meta_title_en` varchar(300) DEFAULT NULL,
  `meta_description_ru` text DEFAULT NULL,
  `meta_description_en` text DEFAULT NULL,
  `meta_keywords_ru` text DEFAULT NULL,
  `meta_keywords_en` text DEFAULT NULL,
  `tags_json_ru` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON массив тегов на русском' CHECK (json_valid(`tags_json_ru`)),
  `tags_json_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON массив тегов на английском' CHECK (json_valid(`tags_json_en`)),
  `schema_org_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Структурированные данные Schema.org' CHECK (json_valid(`schema_org_json`)),
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`article_id`),
  UNIQUE KEY `idx_slug_ru_unique_articles` (`slug_ru`),
  UNIQUE KEY `idx_slug_en_unique_articles` (`slug_en`),
  KEY `idx_article_type_status_published` (`article_type_key`,`status_key`,`published_at`),
  KEY `idx_article_related_entity` (`related_entity_type_key`,`related_entity_id`),
  KEY `fk_contentarticles_author_user` (`author_user_id`),
  KEY `fk_contentarticles_author_ai` (`author_ai_model_id`),
  CONSTRAINT `fk_contentarticles_author_ai` FOREIGN KEY (`author_ai_model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contentarticles_author_user` FOREIGN KEY (`author_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Статьи, обзоры, новости и другой текстовый SEO-контент';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `country_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `country_name_en` varchar(150) NOT NULL COMMENT 'Название страны на английском языке',
  `country_name_ru` varchar(150) NOT NULL COMMENT 'Название страны на русском языке',
  `iso_alpha2` char(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2 код',
  `iso_alpha3` char(3) NOT NULL COMMENT 'ISO 3166-1 alpha-3 код',
  `numeric_code` char(3) DEFAULT NULL COMMENT 'ISO 3166-1 numeric код (опционально, но полезно)',
  `flag_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`country_id`),
  UNIQUE KEY `idx_country_name_en_unique` (`country_name_en`),
  UNIQUE KEY `idx_country_name_ru_unique` (`country_name_ru`),
  UNIQUE KEY `iso_alpha2` (`iso_alpha2`),
  UNIQUE KEY `iso_alpha3` (`iso_alpha3`),
  UNIQUE KEY `idx_numeric_code_unique` (`numeric_code`)
) ENGINE=InnoDB AUTO_INCREMENT=831 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник стран с мультиязычными названиями';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_sources` (
  `source_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(150) NOT NULL COMMENT 'Уникальное имя источника (напр. API_Football)',
  `source_type` varchar(50) NOT NULL COMMENT 'Тип источника (api, parser, manual, etc.)',
  `website_url` varchar(255) DEFAULT NULL COMMENT 'Веб-сайт / API endpoint источника',
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`source_id`),
  UNIQUE KEY `idx_source_name_unique` (`source_name`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник источников данных (API, парсеры и т.д.)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_types` (
  `event_type_key` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ru` varchar(100) NOT NULL,
  `description_en` varchar(255) DEFAULT NULL,
  `description_ru` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`event_type_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Типы событий матча (гол, ЖК, VAR, замена и др.)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_ai_content_generation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_ai_content_generation_log` (
  `generation_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `content_article_id` bigint(20) DEFAULT NULL,
  `target_entity_type_key` varchar(50) DEFAULT NULL,
  `target_entity_id` bigint(20) DEFAULT NULL,
  `language_code` char(5) NOT NULL,
  `page_type_key` varchar(50) NOT NULL,
  `ai_model_id` int(11) DEFAULT NULL,
  `prompt_template_id` int(11) DEFAULT NULL,
  `custom_prompt_text` text DEFAULT NULL,
  `generated_title_ru` varchar(500) DEFAULT NULL,
  `generated_title_en` varchar(500) DEFAULT NULL,
  `generated_content_ru_html` mediumtext DEFAULT NULL,
  `generated_content_en_html` mediumtext DEFAULT NULL,
  `generated_meta_description_ru` text DEFAULT NULL,
  `generated_meta_description_en` text DEFAULT NULL,
  `generation_parameters_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`generation_parameters_json`)),
  `token_usage_prompt` int(11) DEFAULT NULL,
  `token_usage_completion` int(11) DEFAULT NULL,
  `status_key` varchar(50) DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`generation_id`),
  KEY `idx_aicontentlog_target_type_page` (`target_entity_type_key`,`target_entity_id`,`page_type_key`),
  KEY `fk_aicontentlog_article` (`content_article_id`),
  KEY `fk_aicontentlog_model` (`ai_model_id`),
  KEY `fk_aicontentlog_prompt` (`prompt_template_id`),
  CONSTRAINT `fk_aicontentlog_article` FOREIGN KEY (`content_article_id`) REFERENCES `content_articles` (`article_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_aicontentlog_model` FOREIGN KEY (`ai_model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_aicontentlog_prompt` FOREIGN KEY (`prompt_template_id`) REFERENCES `ai_prompt_templates` (`template_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог генерации контента с помощью ИИ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_ai_data_filler_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_ai_data_filler_tasks` (
  `filler_task_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `target_entity_table` varchar(100) NOT NULL,
  `target_entity_id` bigint(20) NOT NULL,
  `data_field_to_fill` varchar(255) NOT NULL,
  `assigned_ai_model_id` int(11) DEFAULT NULL,
  `prompt_template_id` int(11) DEFAULT NULL,
  `custom_prompt_text` text DEFAULT NULL,
  `source_urls_to_check` text DEFAULT NULL,
  `status_key` varchar(50) DEFAULT 'pending',
  `retrieved_data_raw` text DEFAULT NULL,
  `retrieved_data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`retrieved_data_json`)),
  `validation_user_id` int(11) DEFAULT NULL,
  `validation_comment` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `priority` tinyint(4) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`filler_task_id`),
  KEY `idx_filler_status_priority` (`status_key`,`priority`),
  KEY `idx_filler_target_entity_field` (`target_entity_table`,`target_entity_id`,`data_field_to_fill`(191)),
  KEY `fk_extaidatafiller_model` (`assigned_ai_model_id`),
  KEY `fk_extaidatafiller_prompt` (`prompt_template_id`),
  KEY `fk_extaidatafiller_user` (`validation_user_id`),
  CONSTRAINT `fk_extaidatafiller_model` FOREIGN KEY (`assigned_ai_model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extaidatafiller_prompt` FOREIGN KEY (`prompt_template_id`) REFERENCES `ai_prompt_templates` (`template_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extaidatafiller_user` FOREIGN KEY (`validation_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Задачи для ИИ по заполнению недостающих данных';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_ai_explanations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_ai_explanations` (
  `explanation_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `related_entity_type_key` varchar(100) NOT NULL,
  `related_entity_id` bigint(20) NOT NULL,
  `ai_model_id` int(11) DEFAULT NULL,
  `explanation_type_key` varchar(100) DEFAULT NULL,
  `explanation_text_ru` mediumtext DEFAULT NULL,
  `explanation_text_en` mediumtext DEFAULT NULL,
  `supporting_data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supporting_data_json`)),
  `language_code_generated` char(5) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`explanation_id`),
  KEY `idx_explanation_related_entity` (`related_entity_type_key`,`related_entity_id`),
  KEY `fk_extaiexplanations_model` (`ai_model_id`),
  CONSTRAINT `fk_extaiexplanations_model` FOREIGN KEY (`ai_model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Объяснения от ИИ для прогнозов, стратегий и т.д.';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_betting_strategies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_betting_strategies` (
  `strategy_id` int(11) NOT NULL AUTO_INCREMENT,
  `strategy_name_ru` varchar(255) NOT NULL,
  `strategy_name_en` varchar(255) NOT NULL,
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `sport_id` tinyint(3) unsigned DEFAULT NULL,
  `strategy_type_key` varchar(100) NOT NULL,
  `trigger_conditions_schema_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`trigger_conditions_schema_json`)),
  `bet_placement_logic_description` text DEFAULT NULL,
  `risk_level_key` varchar(50) DEFAULT 'medium',
  `parameters_schema_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters_schema_json`)),
  `creator_user_id` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `is_system_strategy` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`strategy_id`),
  UNIQUE KEY `idx_strategy_name_ru_unique` (`strategy_name_ru`),
  UNIQUE KEY `idx_strategy_name_en_unique` (`strategy_name_en`),
  KEY `fk_extbettingstrategies_sport` (`sport_id`),
  KEY `fk_extbettingstrategies_user` (`creator_user_id`),
  CONSTRAINT `fk_extbettingstrategies_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extbettingstrategies_user` FOREIGN KEY (`creator_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник стратегий ставок';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_bonus_transactions_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_bonus_transactions_log` (
  `transaction_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `transaction_type_key` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance_after_transaction` decimal(12,2) NOT NULL,
  `related_entity_type_key` varchar(50) DEFAULT NULL,
  `related_entity_id` bigint(20) DEFAULT NULL,
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `transaction_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `idx_user_transaction_type_time` (`user_id`,`transaction_type_key`,`transaction_timestamp`),
  KEY `fk_extbonustrans_admin` (`admin_user_id`),
  CONSTRAINT `fk_extbonustrans_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extbonustrans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог транзакций по бонусным очкам пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_coach_assignments_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_coach_assignments_history` (
  `assignment_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `coach_id` int(10) unsigned NOT NULL,
  `club_id` int(10) unsigned NOT NULL,
  `role_key` varchar(100) DEFAULT 'head_coach',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason_for_leaving_key` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`assignment_id`),
  KEY `idx_club_coach_dates` (`club_id`,`coach_id`,`start_date`,`end_date`),
  KEY `idx_coach_club_dates` (`coach_id`,`club_id`,`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История назначений тренеров в клубы';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_community_leaderboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_community_leaderboards` (
  `leaderboard_entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `leaderboard_type_key` varchar(100) NOT NULL,
  `period_key` varchar(50) NOT NULL,
  `rank_position` int(11) NOT NULL,
  `score` decimal(12,4) NOT NULL,
  `additional_stats_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_stats_json`)),
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`leaderboard_entry_id`),
  UNIQUE KEY `idx_user_leaderboard_period` (`user_id`,`leaderboard_type_key`,`period_key`),
  KEY `idx_leaderboard_type_score_rank` (`leaderboard_type_key`,`period_key`,`score`,`rank_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лидерборды и рейтинги пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_demo_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_demo_accounts` (
  `demo_account_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `account_name_ru` varchar(100) DEFAULT 'Основной демо-счет',
  `account_name_en` varchar(100) DEFAULT 'Main Demo Account',
  `currency_code` char(3) DEFAULT 'VIR',
  `starting_balance` decimal(15,2) NOT NULL DEFAULT 1000.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 1000.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`demo_account_id`),
  UNIQUE KEY `idx_user_id_unique_demo_acc` (`user_id`),
  CONSTRAINT `fk_extdemoacc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Демо-счета пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_demo_bets_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_demo_bets_log` (
  `demo_bet_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `demo_account_id` int(11) NOT NULL,
  `strategy_evaluation_log_id` bigint(20) DEFAULT NULL,
  `match_id` bigint(20) NOT NULL,
  `market_id` int(11) NOT NULL,
  `selected_outcome_key` varchar(100) NOT NULL,
  `selected_parameter_value` varchar(100) DEFAULT NULL,
  `odds_value` decimal(8,3) NOT NULL,
  `stake_amount` decimal(10,2) NOT NULL,
  `potential_payout` decimal(12,2) GENERATED ALWAYS AS (`stake_amount` * `odds_value`) STORED,
  `bet_status_key` varchar(50) NOT NULL DEFAULT 'pending_result',
  `actual_profit_loss` decimal(10,2) DEFAULT NULL,
  `bet_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `result_processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`demo_bet_id`),
  UNIQUE KEY `idx_strategy_eval_log_id_unique_demo_bet` (`strategy_evaluation_log_id`),
  KEY `idx_demo_bet_account_match` (`demo_account_id`,`match_id`),
  KEY `fk_extdemobets_match` (`match_id`),
  KEY `fk_extdemobets_market` (`market_id`),
  CONSTRAINT `fk_extdemobets_account` FOREIGN KEY (`demo_account_id`) REFERENCES `ext_demo_accounts` (`demo_account_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extdemobets_market` FOREIGN KEY (`market_id`) REFERENCES `betting_markets` (`market_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extdemobets_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extdemobets_strategy_eval` FOREIGN KEY (`strategy_evaluation_log_id`) REFERENCES `ext_strategy_evaluations_log` (`evaluation_log_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог демо-ставок';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_ensemble_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_ensemble_predictions` (
  `ensemble_prediction_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `ensemble_strategy_key` varchar(100) NOT NULL,
  `ensemble_predictions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON с итоговыми предсказаниями ансамбля' CHECK (json_valid(`ensemble_predictions_json`)),
  `contributing_predictions_info_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON с ID участвовавших прогнозов и их весами' CHECK (json_valid(`contributing_predictions_info_json`)),
  `overall_confidence_score` decimal(5,4) DEFAULT NULL,
  `prediction_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ensemble_prediction_id`),
  UNIQUE KEY `idx_ensemble_match_strategy` (`match_id`,`ensemble_strategy_key`),
  CONSTRAINT `fk_extensemble_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Итоговые прогнозы от ансамблей моделей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_entity_ratings_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_entity_ratings_history` (
  `rating_history_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `entity_id` int(10) unsigned NOT NULL,
  `entity_type` enum('club','player','coach') NOT NULL,
  `rating_system_key` varchar(50) NOT NULL,
  `reference_date` date NOT NULL,
  `reference_match_id` bigint(20) DEFAULT NULL,
  `rating_value` decimal(10,4) NOT NULL,
  `rank_position` int(11) DEFAULT NULL,
  `confidence_interval_low` decimal(10,4) DEFAULT NULL,
  `confidence_interval_high` decimal(10,4) DEFAULT NULL,
  `volatility` decimal(10,6) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`rating_history_id`),
  UNIQUE KEY `idx_entity_type_system_date` (`entity_id`,`entity_type`,`rating_system_key`,`reference_date`),
  UNIQUE KEY `idx_entity_type_system_match` (`entity_id`,`entity_type`,`rating_system_key`,`reference_match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История различных рейтингов клубов, игроков или тренеров';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_h2h_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_h2h_summary` (
  `h2h_summary_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `entity1_id` int(10) unsigned NOT NULL,
  `entity1_type` enum('club','player') NOT NULL,
  `entity2_id` int(10) unsigned NOT NULL,
  `entity2_type` enum('club','player') NOT NULL,
  `total_matches` int(11) DEFAULT 0,
  `entity1_wins` int(11) DEFAULT 0,
  `entity2_wins` int(11) DEFAULT 0,
  `draws` int(11) DEFAULT 0,
  `entity1_score_avg` decimal(5,2) DEFAULT NULL,
  `entity2_score_avg` decimal(5,2) DEFAULT NULL,
  `avg_total_score` decimal(5,2) DEFAULT NULL,
  `last_meeting_match_id` bigint(20) DEFAULT NULL,
  `last_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`h2h_summary_id`),
  UNIQUE KEY `idx_h2h_sport_entities_types` (`sport_id`,`entity1_id`,`entity1_type`,`entity2_id`,`entity2_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Агрегированная статистика личных встреч (H2H)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_injuries_suspensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_injuries_suspensions` (
  `entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `club_id` int(10) unsigned DEFAULT NULL,
  `type_key` varchar(50) NOT NULL COMMENT 'Ключ (injury, suspension, illness)',
  `description_ru` varchar(255) NOT NULL,
  `description_en` varchar(255) DEFAULT NULL,
  `severity_key` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status_key` varchar(50) NOT NULL,
  `games_missed_estimated` smallint(6) DEFAULT NULL,
  `games_missed_actual` smallint(6) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `notes_ru` text DEFAULT NULL,
  `notes_en` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`entry_id`),
  KEY `idx_player_inj_status_dates` (`player_id`,`status_key`,`start_date`,`expected_return_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Травмы, дисквалификации и отсутствия игроков';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_match_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_match_events` (
  `event_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `event_minute` smallint(6) DEFAULT NULL,
  `event_extra_minute` smallint(6) DEFAULT NULL,
  `event_second` smallint(6) DEFAULT NULL,
  `period_key` varchar(50) NOT NULL COMMENT 'Ключ периода (PRE_MATCH, FIRST_HALF, SET_1)',
  `club_id` int(10) unsigned DEFAULT NULL COMMENT 'Клуб, совершивший/пострадавший от события',
  `player_id` int(10) unsigned DEFAULT NULL,
  `assisting_player_id` int(10) unsigned DEFAULT NULL,
  `event_type_key` varchar(100) NOT NULL,
  `event_subtype_key` varchar(100) DEFAULT NULL,
  `body_part_key` varchar(50) DEFAULT NULL,
  `shot_outcome_key` varchar(50) DEFAULT NULL,
  `x_coordinate_start` smallint(6) DEFAULT NULL,
  `y_coordinate_start` smallint(6) DEFAULT NULL,
  `x_coordinate_end` smallint(6) DEFAULT NULL,
  `y_coordinate_end` smallint(6) DEFAULT NULL,
  `xg_value` decimal(6,4) DEFAULT NULL,
  `xa_value` decimal(6,4) DEFAULT NULL,
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `raw_source_event_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Сырые данные события от источника' CHECK (json_valid(`raw_source_event_json`)),
  `related_event_id` bigint(20) DEFAULT NULL,
  `source_event_id` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `idx_match_event_time_period` (`match_id`,`period_key`,`event_minute`),
  KEY `idx_event_type_player` (`event_type_key`,`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Детальные события матча';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_match_features_for_ml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_match_features_for_ml` (
  `feature_set_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `feature_vector_version` varchar(50) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `features_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON объект со всеми признаками (ключ:значение)' CHECK (json_valid(`features_json`)),
  `feature_importance_shap_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'SHAP values для этого набора признаков и матча' CHECK (json_valid(`feature_importance_shap_json`)),
  `target_outcome_1x2_key` varchar(20) DEFAULT NULL COMMENT 'Ключ исхода (HOME_WIN, DRAW, AWAY_WIN)',
  `target_total_goals_over_2_5` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`feature_set_id`),
  UNIQUE KEY `idx_match_feature_version_unique` (`match_id`,`feature_vector_version`),
  CONSTRAINT `fk_extmatchfeatures_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Наборы признаков (фич) для обучения ML моделей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_match_tactics_formations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_match_tactics_formations` (
  `match_tactic_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `club_id` int(10) unsigned NOT NULL,
  `period_key` varchar(50) DEFAULT 'EXPECTED_PRE_MATCH',
  `time_slice_start_minute` smallint(6) DEFAULT NULL,
  `time_slice_end_minute` smallint(6) DEFAULT NULL,
  `formation_key` varchar(50) DEFAULT NULL,
  `offensive_style_tags_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`offensive_style_tags_json`)),
  `defensive_style_tags_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`defensive_style_tags_json`)),
  `key_tactical_notes_ru` text DEFAULT NULL,
  `key_tactical_notes_en` text DEFAULT NULL,
  `avg_player_positions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`avg_player_positions_json`)),
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`match_tactic_id`),
  UNIQUE KEY `idx_match_club_period_tactic` (`match_id`,`club_id`,`period_key`,`time_slice_start_minute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Тактические установки и формации клубов в матче';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_ml_model_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_ml_model_predictions` (
  `ml_prediction_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `model_id` int(11) NOT NULL COMMENT 'FK на ai_models (где тип ML)',
  `feature_set_id` bigint(20) DEFAULT NULL,
  `predictions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON с предсказаниями {market_key: {outcome_key: prob}}' CHECK (json_valid(`predictions_json`)),
  `prediction_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ml_prediction_id`),
  KEY `idx_ml_pred_match_model` (`match_id`,`model_id`),
  KEY `fk_extmlpred_model` (`model_id`),
  KEY `fk_extmlpred_featureset` (`feature_set_id`),
  CONSTRAINT `fk_extmlpred_featureset` FOREIGN KEY (`feature_set_id`) REFERENCES `ext_match_features_for_ml` (`feature_set_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extmlpred_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extmlpred_model` FOREIGN KEY (`model_id`) REFERENCES `ai_models` (`model_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Предсказания, сделанные собственными ML моделями';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_partner_payouts_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_partner_payouts_log` (
  `payout_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partner_user_id` int(11) NOT NULL COMMENT 'FK на users (пользователь со статусом партнера)',
  `payout_period_start_date` date NOT NULL,
  `payout_period_end_date` date NOT NULL,
  `total_commission_earned` decimal(12,2) NOT NULL,
  `payout_amount_requested` decimal(12,2) NOT NULL,
  `payout_amount_final` decimal(12,2) DEFAULT NULL,
  `currency_code` char(3) NOT NULL DEFAULT 'RUB',
  `payout_method_details_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Детали выплаты (напр. номер кошелька, банк. счет)' CHECK (json_valid(`payout_method_details_json`)),
  `status_key` varchar(50) NOT NULL COMMENT 'pending_request, pending_processing, processing_payment, paid_success, paid_failed, cancelled_by_admin',
  `external_transaction_id` varchar(255) DEFAULT NULL COMMENT 'ID транзакции в платежной системе',
  `admin_approver_id` int(11) DEFAULT NULL COMMENT 'Кто из админов одобрил/обработал выплату (FK users)',
  `initiated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Время запроса на выплату партнером',
  `processed_at` timestamp NULL DEFAULT NULL COMMENT 'Время фактической обработки/отправки выплаты',
  `notes_admin` text DEFAULT NULL,
  PRIMARY KEY (`payout_id`),
  KEY `idx_partnerpayout_status_period` (`status_key`,`payout_period_start_date`,`payout_period_end_date`),
  KEY `fk_partnerpayout_partner` (`partner_user_id`),
  KEY `fk_partnerpayout_admin` (`admin_approver_id`),
  CONSTRAINT `fk_partnerpayout_admin` FOREIGN KEY (`admin_approver_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_partnerpayout_partner` FOREIGN KEY (`partner_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог начислений и выплат партнерам';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_player_form_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_player_form_history` (
  `player_form_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `season_id` int(11) DEFAULT NULL,
  `reference_match_id` bigint(20) DEFAULT NULL,
  `reference_date` date NOT NULL,
  `surface_key` varchar(50) DEFAULT 'all',
  `matches_considered_count` smallint(6) NOT NULL DEFAULT 5,
  `wins` smallint(6) DEFAULT 0,
  `losses` smallint(6) DEFAULT 0,
  `sets_won` smallint(6) DEFAULT 0,
  `sets_lost` smallint(6) DEFAULT 0,
  `games_won` smallint(6) DEFAULT 0,
  `games_lost` smallint(6) DEFAULT 0,
  `aces_avg_per_match` decimal(5,2) DEFAULT NULL,
  `double_faults_avg_per_match` decimal(5,2) DEFAULT NULL,
  `first_serve_pct_avg` decimal(5,2) DEFAULT NULL,
  `first_serve_points_won_pct_avg` decimal(5,2) DEFAULT NULL,
  `second_serve_points_won_pct_avg` decimal(5,2) DEFAULT NULL,
  `break_points_saved_pct_avg` decimal(5,2) DEFAULT NULL,
  `break_points_converted_pct_avg` decimal(5,2) DEFAULT NULL,
  `return_points_won_pct_avg` decimal(5,2) DEFAULT NULL,
  `form_sequence_keys` varchar(50) DEFAULT NULL,
  `form_score_calculated` decimal(7,4) DEFAULT NULL,
  `source_calculation_method_key` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`player_form_id`),
  UNIQUE KEY `idx_player_refmatch_surface_considered` (`player_id`,`reference_match_id`,`surface_key`,`matches_considered_count`),
  UNIQUE KEY `idx_player_refdate_surface_considered` (`player_id`,`reference_date`,`surface_key`,`matches_considered_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История формы игрока (особенно для тенниса)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_products_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_products_services` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_key` varchar(100) NOT NULL,
  `name_ru` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `product_type_key` enum('subscription','one_time_purchase','token_pack','feature_unlock') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency_code` char(3) DEFAULT 'RUB',
  `duration_days` int(11) DEFAULT NULL,
  `features_included_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_included_json`)),
  `bonus_points_awarded` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `product_key` (`product_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник платных продуктов и услуг';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_referee_match_stats_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_referee_match_stats_summary` (
  `referee_stat_summary_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `referee_id` int(11) NOT NULL,
  `season_id` int(11) DEFAULT NULL,
  `league_id` int(11) DEFAULT NULL,
  `scope_key` varchar(50) DEFAULT 'all_time',
  `total_matches_officiated` int(11) DEFAULT 0,
  `avg_yellow_cards_per_match` decimal(5,2) DEFAULT NULL,
  `avg_red_cards_per_match` decimal(5,2) DEFAULT NULL,
  `avg_penalties_awarded_per_match` decimal(5,2) DEFAULT NULL,
  `avg_fouls_called_per_match` decimal(5,2) DEFAULT NULL,
  `home_team_win_pct` decimal(5,2) DEFAULT NULL,
  `avg_yellow_cards_home` decimal(5,2) DEFAULT NULL,
  `avg_yellow_cards_away` decimal(5,2) DEFAULT NULL,
  `last_calculated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`referee_stat_summary_id`),
  UNIQUE KEY `idx_referee_scope_season_league` (`referee_id`,`scope_key`,`season_id`,`league_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Агрегированная статистика по работе судей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_referral_events_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_referral_events_log` (
  `event_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `referrer_user_id` int(11) NOT NULL COMMENT 'Кто привлек (FK users)',
  `referred_user_id` int(11) NOT NULL COMMENT 'Кого привлекли (новый пользователь, FK users)',
  `event_type_key` varchar(100) NOT NULL COMMENT 'Тип события (user_registered_via_referral, first_purchase_by_referred, subscription_renewal_by_referred)',
  `related_payment_id` bigint(20) DEFAULT NULL COMMENT 'FK на ext_user_payments_purchases, если событие - покупка/платеж',
  `bonus_awarded_to_referrer` decimal(10,2) DEFAULT NULL,
  `commission_earned_by_referrer` decimal(10,2) DEFAULT NULL,
  `event_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `idx_referral_referrer_event` (`referrer_user_id`,`event_type_key`),
  KEY `idx_referral_referred_event` (`referred_user_id`,`event_type_key`),
  KEY `fk_referral_payment` (`related_payment_id`),
  CONSTRAINT `fk_referral_payment` FOREIGN KEY (`related_payment_id`) REFERENCES `ext_user_payments_purchases` (`payment_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_referral_referred` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_referral_referrer` FOREIGN KEY (`referrer_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог событий реферальной программы';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_strategy_evaluations_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_strategy_evaluations_log` (
  `evaluation_log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `strategy_id` int(11) NOT NULL,
  `match_id` bigint(20) NOT NULL,
  `prediction_source_type_key` varchar(50) DEFAULT NULL,
  `prediction_source_id` bigint(20) DEFAULT NULL,
  `market_id` int(11) NOT NULL,
  `selected_outcome_key` varchar(100) NOT NULL,
  `selected_parameter_value` varchar(100) DEFAULT NULL,
  `odds_at_bet_time` decimal(8,3) NOT NULL,
  `calculated_value_metric` decimal(10,4) DEFAULT NULL,
  `kelly_fraction_calculated` decimal(7,6) DEFAULT NULL,
  `stake_amount_simulated` decimal(10,2) NOT NULL,
  `actual_match_result_outcome_key` varchar(100) DEFAULT NULL,
  `bet_status_key` varchar(50) NOT NULL DEFAULT 'pending_result',
  `profit_loss_simulated` decimal(10,2) DEFAULT NULL,
  `evaluation_run_id` varchar(100) DEFAULT NULL,
  `evaluation_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`evaluation_log_id`),
  KEY `idx_strategy_eval_match_status` (`strategy_id`,`match_id`,`bet_status_key`),
  KEY `idx_strategy_eval_run_id` (`evaluation_run_id`),
  KEY `fk_extstrategyeval_match` (`match_id`),
  KEY `fk_extstrategyeval_market` (`market_id`),
  CONSTRAINT `fk_extstrategyeval_market` FOREIGN KEY (`market_id`) REFERENCES `betting_markets` (`market_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extstrategyeval_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extstrategyeval_strategy` FOREIGN KEY (`strategy_id`) REFERENCES `ext_betting_strategies` (`strategy_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог результатов применения стратегий ставок';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_team_form_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_team_form_history` (
  `team_form_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `club_id` int(10) unsigned NOT NULL,
  `season_id` int(11) DEFAULT NULL,
  `reference_match_id` bigint(20) DEFAULT NULL,
  `reference_date` date NOT NULL,
  `matches_considered_count` smallint(6) NOT NULL DEFAULT 5,
  `scope_key` varchar(50) DEFAULT 'all_competitions',
  `wins` smallint(6) DEFAULT 0,
  `draws` smallint(6) DEFAULT 0,
  `losses` smallint(6) DEFAULT 0,
  `goals_scored` smallint(6) DEFAULT 0,
  `goals_conceded` smallint(6) DEFAULT 0,
  `avg_goals_scored_per_match` decimal(5,2) DEFAULT NULL,
  `avg_goals_conceded_per_match` decimal(5,2) DEFAULT NULL,
  `clean_sheets` smallint(6) DEFAULT 0,
  `failed_to_score` smallint(6) DEFAULT 0,
  `form_sequence_keys` varchar(50) DEFAULT NULL,
  `form_points` int(11) DEFAULT NULL,
  `form_score_calculated` decimal(7,4) DEFAULT NULL,
  `source_calculation_method_key` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`team_form_id`),
  UNIQUE KEY `idx_club_refmatch_scope_considered` (`club_id`,`reference_match_id`,`scope_key`,`matches_considered_count`),
  UNIQUE KEY `idx_club_refdate_scope_considered` (`club_id`,`reference_date`,`scope_key`,`matches_considered_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История формы команды/клуба';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_user_content_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_user_content_reports` (
  `report_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `reporting_user_id` int(11) NOT NULL,
  `reported_content_type_key` varchar(50) NOT NULL,
  `reported_content_id` bigint(20) NOT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `reason_key` varchar(100) NOT NULL,
  `report_comment` text DEFAULT NULL,
  `status_key` varchar(50) DEFAULT 'pending_review',
  `moderator_user_id` int(11) DEFAULT NULL,
  `moderation_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`report_id`),
  KEY `idx_report_status_created` (`status_key`,`created_at`),
  KEY `fk_extuserreports_reporter` (`reporting_user_id`),
  KEY `fk_extuserreports_reporteduser` (`reported_user_id`),
  KEY `fk_extuserreports_moderator` (`moderator_user_id`),
  CONSTRAINT `fk_extuserreports_moderator` FOREIGN KEY (`moderator_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserreports_reporteduser` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserreports_reporter` FOREIGN KEY (`reporting_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Жалобы пользователей на контент';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_user_insight_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_user_insight_votes` (
  `vote_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `insight_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` enum('upvote','downvote') NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`vote_id`),
  UNIQUE KEY `idx_insight_user_vote` (`insight_id`,`user_id`),
  KEY `fk_extuserinsightvotes_user` (`user_id`),
  CONSTRAINT `fk_extuserinsightvotes_insight` FOREIGN KEY (`insight_id`) REFERENCES `ext_user_insights` (`insight_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extuserinsightvotes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Голоса пользователей за инсайды';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_user_insights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_user_insights` (
  `insight_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `match_id` bigint(20) DEFAULT NULL,
  `club_id` int(10) unsigned DEFAULT NULL,
  `player_id` int(10) unsigned DEFAULT NULL,
  `league_id` int(11) DEFAULT NULL,
  `insight_type_key` varchar(100) NOT NULL,
  `insight_text_ru` text DEFAULT NULL,
  `insight_text_en` text DEFAULT NULL,
  `source_description_ru` text DEFAULT NULL,
  `source_description_en` text DEFAULT NULL,
  `reliability_score_user` tinyint(3) unsigned DEFAULT NULL,
  `status_key` varchar(50) DEFAULT 'pending_review',
  `moderator_user_id` int(11) DEFAULT NULL,
  `moderation_comment` text DEFAULT NULL,
  `upvotes` int(11) DEFAULT 0,
  `downvotes` int(11) DEFAULT 0,
  `net_votes` int(11) GENERATED ALWAYS AS (`upvotes` - `downvotes`) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`insight_id`),
  KEY `idx_insight_status_type_created` (`status_key`,`insight_type_key`,`created_at`),
  KEY `idx_insight_match_user` (`match_id`,`user_id`),
  KEY `idx_insight_created_votes` (`created_at`,`net_votes`),
  KEY `fk_extuserinsights_user` (`user_id`),
  KEY `fk_extuserinsights_club` (`club_id`),
  KEY `fk_extuserinsights_player` (`player_id`),
  KEY `fk_extuserinsights_league` (`league_id`),
  KEY `fk_extuserinsights_moderator` (`moderator_user_id`),
  CONSTRAINT `fk_extuserinsights_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserinsights_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserinsights_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserinsights_moderator` FOREIGN KEY (`moderator_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserinsights_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserinsights_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Инсайдерская информация и мнения от пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_user_payments_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_user_payments_purchases` (
  `payment_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `subscription_id_for_renewal` bigint(20) DEFAULT NULL COMMENT 'Если это платеж за продление подписки',
  `payment_type_key` varchar(50) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `currency_code` char(3) NOT NULL,
  `payment_gateway_name_key` varchar(100) NOT NULL,
  `payment_gateway_transaction_id` varchar(255) NOT NULL,
  `payment_status_key` varchar(50) DEFAULT 'completed',
  `related_entity_type_key` varchar(50) DEFAULT NULL,
  `related_entity_id` bigint(20) DEFAULT NULL,
  `payment_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `idx_payment_gateway_trans_id_unique` (`payment_gateway_name_key`,`payment_gateway_transaction_id`),
  KEY `idx_payment_user_status_time` (`user_id`,`payment_status_key`,`payment_timestamp`),
  KEY `fk_extuserpayments_product` (`product_id`),
  KEY `fk_extuserpayments_sub` (`subscription_id_for_renewal`),
  CONSTRAINT `fk_extuserpayments_product` FOREIGN KEY (`product_id`) REFERENCES `ext_products_services` (`product_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserpayments_sub` FOREIGN KEY (`subscription_id_for_renewal`) REFERENCES `ext_user_subscriptions` (`subscription_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extuserpayments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Платежи и разовые покупки пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_user_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_user_predictions` (
  `user_prediction_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `match_id` bigint(20) NOT NULL,
  `market_id` int(11) NOT NULL,
  `predicted_outcome_key` varchar(100) NOT NULL,
  `predicted_parameter_value` varchar(100) DEFAULT NULL,
  `confidence_level` tinyint(4) DEFAULT NULL,
  `prediction_comment` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_awarded` decimal(7,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_prediction_id`),
  UNIQUE KEY `idx_user_match_market_prediction` (`user_id`,`match_id`,`market_id`),
  KEY `fk_extuserpred_match` (`match_id`),
  KEY `fk_extuserpred_market` (`market_id`),
  CONSTRAINT `fk_extuserpred_market` FOREIGN KEY (`market_id`) REFERENCES `betting_markets` (`market_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extuserpred_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`match_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extuserpred_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Прогнозы, сделанные пользователями';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ext_user_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ext_user_subscriptions` (
  `subscription_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status_key` varchar(50) DEFAULT 'active',
  `auto_renew` tinyint(1) DEFAULT 0,
  `payment_gateway_subscription_id` varchar(255) DEFAULT NULL,
  `last_payment_id` bigint(20) DEFAULT NULL COMMENT 'FK на ext_user_payments_purchases',
  `cancellation_reason_ru` text DEFAULT NULL,
  `cancellation_reason_en` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`subscription_id`),
  KEY `idx_sub_user_status_end_date` (`user_id`,`status_key`,`end_date`),
  KEY `fk_extusersub_product` (`product_id`),
  KEY `fk_extusersub_lastpayment` (`last_payment_id`),
  CONSTRAINT `fk_extusersub_lastpayment` FOREIGN KEY (`last_payment_id`) REFERENCES `ext_user_payments_purchases` (`payment_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_extusersub_product` FOREIGN KEY (`product_id`) REFERENCES `ext_products_services` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_extusersub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Подписки пользователей на платные услуги';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `external_api_integration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_api_integration_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `integration_name_key` varchar(100) NOT NULL,
  `request_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_url` text DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_headers_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_headers_json`)),
  `request_body_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_body_json`)),
  `response_timestamp` timestamp NULL DEFAULT NULL,
  `response_status_code` int(11) DEFAULT NULL,
  `response_headers_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_headers_json`)),
  `response_body_raw` mediumtext DEFAULT NULL,
  `is_success` tinyint(1) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `related_entity_type_key` varchar(50) DEFAULT NULL,
  `related_entity_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_integration_name_time` (`integration_name_key`,`request_timestamp`),
  KEY `idx_integration_related_entity` (`related_entity_type_key`,`related_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог взаимодействия с внешними API';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `league_standings_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `league_standings_history` (
  `standing_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `club_id` int(10) unsigned NOT NULL,
  `recorded_date` date NOT NULL,
  `position` smallint(6) NOT NULL,
  `matches_played` smallint(6) DEFAULT 0,
  `wins` smallint(6) DEFAULT 0,
  `draws` smallint(6) DEFAULT 0,
  `losses` smallint(6) DEFAULT 0,
  `goals_for` smallint(6) DEFAULT 0,
  `goals_against` smallint(6) DEFAULT 0,
  `goal_difference` smallint(6) GENERATED ALWAYS AS (`goals_for` - `goals_against`) STORED,
  `points` smallint(6) DEFAULT 0,
  `form_last_n_matches_keys` varchar(50) DEFAULT NULL,
  `home_stats_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`home_stats_json`)),
  `away_stats_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`away_stats_json`)),
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`standing_id`),
  UNIQUE KEY `idx_season_club_date` (`season_id`,`club_id`,`recorded_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История турнирных таблиц для лиг (футбол)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leagues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `leagues` (
  `league_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `country_id` smallint(5) unsigned DEFAULT NULL,
  `name_en` varchar(255) NOT NULL,
  `name_ru` varchar(255) NOT NULL,
  `short_name_en` varchar(100) DEFAULT NULL,
  `short_name_ru` varchar(100) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `level` tinyint(4) DEFAULT NULL,
  `type_key` varchar(50) DEFAULT NULL COMMENT 'Ключ типа турнира (league, cup, grand_slam)',
  `surface_key` varchar(50) DEFAULT NULL COMMENT 'Ключ покрытия (hard, clay, grass)',
  `source_league_id` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `slug_ru` varchar(300) DEFAULT NULL,
  `slug_en` varchar(300) DEFAULT NULL,
  `meta_title_ru` varchar(300) DEFAULT NULL,
  `meta_title_en` varchar(300) DEFAULT NULL,
  `meta_description_ru` text DEFAULT NULL,
  `meta_description_en` text DEFAULT NULL,
  `meta_keywords_ru` text DEFAULT NULL,
  `meta_keywords_en` text DEFAULT NULL,
  PRIMARY KEY (`league_id`),
  KEY `idx_league_sport_country_name_en` (`sport_id`,`country_id`,`name_en`(191)),
  KEY `fk_leagues_country` (`country_id`),
  KEY `fk_leagues_source` (`source_id`),
  CONSTRAINT `fk_leagues_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_leagues_source` FOREIGN KEY (`source_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_leagues_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лиги и турниры';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `match_lineups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_lineups` (
  `lineup_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `club_id` int(10) unsigned NOT NULL COMMENT 'Клуб/команда (FK на clubs)',
  `player_id` int(10) unsigned NOT NULL,
  `is_starting` tinyint(1) DEFAULT 0,
  `is_captain` tinyint(1) DEFAULT 0,
  `jersey_number` tinyint(3) unsigned DEFAULT NULL,
  `position_key` varchar(50) DEFAULT NULL,
  `position_name_ru` varchar(100) DEFAULT NULL,
  `position_name_en` varchar(100) DEFAULT NULL,
  `formation_place` tinyint(4) DEFAULT NULL,
  `time_substituted_on_minute` smallint(6) DEFAULT NULL,
  `time_substituted_off_minute` smallint(6) DEFAULT NULL,
  `reason_for_substitution_key` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`lineup_id`),
  UNIQUE KEY `idx_match_club_player` (`match_id`,`club_id`,`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Составы команд на матч';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `match_player_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_player_stats` (
  `match_player_stat_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `club_id` int(10) unsigned DEFAULT NULL,
  `stat_type_id` int(11) NOT NULL,
  `stat_value_numeric` decimal(12,4) DEFAULT NULL,
  `stat_value_text` varchar(255) DEFAULT NULL,
  `period_key` varchar(50) DEFAULT 'FULL_TIME',
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`match_player_stat_id`),
  UNIQUE KEY `idx_match_player_stat_period` (`match_id`,`player_id`,`stat_type_id`,`period_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Статистика игроков по матчам';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `match_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_results` (
  `result_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `home_score_ft` smallint(6) DEFAULT NULL,
  `away_score_ft` smallint(6) DEFAULT NULL,
  `home_score_ht` smallint(6) DEFAULT NULL,
  `away_score_ht` smallint(6) DEFAULT NULL,
  `home_score_ot` smallint(6) DEFAULT NULL,
  `away_score_ot` smallint(6) DEFAULT NULL,
  `home_score_penalties` smallint(6) DEFAULT NULL,
  `away_score_penalties` smallint(6) DEFAULT NULL,
  `set_scores_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Для тенниса: {"set1": {"home": 6, "away": 4}, ...}' CHECK (json_valid(`set_scores_json`)),
  `winner_club_id` int(10) unsigned DEFAULT NULL,
  `winner_player_id` int(10) unsigned DEFAULT NULL,
  `ft_outcome_key` varchar(20) DEFAULT NULL COMMENT 'Ключ исхода (HOME_WIN, AWAY_WIN, DRAW)',
  `result_status_key` varchar(50) DEFAULT 'pending_confirmation',
  `source_id` int(11) DEFAULT NULL,
  `notes_ru` text DEFAULT NULL,
  `notes_en` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`result_id`),
  UNIQUE KEY `match_id_unique_result` (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Фактические результаты матчей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `match_team_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_team_stats` (
  `match_team_stat_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `club_id` int(10) unsigned NOT NULL,
  `stat_type_id` int(11) NOT NULL,
  `stat_value_numeric` decimal(12,4) DEFAULT NULL,
  `stat_value_text` varchar(255) DEFAULT NULL,
  `period_key` varchar(50) DEFAULT 'FULL_TIME',
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`match_team_stat_id`),
  UNIQUE KEY `idx_match_club_stat_period` (`match_id`,`club_id`,`stat_type_id`,`period_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Статистика клубов по матчам';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `match_weather_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_weather_conditions` (
  `weather_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `temperature_celsius` decimal(4,1) DEFAULT NULL,
  `feels_like_celsius` decimal(4,1) DEFAULT NULL,
  `humidity_percentage` tinyint(3) unsigned DEFAULT NULL,
  `wind_speed_kmh` decimal(5,1) DEFAULT NULL,
  `wind_direction_degrees` smallint(5) unsigned DEFAULT NULL,
  `wind_gust_kmh` decimal(5,1) DEFAULT NULL,
  `precipitation_mm_last_hour` decimal(5,2) DEFAULT NULL,
  `cloud_cover_percentage` tinyint(3) unsigned DEFAULT NULL,
  `visibility_km` decimal(4,1) DEFAULT NULL,
  `pressure_hpa` smallint(5) unsigned DEFAULT NULL,
  `condition_code` varchar(50) DEFAULT NULL,
  `condition_description_ru` varchar(255) DEFAULT NULL,
  `condition_description_en` varchar(255) DEFAULT NULL,
  `sunrise_utc` time DEFAULT NULL,
  `sunset_utc` time DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`weather_id`),
  UNIQUE KEY `match_id_unique_weather` (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Погодные условия во время матча';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `matches` (
  `match_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `league_id` int(11) NOT NULL,
  `season_id` int(11) DEFAULT NULL,
  `round_name_ru` varchar(100) DEFAULT NULL,
  `round_name_en` varchar(100) DEFAULT NULL,
  `stage_key` varchar(50) DEFAULT NULL COMMENT 'Ключ стадии турнира (group, etc.)',
  `group_name` varchar(100) DEFAULT NULL,
  `playoff_round` tinyint(4) DEFAULT NULL,
  `match_datetime_utc` datetime NOT NULL,
  `match_status_key` varchar(50) DEFAULT 'scheduled',
  `home_club_id` int(10) unsigned DEFAULT NULL,
  `away_club_id` int(10) unsigned DEFAULT NULL,
  `home_player_id` int(10) unsigned DEFAULT NULL,
  `away_player_id` int(10) unsigned DEFAULT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `referee_id` int(11) DEFAULT NULL,
  `attendance` int(11) DEFAULT NULL,
  `weather_conditions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON погодных условий матча' CHECK (json_valid(`weather_conditions_json`)),
  `primary_source_id` int(11) DEFAULT NULL COMMENT 'Основной источник данных (FK на data_sources)',
  `source_match_id` varchar(100) DEFAULT NULL COMMENT 'ID матча у внешнего источника',
  `parser_input_id` bigint(20) DEFAULT NULL COMMENT 'FK на raw_text_inputs',
  `motivation_home_key` varchar(50) DEFAULT NULL,
  `motivation_away_key` varchar(50) DEFAULT NULL,
  `slug_ru` varchar(300) DEFAULT NULL,
  `slug_en` varchar(300) DEFAULT NULL,
  `meta_title_ru` varchar(350) DEFAULT NULL,
  `meta_title_en` varchar(350) DEFAULT NULL,
  `meta_description_ru` text DEFAULT NULL,
  `meta_description_en` text DEFAULT NULL,
  `meta_keywords_ru` text DEFAULT NULL,
  `meta_keywords_en` text DEFAULT NULL,
  `preview_text_ru` text DEFAULT NULL,
  `preview_text_en` text DEFAULT NULL,
  `full_analysis_access_level_key` varchar(50) DEFAULT 'premium' COMMENT 'Ключ уровня доступа к полному анализу (free, premium, registered_only)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`match_id`),
  UNIQUE KEY `idx_match_slug_ru` (`slug_ru`),
  UNIQUE KEY `idx_match_slug_en` (`slug_en`),
  KEY `idx_match_datetime_utc` (`match_datetime_utc`),
  KEY `idx_match_source_id` (`source_match_id`,`primary_source_id`),
  KEY `fk_matches_sport` (`sport_id`),
  KEY `fk_matches_league` (`league_id`),
  KEY `fk_matches_season` (`season_id`),
  KEY `fk_matches_home_club` (`home_club_id`),
  KEY `fk_matches_away_club` (`away_club_id`),
  KEY `fk_matches_home_player` (`home_player_id`),
  KEY `fk_matches_away_player` (`away_player_id`),
  KEY `fk_matches_venue` (`venue_id`),
  KEY `fk_matches_referee` (`referee_id`),
  KEY `fk_matches_primary_source` (`primary_source_id`),
  KEY `fk_matches_parser_input` (`parser_input_id`),
  CONSTRAINT `fk_matches_away_club` FOREIGN KEY (`away_club_id`) REFERENCES `clubs` (`club_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_away_player` FOREIGN KEY (`away_player_id`) REFERENCES `players` (`player_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_home_club` FOREIGN KEY (`home_club_id`) REFERENCES `clubs` (`club_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_home_player` FOREIGN KEY (`home_player_id`) REFERENCES `players` (`player_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`),
  CONSTRAINT `fk_matches_parser_input` FOREIGN KEY (`parser_input_id`) REFERENCES `raw_text_inputs` (`input_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_primary_source` FOREIGN KEY (`primary_source_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_referee` FOREIGN KEY (`referee_id`) REFERENCES `referees` (`referee_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_season` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`season_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`),
  CONSTRAINT `fk_matches_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Матчи и события';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_assets` (
  `media_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `entity_type_key` varchar(50) NOT NULL COMMENT 'Ключ типа сущности, к которой относится медиа (club, player, article)',
  `entity_id` bigint(20) NOT NULL COMMENT 'ID сущности',
  `media_type_key` varchar(50) NOT NULL COMMENT 'Ключ типа медиа (logo, photo_portrait, featured_image, event_video_preview)',
  `file_path_or_url` varchar(1000) NOT NULL,
  `storage_type_key` varchar(20) DEFAULT 'url' COMMENT 'local_path, s3_bucket, external_url',
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size_bytes` bigint(20) DEFAULT NULL,
  `image_width_px` int(11) DEFAULT NULL,
  `image_height_px` int(11) DEFAULT NULL,
  `alt_text_ru` varchar(255) DEFAULT NULL,
  `alt_text_en` varchar(255) DEFAULT NULL,
  `caption_ru` text DEFAULT NULL,
  `caption_en` text DEFAULT NULL,
  `uploader_user_id` int(11) DEFAULT NULL,
  `is_primary_for_entity_type` tinyint(1) DEFAULT 0 COMMENT 'Является ли основным изображением для этой сущности и типа медиа',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`media_id`),
  KEY `idx_media_entity_type_primary` (`entity_type_key`,`entity_id`,`media_type_key`,`is_primary_for_entity_type`),
  KEY `fk_media_uploader` (`uploader_user_id`),
  CONSTRAINT `fk_media_uploader` FOREIGN KEY (`uploader_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Централизованное хранилище медиа-ассетов';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `migration_id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL COMMENT 'Имя файла миграции или уникальный идентификатор',
  `batch` int(11) NOT NULL COMMENT 'Номер пакета миграций',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`migration_id`),
  UNIQUE KEY `migration_name` (`migration_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История миграций схемы БД (для Phinx или аналога)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `odds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `odds` (
  `odds_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `market_id` int(11) NOT NULL,
  `bookmaker_id` int(11) NOT NULL,
  `outcome_key` varchar(50) NOT NULL,
  `market_parameter_value` varchar(50) DEFAULT NULL,
  `odds_value` decimal(8,3) NOT NULL,
  `last_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`odds_id`),
  UNIQUE KEY `idx_match_market_book_outcome_param` (`match_id`,`market_id`,`bookmaker_id`,`outcome_key`,`market_parameter_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Текущие коэффициенты на исходы матчей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `odds_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `odds_history` (
  `odds_history_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) NOT NULL,
  `market_id` int(11) NOT NULL,
  `bookmaker_id` int(11) NOT NULL,
  `outcome_key` varchar(50) NOT NULL,
  `market_parameter_value` varchar(50) DEFAULT NULL,
  `odds_value` decimal(8,3) NOT NULL,
  `odds_value_old` decimal(8,3) DEFAULT NULL,
  `change_timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`odds_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История изменений коэффициентов';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `player_ranking_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `player_ranking_history` (
  `ranking_history_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `ranking_date` date NOT NULL,
  `ranking_type_key` varchar(50) NOT NULL,
  `rank_position` int(11) NOT NULL,
  `rank_points` int(11) DEFAULT NULL,
  `movement_from_previous_period` int(11) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ranking_history_id`),
  UNIQUE KEY `idx_player_date_type_ranking` (`player_id`,`ranking_date`,`ranking_type_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История рейтинга теннисистов';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `players` (
  `player_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name_en` varchar(255) NOT NULL,
  `full_name_ru` varchar(255) DEFAULT NULL,
  `short_name_en` varchar(100) DEFAULT NULL,
  `short_name_ru` varchar(100) DEFAULT NULL,
  `first_name_en` varchar(100) DEFAULT NULL,
  `last_name_en` varchar(100) DEFAULT NULL,
  `first_name_ru` varchar(100) DEFAULT NULL,
  `last_name_ru` varchar(100) DEFAULT NULL,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `nationality_country_id` smallint(5) unsigned NOT NULL,
  `birth_country_id` smallint(5) unsigned DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `height_cm` smallint(5) unsigned DEFAULT NULL,
  `weight_kg` smallint(5) unsigned DEFAULT NULL,
  `plays_hand_key` varchar(20) DEFAULT NULL COMMENT 'Для тенниса/др. (right, left, ambidextrous)',
  `backhand_type_key` varchar(20) DEFAULT NULL COMMENT 'Для тенниса (one_handed, two_handed)',
  `current_club_id` int(10) unsigned DEFAULT NULL,
  `primary_position_key` varchar(50) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `bio_ru` text DEFAULT NULL,
  `bio_en` text DEFAULT NULL,
  `slug_ru` varchar(300) DEFAULT NULL,
  `slug_en` varchar(300) DEFAULT NULL,
  `meta_title_ru` varchar(300) DEFAULT NULL,
  `meta_title_en` varchar(300) DEFAULT NULL,
  `meta_description_ru` text DEFAULT NULL,
  `meta_description_en` text DEFAULT NULL,
  `meta_keywords_ru` text DEFAULT NULL,
  `meta_keywords_en` text DEFAULT NULL,
  `source_player_id` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`player_id`),
  UNIQUE KEY `idx_player_slug_ru` (`slug_ru`),
  UNIQUE KEY `idx_player_slug_en` (`slug_en`),
  KEY `idx_player_fullname_en_dob` (`full_name_en`,`date_of_birth`),
  KEY `fk_players_sport` (`sport_id`),
  KEY `fk_players_nationality` (`nationality_country_id`),
  KEY `fk_players_birth_country` (`birth_country_id`),
  KEY `fk_players_club` (`current_club_id`),
  KEY `fk_players_source` (`source_id`),
  CONSTRAINT `fk_players_birth_country` FOREIGN KEY (`birth_country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_players_club` FOREIGN KEY (`current_club_id`) REFERENCES `clubs` (`club_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_players_nationality` FOREIGN KEY (`nationality_country_id`) REFERENCES `countries` (`country_id`),
  CONSTRAINT `fk_players_source` FOREIGN KEY (`source_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_players_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Игроки (футбол, теннис и др.)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `raw_text_inputs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `raw_text_inputs` (
  `input_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `source_id` int(11) NOT NULL,
  `raw_text` mediumtext NOT NULL,
  `processing_status_key` varchar(50) DEFAULT 'pending' COMMENT 'Ключ статуса (pending, processing, processed_ok, processed_partial, error_parsing, error_saving)',
  `error_message` text DEFAULT NULL,
  `parsed_league_name` varchar(255) DEFAULT NULL,
  `matches_found_count` int(11) DEFAULT 0,
  `matches_saved_count` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`input_id`),
  KEY `fk_rawtext_user` (`user_id`),
  KEY `fk_rawtext_sport` (`sport_id`),
  KEY `fk_rawtext_source` (`source_id`),
  CONSTRAINT `fk_rawtext_source` FOREIGN KEY (`source_id`) REFERENCES `data_sources` (`source_id`),
  CONSTRAINT `fk_rawtext_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`),
  CONSTRAINT `fk_rawtext_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Хранение сырых текстовых данных для парсинга';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `referees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `referees` (
  `referee_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name_en` varchar(255) NOT NULL,
  `full_name_ru` varchar(255) DEFAULT NULL,
  `country_id` smallint(5) unsigned DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `source_referee_id` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`referee_id`),
  UNIQUE KEY `idx_referee_fullname_en_dob` (`full_name_en`,`date_of_birth`),
  KEY `fk_referees_country` (`country_id`),
  KEY `fk_referees_source` (`source_id`),
  CONSTRAINT `fk_referees_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_referees_source` FOREIGN KEY (`source_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Судьи матчей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasons` (
  `season_id` int(11) NOT NULL AUTO_INCREMENT,
  `league_id` int(11) NOT NULL,
  `season_name` varchar(100) NOT NULL COMMENT 'Название сезона (напр. 2023/2024, 2024)',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current_season` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`season_id`),
  UNIQUE KEY `idx_league_season_name` (`league_id`,`season_name`),
  CONSTRAINT `fk_seasons_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Сезоны для лиг/турниров';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seo_url_redirects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seo_url_redirects` (
  `redirect_id` int(11) NOT NULL AUTO_INCREMENT,
  `old_url_path` varchar(1000) NOT NULL,
  `new_url_path` varchar(1000) NOT NULL,
  `redirect_type_code` smallint(6) DEFAULT 301,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`redirect_id`),
  UNIQUE KEY `idx_redirect_old_url_unique` (`old_url_path`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Управление SEO редиректами';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports` (
  `sport_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(50) NOT NULL,
  `name_ru` varchar(50) NOT NULL,
  `sport_key` varchar(20) NOT NULL COMMENT 'Ключ вида спорта (football, tennis)',
  `logo_url` varchar(255) DEFAULT NULL COMMENT 'URL логотипа вида спорта',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`sport_id`),
  UNIQUE KEY `sport_key` (`sport_key`),
  UNIQUE KEY `idx_sports_name_en` (`name_en`),
  UNIQUE KEY `idx_sports_name_ru` (`name_ru`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник видов спорта';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stat_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stat_types` (
  `stat_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `sport_id` tinyint(3) unsigned NOT NULL,
  `stat_key` varchar(100) NOT NULL COMMENT 'Уникальный ключ типа статистики (например, goals, shots_on_target, aces)',
  `stat_name_ru` varchar(255) NOT NULL,
  `stat_name_en` varchar(255) NOT NULL,
  `stat_category_key` varchar(100) DEFAULT NULL COMMENT 'Категория статы (например, attacking, defensive, serving)',
  `value_type` enum('numeric','text','boolean','percentage') NOT NULL DEFAULT 'numeric',
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`stat_type_id`),
  UNIQUE KEY `idx_sport_stat_key` (`sport_id`,`stat_key`),
  CONSTRAINT `fk_stattypes_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник типов спортивной статистики';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_app_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_app_logs` (
  `app_log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_level_key` varchar(20) NOT NULL DEFAULT 'info' COMMENT 'debug, info, warning, error, critical',
  `channel_key` varchar(100) DEFAULT 'general' COMMENT 'Канал логирования (e.g., user_auth, payment, etl_football)',
  `message` text NOT NULL,
  `context_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`app_log_id`),
  KEY `idx_applog_level_channel_time` (`log_level_key`,`channel_key`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Общие логи приложения и критичных системных событий';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_scheduled_tasks_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_scheduled_tasks_log` (
  `task_log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task_key` varchar(255) NOT NULL,
  `status_key` varchar(50) NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `output_summary_ru` text DEFAULT NULL,
  `output_summary_en` text DEFAULT NULL,
  `log_details_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`log_details_json`)),
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`task_log_id`),
  KEY `idx_tasklog_key_status_start` (`task_key`(191),`status_key`,`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог выполнения запланированных системных задач';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value_text` text DEFAULT NULL,
  `setting_value_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`setting_value_json`)),
  `setting_type_key` varchar(50) DEFAULT 'string',
  `description_ru` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `is_editable_from_ui` tinyint(1) DEFAULT 1,
  `last_updated_by_user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `fk_syssettings_user_ddl` (`last_updated_by_user_id`),
  CONSTRAINT `fk_syssettings_user` FOREIGN KEY (`last_updated_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Системные настройки платформы';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_achievements` (
  `user_achievement_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `achievement_key` varchar(100) NOT NULL,
  `date_earned` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_achievement_id`),
  UNIQUE KEY `uniq_user_achievement` (`user_id`,`achievement_key`),
  KEY `idx_user_ach_user` (`user_id`),
  KEY `idx_user_ach_achievement` (`achievement_key`),
  CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_key`) REFERENCES `achievement_types` (`achievement_key`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог полученных пользователями достижений';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `role_key` varchar(50) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ru` varchar(100) NOT NULL,
  `description_en` varchar(255) DEFAULT NULL,
  `description_ru` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Роли пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_key` varchar(50) DEFAULT 'user' COMMENT 'Роли: user, admin, moderator, etc.',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login_date` timestamp NULL DEFAULT NULL,
  `referrer_user_id` int(11) DEFAULT NULL COMMENT 'FK на users(user_id)',
  `verification_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_referrer` (`referrer_user_id`),
  CONSTRAINT `fk_users_referrer` FOREIGN KEY (`referrer_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Пользователи системы PrognozAi (включая админов)';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `venues` (
  `venue_id` int(11) NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) NOT NULL,
  `name_ru` varchar(255) NOT NULL,
  `city_en` varchar(100) DEFAULT NULL,
  `city_ru` varchar(100) DEFAULT NULL,
  `country_id` smallint(5) unsigned DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `surface_key` varchar(50) DEFAULT NULL COMMENT 'Ключ покрытия поля/корта (grass, artificial_turf, hard, clay)',
  `is_indoor` tinyint(1) DEFAULT 0,
  `roof_type_key` varchar(50) DEFAULT 'open' COMMENT 'Ключ типа крыши (open, retractable, fixed)',
  `year_opened` smallint(6) DEFAULT NULL,
  `pitch_length_m` smallint(6) DEFAULT NULL,
  `pitch_width_m` smallint(6) DEFAULT NULL,
  `geo_latitude` decimal(10,7) DEFAULT NULL,
  `geo_longitude` decimal(10,7) DEFAULT NULL,
  `altitude_m` smallint(6) DEFAULT NULL,
  `venue_photo_url` varchar(255) DEFAULT NULL,
  `source_venue_id` varchar(100) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`venue_id`),
  KEY `fk_venues_country` (`country_id`),
  KEY `fk_venues_source` (`source_id`),
  CONSTRAINT `fk_venues_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_venues_source` FOREIGN KEY (`source_id`) REFERENCES `data_sources` (`source_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Стадионы и места проведения матчей';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

