<?php
/**
 * Quiz Interface Page
 * CS3 Quiz Platform
 */

require_once 'includes/functions.php';

// Require authentication
requireLogin();

// Check if quiz exists in session
if (!isset($_SESSION['current_quiz']) || empty($_SESSION['current_quiz'])) {
    redirectWithMessage('dashboard.php', 'error', 'No active quiz found. Please configure a new quiz.');
}

$quiz = $_SESSION['current_quiz'];

// Set start time if not already set
if (!isset($quiz['start_time']) || empty($quiz['start_time'])) {
    $_SESSION['current_quiz']['start_time'] = time();
    $quiz['start_time'] = time();
}

$questions = $quiz['questions'];
$timeAllowed = $quiz['time_allowed'];
$courseName = $quiz['course_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo htmlspecialchars($courseName); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/quiz.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body class="quiz-page">
    <div class="quiz-header-fixed">
        <div class="quiz-header-content">
            <div class="quiz-info">
                <h2><?php echo htmlspecialchars($courseName); ?></h2>
                <p><?php echo count($questions); ?> Questions | <?php echo ucfirst($quiz['difficulty']); ?> Level</p>
            </div>
            <div class="timer-container">
                <div class="timer" id="timer">
                    <span class="timer-label">Time Remaining:</span>
                    <span class="timer-value" id="timerValue"><?php echo formatTime($timeAllowed); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="quiz-container">
        <form id="quizForm" action="api/submit-quiz.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="start_time" value="<?php echo $quiz['start_time']; ?>">
            <input type="hidden" name="time_allowed" value="<?php echo $timeAllowed; ?>">
            
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card" id="question-<?php echo $index; ?>">
                    <div class="question-header">
                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                        <span class="question-type"><?php echo ucfirst(str_replace('_', ' ', $question['type'])); ?></span>
                    </div>
                    
                    <div class="question-body">
                        <p class="question-text"><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
                        
                        <?php if ($question['type'] === 'mcq'): ?>
                            <div class="options-group">
                                <?php foreach ($question['options'] as $key => $option): ?>
                                    <label class="option-label">
                                        <input type="radio" 
                                               name="answer_<?php echo $index; ?>" 
                                               value="<?php echo htmlspecialchars($key); ?>" 
                                               required>
                                        <span class="option-text">
                                            <strong><?php echo htmlspecialchars($key); ?>.</strong> 
                                            <?php echo htmlspecialchars($option); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        
                        <?php elseif ($question['type'] === 'true_false'): ?>
                            <div class="options-group">
                                <label class="option-label">
                                    <input type="radio" 
                                           name="answer_<?php echo $index; ?>" 
                                           value="True" 
                                           required>
                                    <span class="option-text">True</span>
                                </label>
                                <label class="option-label">
                                    <input type="radio" 
                                           name="answer_<?php echo $index; ?>" 
                                           value="False" 
                                           required>
                                    <span class="option-text">False</span>
                                </label>
                            </div>
                        
                        <?php elseif ($question['type'] === 'essay'): ?>
                            <div class="answer-input">
                                <textarea name="answer_<?php echo $index; ?>" 
                                          rows="8" 
                                          class="form-control"
                                          placeholder="Type your answer here..."
                                          required></textarea>
                                <small class="form-text">Provide a comprehensive answer (200-300 words recommended)</small>
                            </div>
                        
                        <?php elseif ($question['type'] === 'calculation'): ?>
                            <div class="answer-input">
                                <input type="text" 
                                       name="answer_<?php echo $index; ?>" 
                                       class="form-control"
                                       placeholder="Enter your answer..."
                                       required>
                                <small class="form-text">Show your work in the text box if needed</small>
                                <textarea name="working_<?php echo $index; ?>" 
                                          rows="4" 
                                          class="form-control mt-2"
                                          placeholder="Show your working/calculations here (optional)..."></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="quiz-actions">
                <div class="progress-info">
                    <p>Total Questions: <strong><?php echo count($questions); ?></strong></p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    Submit Quiz
                </button>
            </div>
        </form>
    </div>

    <script>
        // Initialize timer variables for timer.js
        window.timeAllowed = <?php echo $timeAllowed; ?>;
        window.startTime = <?php echo $quiz['start_time']; ?>;
    </script>
    <script src="../js/timer.js"></script>
    <script src="../js/quiz.js"></script>
</body>
</html>

