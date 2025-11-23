-- FILE: /database.sql
-- AI Video Generator - Complete Database Schema
-- MySQL 5.7+ / 8.0+ Compatible
-- Character Set: utf8mb4
-- Collation: utf8mb4_unicode_ci

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `ai_video_generator` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ai_video_generator`;

-- ========================================
-- TENANTS TABLE
-- ========================================

CREATE TABLE `tenants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `timezone` VARCHAR(50) DEFAULT 'UTC',
  `status` ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- USERS TABLE
-- ========================================

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'editor', 'viewer') DEFAULT 'editor',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `tenant_id` (`tenant_id`),
  KEY `role` (`role`),
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- PLANS TABLE
-- ========================================

CREATE TABLE `plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price_monthly` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `price_yearly` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `max_projects` INT DEFAULT -1 COMMENT '-1 = unlimited',
  `max_renders_per_month` INT DEFAULT -1,
  `max_render_minutes_per_month` INT DEFAULT -1,
  `max_storage_mb` INT DEFAULT -1,
  `max_team_members` INT DEFAULT -1,
  `features` TEXT COMMENT 'JSON encoded features',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TENANT SUBSCRIPTIONS TABLE
-- ========================================

CREATE TABLE `tenant_subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `status` ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'trial',
  `billing_cycle` ENUM('monthly', 'yearly') DEFAULT 'monthly',
  `current_period_start` DATE NOT NULL,
  `current_period_end` DATE NOT NULL,
  `trial_ends_at` DATE DEFAULT NULL,
  `cancelled_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_subscriptions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_subscriptions_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- USAGE TRACKING TABLE
-- ========================================

CREATE TABLE `usage_tracking` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `month` DATE NOT NULL,
  `projects_count` INT DEFAULT 0,
  `renders_count` INT DEFAULT 0,
  `render_minutes` INT DEFAULT 0,
  `storage_mb` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_month` (`tenant_id`, `month`),
  KEY `tenant_id` (`tenant_id`),
  CONSTRAINT `fk_usage_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INVOICES TABLE
-- ========================================

CREATE TABLE `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `tax` DECIMAL(10, 2) DEFAULT 0.00,
  `total` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `tenant_id` (`tenant_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_invoices_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- PAYMENTS TABLE
-- ========================================

CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_id` INT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'card',
  `transaction_id` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `metadata` TEXT COMMENT 'JSON encoded metadata',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_payments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- BRAND SETTINGS TABLE
-- ========================================

CREATE TABLE `brand_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `primary_color` VARCHAR(7) DEFAULT '#3B82F6',
  `secondary_color` VARCHAR(7) DEFAULT '#10B981',
  `default_font` VARCHAR(100) DEFAULT 'Arial',
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `watermark_url` VARCHAR(500) DEFAULT NULL,
  `intro_clip_url` VARCHAR(500) DEFAULT NULL,
  `outro_clip_url` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_id` (`tenant_id`),
  CONSTRAINT `fk_brand_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- PROJECTS TABLE
-- ========================================

CREATE TABLE `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `type` ENUM('text_to_video', 'image_to_video', 'video_to_video', 'images_to_video') NOT NULL,
  `platform_preset` VARCHAR(50) DEFAULT 'youtube_horizontal' COMMENT 'e.g., tiktok_vertical, youtube_horizontal, square_social',
  `aspect_ratio` VARCHAR(10) DEFAULT '16:9' COMMENT 'e.g., 16:9, 9:16, 1:1',
  `resolution` VARCHAR(20) DEFAULT '1080p' COMMENT 'e.g., 720p, 1080p, 4k',
  `status` ENUM('draft', 'ready', 'rendering', 'completed') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  CONSTRAINT `fk_projects_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SCENES TABLE
-- ========================================

CREATE TABLE `scenes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `project_id` INT UNSIGNED NOT NULL,
  `position` INT NOT NULL DEFAULT 0,
  `scene_type` ENUM('text_only', 'text_image', 'image_only', 'video_clip') NOT NULL,
  `duration` INT NOT NULL DEFAULT 5 COMMENT 'Duration in seconds',
  `prompt` TEXT COMMENT 'AI generation prompt or description',
  `text_content` TEXT COMMENT 'Text to display or narrate',
  `asset_id` INT UNSIGNED DEFAULT NULL COMMENT 'Associated image/video/audio asset',
  `style_preset_id` INT UNSIGNED DEFAULT NULL,
  `metadata` TEXT COMMENT 'JSON encoded metadata',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `project_id` (`project_id`),
  KEY `position` (`position`),
  CONSTRAINT `fk_scenes_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_scenes_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ASSETS TABLE
