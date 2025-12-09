-- CS3 Quiz Web App Database Schema for MySQL (XAMPP)
-- Compatible with MySQL 5.7+ (requires JSON support)
-- Run this in phpMyAdmin SQL tab after creating the database

-- Create database if it doesn't exist (or create manually in phpMyAdmin)
-- CREATE DATABASE IF NOT EXISTS cs3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE cs3;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COURSES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `courses` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUESTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `questions` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `course_id` CHAR(36) NOT NULL,
    `type` ENUM('mcq', 'tf', 'essay', 'calculation', 'mixed') NOT NULL,
    `difficulty` ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    `question_text` TEXT NOT NULL,
    `correct_answer` JSON NOT NULL,
    `explanation` TEXT NULL,
    `created_by_user_id` CHAR(36) NULL,
    `is_ai_generated` BOOLEAN NOT NULL DEFAULT FALSE,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_difficulty` (`difficulty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUESTION OPTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `question_options` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `question_id` CHAR(36) NOT NULL,
    `option_text` TEXT NOT NULL,
    `option_letter` VARCHAR(1) NULL,
    `is_correct` BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
    INDEX `idx_question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUIZ SESSIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `quiz_sessions` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `student_id` CHAR(36) NOT NULL,
    `course_id` CHAR(36) NOT NULL,
    `config` JSON NOT NULL,
    `duration_minutes` INT NOT NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `score` FLOAT NULL,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUIZ RESPONSES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `quiz_responses` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `quiz_session_id` CHAR(36) NOT NULL,
    `question_id` CHAR(36) NOT NULL,
    `student_answer` JSON NOT NULL,
    `is_correct` BOOLEAN NOT NULL,
    `points_earned` FLOAT NOT NULL DEFAULT 0.0,
    `feedback` TEXT NULL,
    FOREIGN KEY (`quiz_session_id`) REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
    INDEX `idx_quiz_session_id` (`quiz_session_id`),
    INDEX `idx_question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AI GENERATION LOGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `ai_generation_logs` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `user_id` CHAR(36) NOT NULL,
    `course_id` CHAR(36) NOT NULL,
    `prompt_sent` TEXT NOT NULL,
    `response_received` JSON NULL,
    `questions_generated` INT NOT NULL DEFAULT 0,
    `questions_stored` INT NOT NULL DEFAULT 0,
    `duplicates_found` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA (Optional - for testing)
-- ============================================

-- Insert sample admin user (password: admin123)
-- Password hash generated with bcrypt for "admin123"
INSERT IGNORE INTO `users` (`id`, `email`, `password_hash`, `role`) VALUES
('00000000-0000-0000-0000-000000000001', 'admin@cs3quiz.com', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYqZJ5vJ5Ke', 'admin');

-- Insert sample courses
INSERT IGNORE INTO `courses` (`id`, `name`, `code`, `description`) VALUES
('10000000-0000-0000-0000-000000000001', 'Data Structures and Algorithms', 'CS301', 'Introduction to fundamental data structures and algorithmic problem-solving.'),
('10000000-0000-0000-0000-000000000002', 'Database Systems', 'CS302', 'Design and implementation of database systems, SQL, and data modeling.'),
('10000000-0000-0000-0000-000000000003', 'Web Development', 'CS303', 'Full-stack web development using modern frameworks and technologies.'),
('10000000-0000-0000-0000-000000000004', 'Operating Systems', 'CS304', 'Core concepts of operating systems, processes, memory management, and file systems.'),
('10000000-0000-0000-0000-000000000005', 'Software Engineering', 'CS305', 'Software development lifecycle, design patterns, and project management.');

-- ============================================
-- NOTES
-- ============================================
-- 1. All UUIDs are stored as CHAR(36) strings
-- 2. JSON columns require MySQL 5.7+ or MariaDB 10.2+
-- 3. If using older MySQL, change JSON to TEXT and parse manually
-- 4. Character set utf8mb4 supports full Unicode including emojis
-- 5. Foreign keys use ON DELETE CASCADE for cleanup
-- 6. Indexes are created on frequently queried columns
-- 7. Sample admin password: admin123 (change after first login!)
-- 
-- To generate a new password hash in Python:
-- from passlib.context import CryptContext
-- pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
-- print(pwd_context.hash("your_password"))

