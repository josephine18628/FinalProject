<?php
/**
 * Generate Quiz API Endpoint
 * CS3 Quiz Platform - Gemini AI Integration
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../../config/gemini.php';
require_once '../config/courses.php';
require_once '../includes/functions.php';
require_once '../includes/question-bank-functions.php';

// Require authentication
requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Invalid request method', 405);
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Try form data instead
    $input = $_POST;
}

// Validate input
$courseId = $input['course_id'] ?? '';
$questionFormat = $input['question_format'] ?? '';
$difficulty = $input['difficulty'] ?? '';
$numQuestions = (int)($input['num_questions'] ?? 0);
$topics = $input['topics'] ?? [];

// Ensure topics is an array
if (!is_array($topics)) {
    $topics = [];
}

// Validate required fields
if (empty($courseId) || empty($questionFormat) || empty($difficulty) || $numQuestions <= 0) {
    jsonResponse(false, null, 'Missing required fields', 400);
}

// Validate topics selection
if (empty($topics)) {
    jsonResponse(false, null, 'Please select at least one topic for your quiz', 400);
}

// Validate question format
$validFormats = ['mcq', 'true_false', 'essay', 'calculation', 'all_types'];
if (!in_array($questionFormat, $validFormats)) {
    jsonResponse(false, null, 'Invalid question format', 400);
}

// Validate difficulty
$validDifficulties = ['beginner', 'intermediate', 'advanced'];
if (!in_array($difficulty, $validDifficulties)) {
    jsonResponse(false, null, 'Invalid difficulty level', 400);
}

// Validate number of questions
if ($numQuestions < 1 || $numQuestions > 50) {
    jsonResponse(false, null, 'Number of questions must be between 1 and 50', 400);
}

// Get course details
$course = getCourseById($courseId);
if (!$course) {
    jsonResponse(false, null, 'Course not found', 404);
}

try {
    $pdo = getDBConnection();
    $userId = getCurrentUserId();
    $questions = [];
    
    // Test API connectivity before proceeding (optional, commented out to avoid delays)
    // Uncomment if you want to test connectivity upfront:
    // if (!testGeminiAPIConnection()) {
    //     throw new Exception('Cannot connect to Gemini API. Please check your internet connection.');
    // }
    
    // Handle "all_types" format
    if ($questionFormat === 'all_types') {
        $mcqCount = (int)($input['mcq_count'] ?? 0);
        $tfCount = (int)($input['tf_count'] ?? 0);
        $essayCount = (int)($input['essay_count'] ?? 0);
        $calcCount = (int)($input['calc_count'] ?? 0);
        
        $totalCount = $mcqCount + $tfCount + $essayCount + $calcCount;
        if ($totalCount <= 0) {
            jsonResponse(false, null, 'Please specify at least one question type', 400);
        }
        
        // Get/generate each type of question
        if ($mcqCount > 0) {
            $mcqQuestions = getOrGenerateQuestions($pdo, $userId, $course, 'mcq', $difficulty, $mcqCount, $topics);
            $questions = array_merge($questions, $mcqQuestions);
        }
        
        if ($tfCount > 0) {
            $tfQuestions = getOrGenerateQuestions($pdo, $userId, $course, 'true_false', $difficulty, $tfCount, $topics);
            $questions = array_merge($questions, $tfQuestions);
        }
        
        if ($essayCount > 0) {
            $essayQuestions = getOrGenerateQuestions($pdo, $userId, $course, 'essay', $difficulty, $essayCount, $topics);
            $questions = array_merge($questions, $essayQuestions);
        }
        
        if ($calcCount > 0) {
            $calcQuestions = getOrGenerateQuestions($pdo, $userId, $course, 'calculation', $difficulty, $calcCount, $topics);
            $questions = array_merge($questions, $calcQuestions);
        }
        
    } else {
        // Get or generate single type of questions
        $questions = getOrGenerateQuestions($pdo, $userId, $course, $questionFormat, $difficulty, $numQuestions, $topics);
    }
    
    // Shuffle questions
    shuffle($questions);
    
    // Calculate quiz time
    $quizTime = calculateQuizTime($questions, $difficulty);
    
    // Store quiz in session
    $_SESSION['current_quiz'] = [
        'course_id' => $courseId,
        'course_name' => $course['name'],
        'question_format' => $questionFormat,
        'difficulty' => $difficulty,
        'questions' => $questions,
        'time_allowed' => $quizTime,
        'start_time' => null
    ];
    
    jsonResponse(true, [
        'questions' => $questions,
        'time_allowed' => $quizTime,
        'total_questions' => count($questions)
    ], 'Quiz generated successfully');
    
} catch (Exception $e) {
    logError('Quiz generation error', [
        'error' => $e->getMessage(),
        'course_id' => $courseId,
        'user_id' => getCurrentUserId()
    ]);
    jsonResponse(false, null, 'Failed to generate quiz: ' . $e->getMessage(), 500);
}

/**
 * Get questions from bank or generate new ones
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param array $course Course details
 * @param string $type Question type
 * @param string $difficulty Difficulty level
 * @param int $count Number of questions needed
 * @param array $topics Selected topics (optional)
 * @return array Questions array
 */