-- ========================================

CREATE TABLE `assets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` ENUM('image', 'video', 'audio') NOT NULL,
  `file_size` BIGINT NOT NULL COMMENT 'Size in bytes',
  `mime_type` VARCHAR(100) NOT NULL,
  `width` INT DEFAULT NULL,
  `height` INT DEFAULT NULL,
  `duration` INT DEFAULT NULL COMMENT 'Duration in seconds for video/audio',
  `tags` VARCHAR(500) DEFAULT NULL COMMENT 'Comma-separated tags',
  `metadata` TEXT COMMENT 'JSON encoded metadata',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `file_type` (`file_type`),
  CONSTRAINT `fk_assets_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TEMPLATES TABLE
-- ========================================

CREATE TABLE `templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = global template',
  `user_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(100) DEFAULT NULL COMMENT 'e.g., product_promo, tiktok_short, youtube_intro',
  `thumbnail_url` VARCHAR(500) DEFAULT NULL,
  `is_public` TINYINT(1) DEFAULT 0,
  `usage_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `category` (`category`),
  CONSTRAINT `fk_templates_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_templates_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TEMPLATE SCENES TABLE
-- ========================================

CREATE TABLE `template_scenes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` INT UNSIGNED NOT NULL,
  `position` INT NOT NULL DEFAULT 0,
  `scene_type` ENUM('text_only', 'text_image', 'image_only', 'video_clip') NOT NULL,
  `duration` INT NOT NULL DEFAULT 5,
  `prompt` TEXT,
  `text_content` TEXT,
  `metadata` TEXT COMMENT 'JSON encoded metadata',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  KEY `position` (`position`),
  CONSTRAINT `fk_template_scenes_template` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- STYLE PRESETS TABLE
-- ========================================

CREATE TABLE `style_presets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `color_mood` VARCHAR(100) DEFAULT NULL COMMENT 'e.g., vibrant, dark, pastel',
  `motion_style` VARCHAR(100) DEFAULT NULL COMMENT 'e.g., smooth, dynamic, static',
  `visual_style` VARCHAR(100) DEFAULT NULL COMMENT 'e.g., cinematic, cartoon, 3d, minimal',
  `metadata` TEXT COMMENT 'JSON encoded style parameters',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- RENDER JOBS TABLE
-- ========================================

CREATE TABLE `render_jobs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `project_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `job_type` ENUM('text_to_video', 'image_to_video', 'video_to_video', 'images_to_video') NOT NULL,
  `status` ENUM('queued', 'processing', 'completed', 'failed') DEFAULT 'queued',
  `progress` INT DEFAULT 0 COMMENT 'Progress percentage 0-100',
  `parameters` TEXT COMMENT 'JSON encoded job parameters',
  `error_message` TEXT,
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_render_jobs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_render_jobs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_render_jobs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- VIDEOS TABLE
-- ========================================

CREATE TABLE `videos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `project_id` INT UNSIGNED NOT NULL,
  `render_job_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` BIGINT NOT NULL,
  `duration` INT NOT NULL COMMENT 'Duration in seconds',
  `resolution` VARCHAR(20) NOT NULL,
  `thumbnail_url` VARCHAR(500) DEFAULT NULL,
  `public_token` VARCHAR(64) DEFAULT NULL COMMENT 'Token for public sharing',
  `view_count` INT DEFAULT 0,
  `download_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_token` (`public_token`),
  KEY `tenant_id` (`tenant_id`),
  KEY `project_id` (`project_id`),
  KEY `render_job_id` (`render_job_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_videos_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_videos_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_videos_render_job` FOREIGN KEY (`render_job_id`) REFERENCES `render_jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_videos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- API KEYS TABLE
-- ========================================

