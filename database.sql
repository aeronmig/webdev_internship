-- CCS Student Internship Tracker database
-- Import this file in MySQL/phpMyAdmin.

CREATE DATABASE IF NOT EXISTS `webdev`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `webdev`;

CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_address` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email_address`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `internship_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email_address` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  `firstname` VARCHAR(100) NOT NULL,
  `middlename` VARCHAR(100) NOT NULL,
  `surname` VARCHAR(100) NOT NULL,
  `company` VARCHAR(255) NOT NULL,
  `school` VARCHAR(255) NOT NULL,
  `department` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `hours_required` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `feedback_token` CHAR(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_internship_users_email` (`email_address`),
  UNIQUE KEY `uq_internship_users_feedback_token` (`feedback_token`),
  KEY `idx_internship_users_name` (`firstname`, `middlename`, `surname`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `internship_log` (
  `id` INT UNSIGNED NOT NULL,
  `user_log_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `time_in` TIME NOT NULL,
  `time_out` TIME NOT NULL DEFAULT '00:00:00',
  `hours_rendered` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `hours_accumulated` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`, `user_log_id`),
  KEY `idx_internship_log_date` (`id`, `date`),
  CONSTRAINT `fk_internship_log_user`
    FOREIGN KEY (`id`) REFERENCES `internship_users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `feedback` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `intern_id` INT UNSIGNED NOT NULL,
  `feedback` TEXT NOT NULL,
  `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_feedback_intern_id_submitted_at` (`intern_id`, `submitted_at`),
  CONSTRAINT `fk_feedback_intern`
    FOREIGN KEY (`intern_id`) REFERENCES `internship_users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- The current login.php compares admin passwords as plain text.
-- Change this password after the first login and update login.php to use
-- password_verify() for admin accounts.
INSERT INTO `admin` (`email_address`, `password`, `role`)
VALUES ('admin@example.com', 'admin', 'admin')
ON DUPLICATE KEY UPDATE `email_address` = `email_address`;