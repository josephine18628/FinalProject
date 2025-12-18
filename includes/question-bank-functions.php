<?php
/**
 * Question Bank Management Functions
 * CS3 Quiz Platform
 */

/**
 * Get questions from question bank for a user
 * Excludes questions the user has already seen
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param string $courseId Course identifier
 * @param string $questionType Type of question
 * @param string $difficulty Difficulty level
 * @param int $count Number of questions needed
 * @return array Array of questions from bank
 */
function getQuestionsFromBank($pdo, $userId, $courseId, $questionType, $difficulty, $count) {
    try {
        // Get questions that user hasn't seen yet
        $stmt = $pdo->prepare("
            SELECT qb.*
            FROM question_bank qb
            LEFT JOIN user_question_history uqh 
                ON qb.id = uqh.question_bank_id AND uqh.user_id = ?
            WHERE qb.course_id = ?
                AND qb.question_type = ?
                AND qb.difficulty_level = ?
                AND uqh.id IS NULL
            ORDER BY qb.times_used ASC, RAND()
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $courseId, $questionType, $difficulty, $count]);
        $questions = $stmt->fetchAll();
        
        return array_map(function($q) {
            return [
                'id' => $q['id'],
                'type' => $q['question_type'],
                'question' => $q['question_text'],
                'correct_answer' => $q['correct_answer'],
                'options' => $q['options'] ? json_decode($q['options'], true) : null,
                'solution_steps' => $q['solution_steps'] ? json_decode($q['solution_steps'], true) : null,
                'model_answer' => $q['model_answer'],
                'key_points' => $q['key_points'] ? json_decode($q['key_points'], true) : null,
                'explanation' => $q['explanation'],
                'from_bank' => true
            ];
        }, $questions);
        
    } catch (PDOException $e) {
        error_log("Question bank fetch error: " . $e->getMessage());
        return [];
    }
}

/**
 * Save questions to question bank
 * 
 * @param PDO $pdo Database connection
 * @param string $courseId Course identifier
 * @param array $questions Array of questions to save
 * @return array Array of question bank IDs
 */
function saveQuestionsToBank($pdo, $courseId, $questions) {
    $bankIds = [];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO question_bank 
            (course_id, question_type, difficulty_level, question_text, correct_answer, 
             options, solution_steps, model_answer, key_points, explanation, times_used)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        foreach ($questions as $q) {
            // Check if similar question already exists to avoid duplicates
            $existing = findSimilarQuestion($pdo, $courseId, $q['type'], $q['question']);
            
            if ($existing) {
                $bankIds[] = $existing['id'];
                // Increment times_used
                incrementQuestionUsage($pdo, $existing['id']);
            } else {
                // Prepare data based on question type
                $options = isset($q['options']) ? json_encode($q['options']) : null;
                $solutionSteps = isset($q['solution_steps']) ? json_encode($q['solution_steps']) : null;
                $keyPoints = isset($q['key_points']) ? json_encode($q['key_points']) : null;
                $modelAnswer = $q['model_answer'] ?? null;
                $explanation = $q['explanation'] ?? null;
                
                // Get correct answer - for essay questions, use model_answer as placeholder
                $correctAnswer = $q['correct_answer'] ?? $modelAnswer ?? '';
                
                // Determine difficulty from question data or use default
                $difficulty = $q['difficulty'] ?? 'intermediate';
                
                $stmt->execute([
                    $courseId,
                    $q['type'],
                    $difficulty,
                    $q['question'],
                    $correctAnswer,
                    $options,
                    $solutionSteps,
                    $modelAnswer,
                    $keyPoints,
                    $explanation
                ]);
                
                $bankIds[] = $pdo->lastInsertId();
            }
        }
        
        return $bankIds;
        
    } catch (PDOException $e) {
        error_log("Question bank save error: " . $e->getMessage());
        return $bankIds;
    }
}

/**
 * Find similar question in bank to avoid duplicates
 * 
 * @param PDO $pdo Database connection
 * @param string $courseId Course identifier
 * @param string $type Question type
 * @param string $questionText Question text
 * @return array|null Question if found, null otherwise
 */
function findSimilarQuestion($pdo, $courseId, $type, $questionText) {
    try {
        // Use similarity check - exact match or very close
        $stmt = $pdo->prepare("
            SELECT id, times_used
            FROM question_bank
            WHERE course_id = ?
                AND question_type = ?
                AND question_text = ?
            LIMIT 1
        ");
        
        $stmt->execute([$courseId, $type, $questionText]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Increment usage count for a question
 * 
 * @param PDO $pdo Database connection
 * @param int $questionBankId Question bank ID
 */
function incrementQuestionUsage($pdo, $questionBankId) {
    try {
        $stmt = $pdo->prepare("
            UPDATE question_bank 
            SET times_used = times_used + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$questionBankId]);
    } catch (PDOException $e) {
        error_log("Question usage increment error: " . $e->getMessage());
    }
}

/**
 * Record that a user has seen/answered a question
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param int $questionBankId Question bank ID
 * @param int $attemptId Quiz attempt ID
 * @param bool|null $wasCorrect Whether answer was correct
 */
function recordUserQuestionHistory($pdo, $userId, $questionBankId, $attemptId, $wasCorrect = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_question_history 
            (user_id, question_bank_id, attempt_id, was_correct)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                attempt_id = VALUES(attempt_id),
                was_correct = VALUES(was_correct),
                answered_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([$userId, $questionBankId, $attemptId, $wasCorrect]);
    } catch (PDOException $e) {
        error_log("User question history error: " . $e->getMessage());
    }
}

/**
 * Get count of available questions in bank for user
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param string $courseId Course identifier
 * @param string $questionType Type of question
 * @param string $difficulty Difficulty level
 * @return int Number of available questions
 */
function getAvailableQuestionCount($pdo, $userId, $courseId, $questionType, $difficulty) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM question_bank qb
            LEFT JOIN user_question_history uqh 
                ON qb.id = uqh.question_bank_id AND uqh.user_id = ?
            WHERE qb.course_id = ?
                AND qb.question_type = ?
                AND qb.difficulty_level = ?
                AND uqh.id IS NULL
        ");
        
        $stmt->execute([$userId, $courseId, $questionType, $difficulty]);
        $result = $stmt->fetch();
        
        return (int)$result['count'];
        
    } catch (PDOException $e) {
        error_log("Available question count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get statistics about question bank
 * 
 * @param PDO $pdo Database connection
 * @return array Statistics array
 */
function getQuestionBankStats($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM question_bank_stats ORDER BY course_id, question_type");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Question bank stats error: " . $e->getMessage());
        return [];
    }
}
?>