CREATE TABLE `api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `key_hash` VARCHAR(255) NOT NULL,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_hash` (`key_hash`),
  KEY `tenant_id` (`tenant_id`),
  CONSTRAINT `fk_api_keys_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- NOTIFICATIONS TABLE
-- ========================================

CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'e.g., render_completed, quota_warning, payment_success',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT,
  `metadata` TEXT COMMENT 'JSON encoded metadata',
  `is_read` TINYINT(1) DEFAULT 0,
  `sent_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `fk_notifications_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ANALYTICS EVENTS TABLE
-- ========================================

CREATE TABLE `analytics_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `event_type` VARCHAR(50) NOT NULL COMMENT 'e.g., project_created, render_completed, video_downloaded',
  `event_data` TEXT COMMENT 'JSON encoded event data',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_analytics_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_analytics_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- DEMO SEED DATA
-- ========================================

-- Insert Platform Admin User (tenant_id=1 will be created first)
INSERT INTO `tenants` (`id`, `name`, `slug`, `email`, `website`, `status`) VALUES
(1, 'Platform Administration', 'platform-admin', 'admin@aivideogen.com', 'https://aivideogen.com', 'active'),
(2, 'Personal Creator Studio', 'personal-creator', 'john@example.com', 'https://johncreates.com', 'active'),
(3, 'Digital Marketing Agency', 'digital-agency', 'contact@digitalagency.com', 'https://digitalagency.com', 'active');

