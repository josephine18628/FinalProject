<?php
/**
 * Registration Page
 * CS3 Quiz Platform
 */

require_once 'includes/functions.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Get redirect course from URL if provided
$redirectCourse = sanitizeInput($_GET['redirect_course'] ?? '');

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - CS3 Quiz Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        }
        
        .register-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg);
            padding: 2rem;
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-xl);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .register-header .logo {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--gradient-green);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .register-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }
        
        .auth-link {
            text-align: center;
            margin-top: 2rem;
            color: var(--light-text);
        }
        
        .auth-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .auth-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-page">
        <div class="register-container">
            <div class="register-header">
                <div class="logo">CS3 Quiz Platform</div>
                <h1>Create Your Account</h1>
                <p><?php echo !empty($redirectCourse) ? 'Sign up to start your quiz' : 'Get started in less than a minute'; ?></p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <form id="registerForm" action="api/register.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="redirect_course" value="<?php echo htmlspecialchars($redirectCourse); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           pattern="[a-zA-Z0-9_]{3,20}" 
                           title="Username must be 3-20 characters, letters, numbers, and underscores only"
                           autocomplete="username"
                           placeholder="Choose a username">
                    <small class="form-text">3-20 characters (letters, numbers, underscores)</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required autocomplete="email" placeholder="your@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           minlength="6" autocomplete="new-password" placeholder="Create a strong password">
                    <small class="form-text">Minimum 6 characters with letters and numbers</small>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                           autocomplete="new-password" placeholder="Re-enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <div class="auth-link">
                <p>Already have an account? <a href="index.php">Sign in here</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/validation.js"></script>
</body>
</html>
