<?php
/**
 * Utility Functions
 * CS3 Quiz Platform
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Require login - redirect to login page if not authenticated
 * @param string $redirectTo URL to redirect to after login
 */
function requireLogin($redirectTo = null) {
    if (!isLoggedIn()) {
        if ($redirectTo) {
            $_SESSION['redirect_after_login'] = $redirectTo;
        }
        header('Location: /Individual Project/index.php');
        exit();
    }
}

/**
 * Get current username
 * @return string|null Username or null if not logged in
 */
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Sanitize input string
 * @param string $input Input string to sanitize
 * @return string Sanitized string
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePassword($password) {
    $result = ['valid' => false, 'message' => ''];
    
    if (strlen($password) < 6) {
        $result['message'] = 'Password must be at least 6 characters long';
        return $result;
    }
    
    if (!preg_match('/[A-Za-z]/', $password)) {
        $result['message'] = 'Password must contain at least one letter';
        return $result;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $result['message'] = 'Password must contain at least one number';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Password is strong';
    return $result;
}

/**
 * Format time in seconds to human readable format
 * @param int $seconds Time in seconds
 * @return string Formatted time string (e.g., "5:30" or "1:05:30")
 */
function formatTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%d:%02d', $minutes, $secs);
    }
}

/**
 * Calculate quiz time based on question types and difficulty
 * @param array $questions Array of questions with types
 * @param string $difficulty Difficulty level
 * @return int Time in seconds
 */
function calculateQuizTime($questions, $difficulty) {
    $baseTime = 0;
    
    // Base time per question type (in seconds)
    $timePerType = [
        'mcq' => 60,
        'true_false' => 60,
        'calculation' => 180,
        'essay' => 300
    ];
    
    // Count questions by type
    foreach ($questions as $question) {
        $type = $question['type'] ?? 'mcq';
        $baseTime += $timePerType[$type] ?? 60;
    }
    
    // Apply difficulty multiplier
    $multipliers = [
        'beginner' => 1.2,
        'intermediate' => 1.0,
        'advanced' => 1.5
    ];
    
    $multiplier = $multipliers[$difficulty] ?? 1.0;
    return (int)($baseTime * $multiplier);
}

/**
 * Calculate score percentage
 * @param int $correct Number of correct answers
 * @param int $total Total number of questions
 * @return float Score percentage
 */
function calculateScore($correct, $total) {
    if ($total == 0) {
        return 0;
    }
    return round(($correct / $total) * 100, 2);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message or null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Redirect with message
 * @param string $url URL to redirect to
 * @param string $type Message type
 * @param string $message Message content
 */
function redirectWithMessage($url, $type, $message) {
    setFlashMessage($type, $message);
    header('Location: ' . $url);
    exit();
}

/**
 * JSON response helper
 * @param bool $success Success status
 * @param mixed $data Response data
 * @param string $message Response message
 * @param int $httpCode HTTP status code
 */
function jsonResponse($success, $data = null, $message = '', $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit();
}

/**
 * Log error to file
 * @param string $message Error message
 * @param array $context Additional context
 */
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[{$timestamp}] {$message} {$contextStr}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
?>

