<?php
/**
 * Quiz Results Page
 * CS3 Quiz Platform
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Require authentication
requireLogin();

// Get attempt ID from URL or session
$attemptId = $_GET['attempt'] ?? null;

// Check if results exist in session (just submitted)
if (isset($_SESSION['quiz_results'])) {
    $results = $_SESSION['quiz_results'];
    unset($_SESSION['quiz_results']); // Clear after retrieving
} elseif ($attemptId) {
    // Load results from database
    try {
        $pdo = getDBConnection();
        
        // Get attempt details
        $stmt = $pdo->prepare("
            SELECT * FROM quiz_attempts 
            WHERE id = ? AND user_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$attemptId, getCurrentUserId()]);
        $attempt = $stmt->fetch();
        
        if (!$attempt) {
            redirectWithMessage('dashboard.php', 'error', 'Quiz results not found');
        }
        
        // Get responses
        $stmt = $pdo->prepare("
            SELECT * FROM quiz_responses 
            WHERE attempt_id = ? 
            ORDER BY question_number
        ");
        $stmt->execute([$attemptId]);
        $responses = $stmt->fetchAll();
        
        // Build results array
        $correctCount = 0;
        $totalGradable = 0;
        
        foreach ($responses as $response) {
            if ($response['is_correct'] !== null) {
                $totalGradable++;
                if ($response['is_correct']) {
                    $correctCount++;
                }
            }
            
            // Parse JSON fields
            if ($response['options']) {
                $response['options_decoded'] = json_decode($response['options'], true);
            }
        }
        
        $results = [
            'attempt_id' => $attempt['id'],
            'course_name' => $attempt['course_name'],
            'score' => $attempt['score'],
            'correct_count' => $correctCount,
            'total_gradable' => $totalGradable,
            'total_questions' => $attempt['total_questions'],
            'time_taken' => $attempt['time_taken'],
            'time_allowed' => $attempt['time_allowed'],
            'difficulty' => $attempt['difficulty_level'],
            'responses' => $responses
        ];
        
    } catch (Exception $e) {
        logError('Results loading error', ['error' => $e->getMessage()]);
        redirectWithMessage('dashboard.php', 'error', 'Failed to load results');
    }
} else {
    redirectWithMessage('dashboard.php', 'error', 'No quiz results to display');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - CS3 Quiz Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/quiz.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="results-container">
            <!-- Results Summary -->
            <div class="results-header">
                <h1>Quiz Results</h1>
                <p class="course-name"><?php echo htmlspecialchars($results['course_name']); ?></p>
            </div>

            <div class="results-summary">
                <div class="summary-card">
                    <div class="summary-score">
                        <div class="score-circle <?php echo $results['score'] >= 70 ? 'pass' : 'fail'; ?>">
                            <span class="score-value"><?php echo number_format($results['score'], 1); ?>%</span>
                        </div>
                        <p class="score-label">
                            <?php if ($results['score'] >= 90): ?>
                                Excellent!
                            <?php elseif ($results['score'] >= 70): ?>
                                Good Job!
                            <?php elseif ($results['score'] >= 50): ?>
                                Fair
                            <?php else: ?>
                                Keep Practicing
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="summary-stats">
                        <div class="stat-item">
                            <span class="stat-label">Correct Answers</span>
                            <span class="stat-value"><?php echo $results['correct_count']; ?> / <?php echo $results['total_gradable']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Questions</span>
                            <span class="stat-value"><?php echo $results['total_questions']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Time Taken</span>
                            <span class="stat-value"><?php echo formatTime($results['time_taken']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Time Allowed</span>
                            <span class="stat-value"><?php echo formatTime($results['time_allowed']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Difficulty</span>
                            <span class="stat-value"><?php echo ucfirst($results['difficulty']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Results -->
            <div class="results-details">
                <h2>Detailed Review</h2>
                
                <?php foreach ($results['responses'] as $response): ?>
                    <?php 
                    $isCorrect = $response['is_correct'] ?? ($response['is_correct'] ?? null);
                    $statusClass = '';
                    if ($isCorrect === true || $isCorrect === 1) {
                        $statusClass = 'correct';
                    } elseif ($isCorrect === false || $isCorrect === 0) {
                        $statusClass = 'incorrect';
                    } else {
                        $statusClass = 'not-graded';
                    }
                    
                    // Get decoded options if available
                    $options = null;
                    if (isset($response['options_decoded'])) {
                        $options = $response['options_decoded'];
                    } elseif (isset($response['options']) && is_string($response['options'])) {
                        $options = json_decode($response['options'], true);
                    }
                    ?>
                    
                    <div class="result-card <?php echo $statusClass; ?>">
                        <div class="result-header">
                            <span class="question-number">Question <?php echo $response['question_number']; ?></span>
                            <span class="result-badge">
                                <?php if ($statusClass === 'correct'): ?>
                                    ✓ Correct
                                <?php elseif ($statusClass === 'incorrect'): ?>
                                    ✗ Incorrect
                                <?php else: ?>
                                    Not Graded
                                <?php endif; ?>
                                <?php if (isset($response['similarity']) && ($response['question_type'] === 'essay' || $response['question_type'] === 'calculation')): ?>
                                    <span class="similarity-score" style="margin-left: 10px; font-size: 0.9em;">
                                        (<?php echo round($response['similarity']); ?>% match)
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="result-body">
                            <p class="question-text"><?php echo nl2br(htmlspecialchars($response['question_text'])); ?></p>
                            
                            <!-- MCQ Options Display -->
                            <?php if ($response['question_type'] === 'mcq' && $options): ?>
                                <div class="options-review">
                                    <?php foreach ($options as $key => $option): ?>
                                        <?php if (is_string($key)): // Only show A, B, C, D options ?>
                                            <div class="option-item">
                                                <strong><?php echo htmlspecialchars($key); ?>.</strong> 
                                                <?php echo htmlspecialchars($option); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="answer-section">
                                <div class="answer-item">
                                    <strong>Your Answer:</strong>
                                    <div class="answer-content">
                                        <?php echo nl2br(htmlspecialchars($response['user_answer'] ?: 'No answer provided')); ?>
                                    </div>
                                </div>
                                
                                <?php if ($response['question_type'] !== 'essay'): ?>
                                    <div class="answer-item">
                                        <strong>Correct Answer:</strong>
                                        <div class="answer-content correct">
                                            <?php echo nl2br(htmlspecialchars($response['correct_answer'])); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="answer-item">
                                        <strong>Model Answer:</strong>
                                        <div class="answer-content model">
                                            <?php echo nl2br(htmlspecialchars($response['correct_answer'])); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($options && isset($options['key_points'])): ?>
                                        <div class="answer-item">
                                            <strong>Key Points to Include:</strong>
                                            <ul class="key-points">
                                                <?php foreach ($options['key_points'] as $point): ?>
                                                    <li><?php echo htmlspecialchars($point); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($response['explanation'])): ?>
                                    <div class="answer-item">
                                        <strong>Explanation:</strong>
                                        <div class="answer-content explanation">
                                            <?php echo nl2br(htmlspecialchars($response['explanation'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Actions -->
            <div class="results-actions">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                <?php if (isset($attempt)): ?>
                    <?php 
                    // Extract course ID from course name (simplified - you may need to adjust)
                    $courseIdMap = [
                        'Hardware and Systems Fundamentals' => 'hardware_systems',
                        'Web Technologies' => 'web_technologies',
                        'Intermediate Computer Programming (C++)' => 'cpp_programming',
                        'Algorithm Design and Analysis' => 'algorithms',
                        'Research Methods' => 'research_methods',
                        'Introduction to Modeling and Simulation' => 'modeling_simulation',
                        'Software Engineering' => 'software_engineering',
                        'Computer Architecture' => 'computer_architecture',
                        'Operating Systems' => 'operating_systems',
                        'Database Systems' => 'database_systems',
                        'Computer Networks' => 'computer_networks'
                    ];
                    $courseId = $courseIdMap[$attempt['course_name']] ?? 'web_technologies';
                    ?>
                    <a href="quiz-config.php?course=<?php echo urlencode($courseId); ?>" class="btn btn-success">
                        🔄 Retake This Quiz
                    </a>
                <?php endif; ?>
                <button onclick="window.print()" class="btn btn-secondary">Print Results</button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="../js/validation.js"></script>
</body>
</html>

