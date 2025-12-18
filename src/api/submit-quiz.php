<?php
/**
 * Submit Quiz and Grade
 * CS3 Quiz Platform
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/question-bank-functions.php';
require_once '../includes/similarity-functions.php';

// Require authentication
requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../dashboard.php', 'error', 'Invalid request method');
}

// Check if quiz exists in session
if (!isset($_SESSION['current_quiz']) || empty($_SESSION['current_quiz'])) {
    redirectWithMessage('../dashboard.php', 'error', 'No active quiz found');
}

$quiz = $_SESSION['current_quiz'];
$questions = $quiz['questions'];

// Calculate time taken
$startTime = (int)($_POST['start_time'] ?? $quiz['start_time']);
$endTime = time();
$timeTaken = $endTime - $startTime;
$timeAllowed = (int)($_POST['time_allowed'] ?? $quiz['time_allowed']);

// Collect user answers
$responses = [];
$correctCount = 0;
$totalGradable = 0; // Questions that can be auto-graded

foreach ($questions as $index => $question) {
    $userAnswer = $_POST["answer_{$index}"] ?? '';
    $working = $_POST["working_{$index}"] ?? '';
    
    $response = [
        'question_bank_id' => $question['id'] ?? null, // Store question bank ID
        'question_number' => $index + 1,
        'question_text' => $question['question'],
        'question_type' => $question['type'],
        'user_answer' => $userAnswer,
        'working' => $working,
        'correct_answer' => '',
        'is_correct' => false,
        'options' => null,
        'explanation' => ''
    ];
    
    // Grade based on question type
    switch ($question['type']) {
        case 'mcq':
            $response['correct_answer'] = $question['correct_answer'];
            $response['options'] = json_encode($question['options']);
            $response['explanation'] = $question['explanation'] ?? '';
            $response['is_correct'] = (strtoupper(trim($userAnswer)) === strtoupper(trim($question['correct_answer'])));
            $totalGradable++;
            if ($response['is_correct']) $correctCount++;
            break;
            
        case 'true_false':
            $response['correct_answer'] = $question['correct_answer'];
            $response['explanation'] = $question['explanation'] ?? '';
            $response['is_correct'] = (strtolower(trim($userAnswer)) === strtolower(trim($question['correct_answer'])));
            $totalGradable++;
            if ($response['is_correct']) $correctCount++;
            break;
            
        case 'calculation':
            $response['correct_answer'] = $question['correct_answer'];
            
            // Use similarity-based grading for calculations
            $gradingResult = gradeCalculationAnswer(
                $userAnswer,
                $question['correct_answer'],
                $working
            );
            
            $response['is_correct'] = $gradingResult['is_correct'];
            $response['similarity'] = $gradingResult['similarity'];
            
            // Build explanation with feedback and solution steps
            $explanation = $gradingResult['feedback'];
            if (isset($question['solution_steps']) && !empty($question['solution_steps'])) {
                $explanation .= "\n\nSolution Steps:\n" . implode("\n", $question['solution_steps']);
            } elseif (isset($question['explanation'])) {
                $explanation .= "\n\n" . $question['explanation'];
            }
            $response['explanation'] = $explanation;
            
            $totalGradable++;
            if ($response['is_correct']) $correctCount++;
            break;
            
        case 'essay':
            $response['correct_answer'] = $question['model_answer'] ?? $question['correct_answer'] ?? '';
            
            // Use similarity-based grading for essays
            $keyPoints = $question['key_points'] ?? [];
            $gradingResult = gradeEssayAnswer(
                $userAnswer,
                $response['correct_answer'],
                $keyPoints
            );
            
            // Essay passes if similarity >= 60%
            $response['is_correct'] = $gradingResult['grade'];
            $response['similarity'] = $gradingResult['similarity'];
            
            // Build detailed explanation with feedback
            $explanation = $gradingResult['feedback'];
            $explanation .= "\n\nSimilarity Score: " . round($gradingResult['similarity']) . "%";
            
            if (!empty($keyPoints)) {
                $explanation .= "\nKey Points Score: " . round($gradingResult['key_points_score']) . "%";
            }
            
            $explanation .= "\n\nNote: This is an automated similarity-based assessment. Your instructor may review and adjust the grade.";
            
            $response['explanation'] = $explanation;
            
            // Store key points for display
            if (!empty($keyPoints)) {
                $response['options'] = json_encode(['key_points' => $keyPoints]);
            }
            
            $totalGradable++;
            if ($response['is_correct']) $correctCount++;
            break;
    }
    
    $responses[] = $response;
}

// Calculate score
$score = $totalGradable > 0 ? calculateScore($correctCount, $totalGradable) : 0;

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Insert quiz attempt
    $stmt = $pdo->prepare("
        INSERT INTO quiz_attempts 
        (user_id, course_name, question_format, difficulty_level, total_questions, score, time_taken, time_allowed) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        getCurrentUserId(),
        $quiz['course_name'],
        $quiz['question_format'],
        $quiz['difficulty'],
        count($questions),
        $score,
        $timeTaken,
        $timeAllowed
    ]);
    
    $attemptId = $pdo->lastInsertId();
    
    // Insert individual responses
    $stmt = $pdo->prepare("
        INSERT INTO quiz_responses 
        (question_bank_id, attempt_id, question_number, question_text, question_type, user_answer, correct_answer, is_correct, similarity, options, explanation) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($responses as $response) {
        // Combine user answer and working for calculation questions
        $fullAnswer = $response['user_answer'];
        if (!empty($response['working'])) {
            $fullAnswer .= "\n\nWorking:\n" . $response['working'];
        }
        
        // Get question bank ID if available
        $questionBankId = $response['question_bank_id'] ?? null;
        
        // Get similarity score (for essay and calculation questions)
        $similarity = $response['similarity'] ?? null;
        
        $stmt->execute([
            $questionBankId,
            $attemptId,
            $response['question_number'],
            $response['question_text'],
            $response['question_type'],
            $fullAnswer,
            $response['correct_answer'],
            $response['is_correct'],
            $similarity,
            $response['options'],
            $response['explanation']
        ]);
        
        // Record user question history if question is from bank
        if ($questionBankId) {
            recordUserQuestionHistory(
                $pdo, 
                getCurrentUserId(), 
                $questionBankId, 
                $attemptId, 
                $response['is_correct']
            );
        }
    }
    
    $pdo->commit();
    
    // Store results in session for display
    $_SESSION['quiz_results'] = [
        'attempt_id' => $attemptId,
        'course_name' => $quiz['course_name'],
        'score' => $score,
        'correct_count' => $correctCount,
        'total_gradable' => $totalGradable,
        'total_questions' => count($questions),
        'time_taken' => $timeTaken,
        'time_allowed' => $timeAllowed,
        'difficulty' => $quiz['difficulty'],
        'responses' => $responses
    ];
    
    // Clear current quiz from session
    unset($_SESSION['current_quiz']);
    
    // Redirect to results page
    redirectWithMessage('../results.php?attempt=' . $attemptId, 'success', 'Quiz submitted successfully!');
    
} catch (PDOException $e) {
    $pdo->rollBack();
    logError('Quiz submission error: Database error', [
        'error' => $e->getMessage(),
        'user_id' => getCurrentUserId()
    ]);
    redirectWithMessage('../dashboard.php', 'error', 'Failed to submit quiz. Please try again.');
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logError('Quiz submission error: General error', [
        'error' => $e->getMessage(),
        'user_id' => getCurrentUserId()
    ]);
    redirectWithMessage('../dashboard.php', 'error', 'An error occurred. Please try again.');
}