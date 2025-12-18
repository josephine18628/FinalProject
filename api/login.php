<?php
/**
 * Login API Endpoint
 * CS3 Quiz Platform
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../index.php', 'error', 'Invalid request method');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('../index.php', 'error', 'Invalid security token. Please try again.');
}

// Get and sanitize input
$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    redirectWithMessage('../index.php', 'error', 'Please provide both username and password');
}

try {
    $pdo = getDBConnection();
    
    // Check if username is email or username
    $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // User not found
        logError('Login failed: User not found', ['username' => $username]);
        redirectWithMessage('../index.php', 'error', 'Invalid username or password');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Invalid password
        logError('Login failed: Invalid password', ['username' => $username]);
        redirectWithMessage('../index.php', 'error', 'Invalid username or password');
    }
    
    // Login successful - create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Check if user selected a specific course from landing page
    $redirectCourse = sanitizeInput($_POST['redirect_course'] ?? '');
    
    if (!empty($redirectCourse)) {
        // Redirect to quiz configuration for the selected course
        $redirectUrl = 'quiz-config.php?course=' . urlencode($redirectCourse);
        redirectWithMessage('../' . $redirectUrl, 'success', 'Welcome back, ' . $user['username'] . '! Let\'s start your quiz.');
    } else {
        // Check if there's a redirect URL in session
        $redirectUrl = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
        unset($_SESSION['redirect_after_login']);
        
        redirectWithMessage('../' . $redirectUrl, 'success', 'Welcome back, ' . $user['username'] . '!');
    }
    
} catch (PDOException $e) {
    logError('Login error: Database error', ['error' => $e->getMessage()]);
    redirectWithMessage('../index.php', 'error', 'An error occurred. Please try again later.');
} catch (Exception $e) {
    logError('Login error: General error', ['error' => $e->getMessage()]);
    redirectWithMessage('../index.php', 'error', 'An error occurred. Please try again later.');
}
?>

