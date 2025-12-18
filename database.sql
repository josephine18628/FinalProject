-- CS3 Quiz Platform Database Schema
-- Complete Database Setup with All Features
-- MySQL Database

CREATE DATABASE IF NOT EXISTS cs3_quiz_platform;
USE cs3_quiz_platform;

-- =============================================
-- CORE TABLES
-- =============================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz attempts table
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    question_format ENUM('mcq', 'true_false', 'essay', 'calculation', 'all_types') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    total_questions INT NOT NULL,
    score FLOAT DEFAULT 0,
    time_taken INT DEFAULT 0 COMMENT 'Time taken in seconds',
    time_allowed INT DEFAULT 0 COMMENT 'Time allowed in seconds',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- QUESTION BANK SYSTEM
-- =============================================

-- Question bank table to store reusable questions
CREATE TABLE IF NOT EXISTS question_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id VARCHAR(50) NOT NULL,
    question_type ENUM('mcq', 'true_false', 'essay', 'calculation') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    question_text TEXT NOT NULL,
    correct_answer TEXT NOT NULL,
    options JSON DEFAULT NULL COMMENT 'For MCQ questions - stores all 4 options',
    solution_steps JSON DEFAULT NULL COMMENT 'For calculation questions - step by step solution',
    model_answer TEXT DEFAULT NULL COMMENT 'For essay questions - model answer',
    key_points JSON DEFAULT NULL COMMENT 'For essay questions - key points to cover',
    explanation TEXT DEFAULT NULL COMMENT 'Explanation for the answer',
    times_used INT DEFAULT 0 COMMENT 'How many times this question has been used',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course_type_difficulty (course_id, question_type, difficulty_level),
    INDEX idx_times_used (times_used),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User question history to track which questions each user has seen
CREATE TABLE IF NOT EXISTS user_question_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_bank_id INT NOT NULL,
    attempt_id INT DEFAULT NULL COMMENT 'Which quiz attempt this was part of',
    was_correct BOOLEAN DEFAULT NULL COMMENT 'Whether user answered correctly',
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_bank_id) REFERENCES question_bank(id) ON DELETE CASCADE,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_question (user_id, question_bank_id),
    INDEX idx_user_id (user_id),
    INDEX idx_question_bank_id (question_bank_id),
    INDEX idx_answered_at (answered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz responses table (with question bank link and similarity scoring)
CREATE TABLE IF NOT EXISTS quiz_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_bank_id INT DEFAULT NULL,
    attempt_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type VARCHAR(20) NOT NULL,
    user_answer TEXT,
    correct_answer TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    similarity DECIMAL(5,2) DEFAULT NULL COMMENT 'Similarity score for essay/calculation answers (0-100)',
    options JSON DEFAULT NULL COMMENT 'For MCQ questions - stores all options',
    explanation TEXT DEFAULT NULL COMMENT 'Explanation for the answer',
    FOREIGN KEY (question_bank_id) REFERENCES question_bank(id) ON DELETE SET NULL,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    INDEX idx_question_bank_id (question_bank_id),
    INDEX idx_attempt_id (attempt_id),
    INDEX idx_similarity (similarity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- VIEWS FOR ANALYTICS
-- =============================================

-- Question bank statistics view
CREATE OR REPLACE VIEW question_bank_stats AS
SELECT 
    course_id,
    question_type,
    difficulty_level,
    COUNT(*) as total_questions,
    AVG(times_used) as avg_times_used,
    MAX(times_used) as max_times_used,
    MIN(times_used) as min_times_used
FROM question_bank
GROUP BY course_id, question_type, difficulty_level;

-- =============================================
-- INITIAL DATA
-- =============================================

-- Insert a demo user for testing (password: demo123)
INSERT INTO users (username, email, password) VALUES 
('demo', 'demo@cs3quiz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username=username;

-- =============================================
-- DATABASE READY
-- =============================================

SELECT 'Database setup completed successfully!' as status,
       (SELECT COUNT(*) FROM users) as total_users,
       (SELECT COUNT(*) FROM question_bank) as total_questions_in_bank;