-- Insert Users
INSERT INTO `users` (`tenant_id`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES
(1, 'admin@aivideogen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'platform_admin', 'active'),
(2, 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'tenant_admin', 'active'),
(2, 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Smith', 'editor', 'active'),
(3, 'mike@digitalagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Johnson', 'tenant_admin', 'active'),
(3, 'emma@digitalagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma', 'Williams', 'editor', 'active'),
(3, 'viewer@digitalagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tom', 'Viewer', 'viewer', 'active');

-- Password for all demo users: "password"

-- Insert Plans
INSERT INTO `plans` (`name`, `slug`, `description`, `price_monthly`, `price_yearly`, `max_projects`, `max_renders_per_month`, `max_render_minutes_per_month`, `max_storage_mb`, `max_team_members`, `is_active`, `sort_order`) VALUES
('Free', 'free', 'Perfect for trying out AI video generation', 0.00, 0.00, 3, 10, 30, 500, 1, 1, 1),
('Starter', 'starter', 'For individual creators and small projects', 29.00, 290.00, 10, 50, 150, 5120, 3, 1, 2),
('Professional', 'professional', 'For serious content creators and small teams', 99.00, 990.00, 50, 200, 600, 51200, 10, 1, 3),
('Agency', 'agency', 'For agencies and large teams with high volume needs', 299.00, 2990.00, -1, -1, -1, 512000, -1, 1, 4);

-- Insert Tenant Subscriptions
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `billing_cycle`, `current_period_start`, `current_period_end`) VALUES
(1, 4, 'active', 'yearly', '2025-01-01', '2026-01-01'),
(2, 2, 'active', 'monthly', '2025-11-01', '2025-12-01'),
(3, 3, 'active', 'monthly', '2025-11-01', '2025-12-01');

-- Insert Usage Tracking
INSERT INTO `usage_tracking` (`tenant_id`, `month`, `projects_count`, `renders_count`, `render_minutes`, `storage_mb`) VALUES
(2, '2025-11-01', 5, 23, 87, 1024),
(3, '2025-11-01', 12, 67, 245, 4096);

-- Insert Sample Invoices
INSERT INTO `invoices` (`tenant_id`, `invoice_number`, `amount`, `tax`, `total`, `status`, `due_date`, `paid_at`) VALUES
(2, 'INV-2025-001', 29.00, 2.90, 31.90, 'paid', '2025-11-15', '2025-11-10 14:23:00'),
(3, 'INV-2025-002', 99.00, 9.90, 108.90, 'paid', '2025-11-15', '2025-11-12 09:15:00'),
(2, 'INV-2025-003', 29.00, 2.90, 31.90, 'pending', '2025-12-15', NULL);

-- Insert Sample Payments
INSERT INTO `payments` (`tenant_id`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`) VALUES
(2, 1, 31.90, 'card', 'txn_1234567890abc', 'completed'),
(3, 2, 108.90, 'card', 'txn_0987654321xyz', 'completed');

-- Insert Brand Settings
INSERT INTO `brand_settings` (`tenant_id`, `primary_color`, `secondary_color`, `default_font`, `logo_url`, `watermark_url`) VALUES
(2, '#FF6B6B', '#4ECDC4', 'Montserrat', '/storage/uploads/logos/personal-creator-logo.png', '/storage/uploads/watermarks/personal-watermark.png'),
(3, '#2D3748', '#ED8936', 'Roboto', '/storage/uploads/logos/agency-logo.png', '/storage/uploads/watermarks/agency-watermark.png');

-- Insert Sample Projects
INSERT INTO `projects` (`tenant_id`, `user_id`, `name`, `description`, `type`, `platform_preset`, `aspect_ratio`, `resolution`, `status`) VALUES
(2, 2, 'Product Launch Video', 'Promotional video for new product launch', 'text_to_video', 'youtube_horizontal', '16:9', '1080p', 'completed'),
(2, 2, 'Instagram Reel - Travel', 'Quick travel montage for social media', 'images_to_video', 'tiktok_vertical', '9:16', '1080p', 'completed'),
(2, 3, 'Tutorial Series Intro', 'Intro sequence for tutorial videos', 'image_to_video', 'youtube_horizontal', '16:9', '1080p', 'draft'),
(3, 4, 'Client Campaign - Fitness Brand', 'Social media campaign for fitness client', 'text_to_video', 'square_social', '1:1', '1080p', 'rendering'),
(3, 5, 'Real Estate Showcase', 'Property showcase video', 'images_to_video', 'youtube_horizontal', '16:9', '1080p', 'completed'),
(3, 5, 'TikTok Ad Campaign', 'Short form ads for TikTok', 'video_to_video', 'tiktok_vertical', '9:16', '1080p', 'completed');

-- Insert Sample Assets
INSERT INTO `assets` (`tenant_id`, `user_id`, `name`, `filename`, `file_path`, `file_type`, `file_size`, `mime_type`, `width`, `height`, `tags`) VALUES
(2, 2, 'Product Photo 1', 'product_1.jpg', '/storage/uploads/images/product_1.jpg', 'image', 245760, 'image/jpeg', 1920, 1080, 'product,promo'),
(2, 2, 'Product Photo 2', 'product_2.jpg', '/storage/uploads/images/product_2.jpg', 'image', 198432, 'image/jpeg', 1920, 1080, 'product,promo'),
(2, 2, 'Travel Photo 1', 'travel_1.jpg', '/storage/uploads/images/travel_1.jpg', 'image', 512000, 'image/jpeg', 1920, 1080, 'travel,landscape'),
(2, 2, 'Travel Photo 2', 'travel_2.jpg', '/storage/uploads/images/travel_2.jpg', 'image', 478965, 'image/jpeg', 1920, 1080, 'travel,landscape'),
(2, 2, 'Background Music', 'upbeat.mp3', '/storage/uploads/audio/upbeat.mp3', 'audio', 3145728, 'audio/mpeg', NULL, NULL, 'music,upbeat'),
(3, 4, 'Fitness Model Photo', 'fitness_model.jpg', '/storage/uploads/images/fitness_model.jpg', 'image', 356789, 'image/jpeg', 1080, 1920, 'fitness,model'),
(3, 5, 'Property Exterior', 'property_ext.jpg', '/storage/uploads/images/property_ext.jpg', 'image', 654321, 'image/jpeg', 1920, 1080, 'realestate,exterior'),
(3, 5, 'Property Interior', 'property_int.jpg', '/storage/uploads/images/property_int.jpg', 'image', 598234, 'image/jpeg', 1920, 1080, 'realestate,interior'),
(3, 4, 'Brand Logo', 'client_logo.png', '/storage/uploads/logos/client_logo.png', 'image', 52428, 'image/png', 512, 512, 'logo,brand'),
(3, 5, 'Source Video Clip', 'source_clip.mp4', '/storage/uploads/videos/source_clip.mp4', 'video', 15728640, 'video/mp4', 1080, 1920, 'source,tiktok');

-- Insert Sample Templates
INSERT INTO `templates` (`tenant_id`, `user_id`, `name`, `description`, `category`, `is_public`, `usage_count`) VALUES
(NULL, NULL, 'Product Promo - 30 sec', 'Quick product showcase template', 'product_promo', 1, 45),
(NULL, NULL, 'TikTok/Reels Short', 'Vertical short-form content template', 'tiktok_short', 1, 128),
(NULL, NULL, 'YouTube Intro', 'Professional channel intro', 'youtube_intro', 1, 67),
(NULL, NULL, 'Quote Video', 'Inspirational quote with visuals', 'quote_video', 1, 89),
(2, 2, 'My Custom Product Template', 'Custom template for product videos', 'product_promo', 0, 3),
(3, 4, 'Agency Slideshow', 'Client project slideshow', 'slideshow', 0, 8);

-- Insert Template Scenes
INSERT INTO `template_scenes` (`template_id`, `position`, `scene_type`, `duration`, `prompt`, `text_content`) VALUES
(1, 1, 'text_image', 3, 'Dynamic product reveal with energetic motion', 'Introducing Our New Product'),
(1, 2, 'image_only', 5, 'Close-up product showcase with smooth rotation', NULL),
(1, 3, 'text_image', 4, 'Call to action with vibrant colors', 'Get Yours Today!'),
(2, 1, 'text_only', 2, 'Eye-catching text intro with trendy effects', 'Check This Out!'),
(2, 2, 'video_clip', 6, 'Main content with dynamic transitions', NULL),
(2, 3, 'text_only', 2, 'Strong call-to-action finale', 'Follow for More!'),
(3, 1, 'video_clip', 5, 'Cinematic intro with logo animation', NULL),
(4, 1, 'text_only', 8, 'Elegant text display with subtle background', '"Success is not final, failure is not fatal."');

-- Insert Style Presets
INSERT INTO `style_presets` (`name`, `slug`, `description`, `color_mood`, `motion_style`, `visual_style`, `is_active`) VALUES
('Cinematic', 'cinematic', 'Movie-like quality with dramatic lighting', 'dark', 'smooth', 'cinematic', 1),
('Vibrant Social', 'vibrant-social', 'Bright and energetic for social media', 'vibrant', 'dynamic', 'modern', 1),
('Minimal Clean', 'minimal-clean', 'Simple and professional look', 'neutral', 'static', 'minimal', 1),
('Cartoon Fun', 'cartoon-fun', 'Playful animated style', 'colorful', 'bouncy', 'cartoon', 1),
('3D Modern', '3d-modern', 'Contemporary 3D rendered look', 'cool', 'smooth', '3d', 1),
('Vintage Film', 'vintage-film', 'Retro film aesthetic', 'sepia', 'gentle', 'vintage', 1);

-- Insert Sample Render Jobs
INSERT INTO `render_jobs` (`tenant_id`, `project_id`, `user_id`, `job_type`, `status`, `progress`, `parameters`, `started_at`, `completed_at`) VALUES
(2, 1, 2, 'text_to_video', 'completed', 100, '{"script":"Discover our amazing new product","style":"cinematic","duration":30}', '2025-11-20 10:00:00', '2025-11-20 10:15:00'),
(2, 2, 2, 'images_to_video', 'completed', 100, '{"image_ids":[3,4],"transition":"fade","duration":15}', '2025-11-21 14:30:00', '2025-11-21 14:42:00'),
(3, 4, 4, 'text_to_video', 'processing', 67, '{"script":"Transform your fitness journey","style":"vibrant-social","duration":20}', '2025-11-23 08:00:00', NULL),
(3, 5, 5, 'images_to_video', 'completed', 100, '{"image_ids":[7,8],"transition":"slide","duration":25}', '2025-11-22 11:00:00', '2025-11-22 11:18:00'),
(3, 6, 5, 'video_to_video', 'completed', 100, '{"video_id":10,"style":"3d-modern","duration":12}', '2025-11-22 16:00:00', '2025-11-22 16:10:00'),
(2, 3, 3, 'image_to_video', 'queued', 0, '{"image_id":1,"motion":"zoom","duration":10}', NULL, NULL),
(3, 4, 4, 'text_to_video', 'failed', 0, '{"script":"Test video","style":"cinematic"}', '2025-11-19 09:00:00', '2025-11-19 09:02:00');

-- Update failed job with error message
UPDATE `render_jobs` SET `error_message` = 'AI service timeout - please try again' WHERE `id` = 7;

-- Insert Sample Videos
INSERT INTO `videos` (`tenant_id`, `project_id`, `render_job_id`, `user_id`, `title`, `description`, `filename`, `file_path`, `file_size`, `duration`, `resolution`, `thumbnail_url`, `public_token`, `view_count`, `download_count`) VALUES
(2, 1, 1, 2, 'Product Launch Video - Final', 'AI-generated product promo video', 'product_launch_final.mp4', '/storage/outputs/product_launch_final.mp4', 8388608, 30, '1080p', '/storage/outputs/thumbs/product_launch_thumb.jpg', 'a7f8e9d0c1b2a3f4e5d6c7b8a9f0e1d2', 45, 12),
(2, 2, 2, 2, 'Travel Reel - Instagram', 'Beautiful travel montage', 'travel_reel.mp4', '/storage/outputs/travel_reel.mp4', 5242880, 15, '1080p', '/storage/outputs/thumbs/travel_reel_thumb.jpg', 'b8g9f0e1d2c3b4a5f6e7d8c9b0a1f2e3', 128, 34),
(3, 5, 4, 5, 'Property Showcase', 'Real estate property tour', 'property_showcase.mp4', '/storage/outputs/property_showcase.mp4', 10485760, 25, '1080p', '/storage/outputs/thumbs/property_thumb.jpg', 'c9h0g1f2e3d4c5b6a7f8e9d0c1b2a3f4', 67, 8),
(3, 6, 5, 5, 'TikTok Ad - Stylized', 'AI-enhanced TikTok advertisement', 'tiktok_ad_stylized.mp4', '/storage/outputs/tiktok_ad_stylized.mp4', 4194304, 12, '1080p', '/storage/outputs/thumbs/tiktok_ad_thumb.jpg', 'd0i1h2g3f4e5d6c7b8a9f0e1d2c3b4a5', 234, 56);

-- Insert Sample API Keys
INSERT INTO `api_keys` (`tenant_id`, `name`, `key_hash`, `is_active`) VALUES
(2, 'Production API Key', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQR', 1),
(3, 'Main API Key', '$2y$10$1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQR', 1),
(3, 'Development API Key', '$2y$10$zyxwvutsrqponmlkjihgfedcba0987654321ZYXWVUTSRQPONMLKJI', 1);

-- API Key plain text (for testing - store these securely):
-- Tenant 2: avgen_live_2k8j9h7g6f5d4s3a2z1x0c9v8b7n6m5l4k3j2h1g0
-- Tenant 3: avgen_live_9m8n7b6v5c4x3z2a1s0d4f5g6h7j8k9l0p1q2r3t4
-- Tenant 3 Dev: avgen_test_5l4k3j2h1g0f9d8s7a6z5x4c3v2b1n0m9l8k7j6i5

-- Insert Sample Notifications
INSERT INTO `notifications` (`tenant_id`, `user_id`, `type`, `title`, `message`, `is_read`, `sent_at`) VALUES
(2, 2, 'render_completed', 'Video Render Completed', 'Your video "Product Launch Video - Final" has been successfully rendered and is ready to download.', 1, '2025-11-20 10:15:00'),
(2, 2, 'render_completed', 'Video Render Completed', 'Your video "Travel Reel - Instagram" has been successfully rendered.', 1, '2025-11-21 14:42:00'),
(3, 4, 'quota_warning', 'Approaching Monthly Render Limit', 'You have used 67 out of 200 renders this month (33% used).', 0, '2025-11-23 09:00:00'),
(3, 5, 'render_completed', 'Video Render Completed', 'Your video "Property Showcase" is ready!', 1, '2025-11-22 11:18:00'),
(2, 2, 'payment_success', 'Payment Received', 'Thank you! Your payment of $31.90 has been received.', 1, '2025-11-10 14:25:00');

-- Insert Sample Analytics Events
INSERT INTO `analytics_events` (`tenant_id`, `user_id`, `event_type`, `event_data`) VALUES
(2, 2, 'project_created', '{"project_id":1,"project_type":"text_to_video"}'),
(2, 2, 'render_completed', '{"render_job_id":1,"duration":30,"resolution":"1080p"}'),
(2, 2, 'video_downloaded', '{"video_id":1}'),
(2, 2, 'project_created', '{"project_id":2,"project_type":"images_to_video"}'),
(2, 2, 'render_completed', '{"render_job_id":2,"duration":15,"resolution":"1080p"}'),
(3, 4, 'project_created', '{"project_id":4,"project_type":"text_to_video"}'),
(3, 5, 'asset_uploaded', '{"asset_id":7,"file_type":"image","file_size":654321}'),
(3, 5, 'render_completed', '{"render_job_id":4,"duration":25,"resolution":"1080p"}'),
(3, 5, 'video_downloaded', '{"video_id":3}'),
(2, 3, 'asset_uploaded', '{"asset_id":1,"file_type":"image"}');

-- ========================================
-- END OF DATABASE SCHEMA
-- ========================================
