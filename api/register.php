<?php
/**
 * Registration API Endpoint
 * CS3 Quiz Platform
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../register.php', 'error', 'Invalid request method');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('../register.php', 'error', 'Invalid security token. Please try again.');
}

// Get and sanitize input
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate input
if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
    redirectWithMessage('../register.php', 'error', 'All fields are required');
}

// Validate username format
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    redirectWithMessage('../register.php', 'error', 'Username must be 3-20 characters with letters, numbers, and underscores only');
}

// Validate email
if (!isValidEmail($email)) {
    redirectWithMessage('../register.php', 'error', 'Invalid email format');
}

// Validate password strength
$passwordValidation = validatePassword($password);
if (!$passwordValidation['valid']) {
    redirectWithMessage('../register.php', 'error', $passwordValidation['message']);
}

// Check if passwords match
if ($password !== $confirmPassword) {
    redirectWithMessage('../register.php', 'error', 'Passwords do not match');
}

try {
    $pdo = getDBConnection();
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        redirectWithMessage('../register.php', 'error', 'Username already exists');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        redirectWithMessage('../register.php', 'error', 'Email already exists');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);
    
    // Get the new user ID
    $userId = $pdo->lastInsertId();
    
    // Auto-login the user
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Check if user selected a specific course from landing page
    $redirectCourse = sanitizeInput($_POST['redirect_course'] ?? '');
    
    if (!empty($redirectCourse)) {
        // Redirect to quiz configuration for the selected course
        $redirectUrl = 'quiz-config.php?course=' . urlencode($redirectCourse);
        redirectWithMessage('../' . $redirectUrl, 'success', 'Welcome to CS3 Quiz Platform! Let\'s start your quiz.');
    } else {
        // Regular redirect to dashboard
        redirectWithMessage('../dashboard.php', 'success', 'Registration successful! Welcome to CS3 Quiz Platform.');
    }
    
} catch (PDOException $e) {
    logError('Registration error: Database error', ['error' => $e->getMessage()]);
    
    // Check for duplicate key error
    if ($e->getCode() == 23000) {
        redirectWithMessage('../register.php', 'error', 'Username or email already exists');
    }
    
    redirectWithMessage('../register.php', 'error', 'An error occurred during registration. Please try again.');
} catch (Exception $e) {
    logError('Registration error: General error', ['error' => $e->getMessage()]);
    redirectWithMessage('../register.php', 'error', 'An error occurred. Please try again later.');
}
?>