function getOrGenerateQuestions($pdo, $userId, $course, $type, $difficulty, $count, $topics = []) {
    // First, try to get questions from bank that user hasn't seen
    $bankQuestions = getQuestionsFromBank($pdo, $userId, $course['id'], $type, $difficulty, $count);
    
    $needed = $count - count($bankQuestions);
    
    if ($needed > 0) {
        // Generate new questions if we don't have enough in bank
        $newQuestions = generateQuestions($course, $type, $difficulty, $needed, $topics);
        
        // Save new questions to bank for future use
        $bankIds = saveQuestionsToBank($pdo, $course['id'], $newQuestions);
        
        // Add bank IDs to new questions
        foreach ($newQuestions as $index => $question) {
            if (isset($bankIds[$index])) {
                $newQuestions[$index]['id'] = $bankIds[$index];
                $newQuestions[$index]['from_bank'] = false; // Newly generated
            }
        }
        
        // Combine bank questions with newly generated ones
        $allQuestions = array_merge($bankQuestions, $newQuestions);
    } else {
        $allQuestions = $bankQuestions;
    }
    
    // Add difficulty to each question for later use
    foreach ($allQuestions as &$q) {
        $q['difficulty'] = $difficulty;
    }
    
    return $allQuestions;
}

/**
 * Generate questions using Gemini API
 * @param array $course Course details
 * @param string $type Question type
 * @param string $difficulty Difficulty level
 * @param int $count Number of questions
 * @param array $topics Selected topics (optional)
 * @return array Generated questions
 */
function generateQuestions($course, $type, $difficulty, $count, $topics = []) {
    // Build prompt
    $prompt = buildQuizPrompt(
        $course['name'],
        $course['textbooks'],
        $difficulty,
        $type,
        $count,
        $topics
    );
    
    // Call Gemini API
    $response = callGeminiAPI($prompt);
    $text = extractGeminiText($response);
    
    // Clean up the text - remove markdown code blocks if present
    $text = preg_replace('/```json\s*/', '', $text);
    $text = preg_replace('/```\s*$/', '', $text);
    $text = trim($text);
    
    // Try to extract JSON object if embedded in other text
    if (preg_match('/\{[\s\S]*"questions"[\s\S]*\}/', $text, $matches)) {
        $text = $matches[0];
    }
    
    // Remove control characters and fix encoding issues
    // Remove null bytes and other control characters except newlines and tabs in strings
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    
    // Fix common JSON encoding issues
    $text = str_replace(["\r\n", "\r"], "\n", $text); // Normalize line endings
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8'); // Fix encoding
    
    // Remove any BOM (Byte Order Mark)
    $text = str_replace("\xEF\xBB\xBF", '', $text);
    
    // Parse JSON response
    $data = json_decode($text, true);
    $jsonError = json_last_error();
    $jsonErrorMsg = json_last_error_msg();
    
    if ($jsonError !== JSON_ERROR_NONE || $data === null) {
        // Log the problematic text for debugging
        logError('JSON Parse Error', [
            'error' => $jsonErrorMsg,
            'json_error_code' => $jsonError,
            'text_length' => strlen($text),
            'text_preview' => substr($text, 0, 1000),
            'text_end' => substr($text, -200),
            'course' => $course['name'],
            'type' => $type
        ]);
        
        // Provide more helpful error message
        $errorMsg = $jsonErrorMsg !== 'No error' ? $jsonErrorMsg : 'JSON parsing returned null';
        throw new Exception('Failed to parse quiz questions: ' . $errorMsg . '. The API response may be incomplete or malformed.');
    }
    
    if (!isset($data['questions']) || !is_array($data['questions'])) {
        logError('Invalid Question Format', [
            'data_keys' => array_keys($data ?? []),
            'data' => json_encode($data),
            'course' => $course['name'],
            'type' => $type
        ]);
        throw new Exception('Invalid question format in API response. Expected "questions" array not found.');
    }
    
    if (empty($data['questions'])) {
        throw new Exception('No questions were generated. Please try again with different settings.');
    }
    
    // Process questions and add metadata
    $questions = [];
    foreach ($data['questions'] as $index => $q) {
        $question = [
            'number' => $index + 1,
            'type' => $type,
            'question' => $q['question'] ?? ''
        ];
        
        // Add type-specific fields
        switch ($type) {
            case 'mcq':
                $question['options'] = $q['options'] ?? [];
                $question['correct_answer'] = $q['correct_answer'] ?? '';
                $question['explanation'] = $q['explanation'] ?? '';
                break;
                
            case 'true_false':
                $question['correct_answer'] = $q['correct_answer'] ?? '';
                $question['explanation'] = $q['explanation'] ?? '';
                break;
                
            case 'essay':
                $question['model_answer'] = $q['model_answer'] ?? '';
                $question['key_points'] = $q['key_points'] ?? [];
                $question['correct_answer'] = $q['model_answer'] ?? ''; // Use model_answer as correct_answer for consistency
                $question['explanation'] = $q['explanation'] ?? '';
                break;
                
            case 'calculation':
                $question['correct_answer'] = $q['correct_answer'] ?? '';
                $question['solution_steps'] = $q['solution_steps'] ?? [];
                $question['explanation'] = $q['explanation'] ?? '';
                break;
        }
        
        $questions[] = $question;
    }
    
    return $questions;
}
?>

