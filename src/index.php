<?php
/**
 * Landing Page with Login Form
 * CS3 Quiz Platform
 */

require_once 'includes/functions.php';

// Check if user is already logged in (but don't redirect automatically)
$isLoggedIn = isLoggedIn();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CS3 Quiz Platform - Master Computer Science Concepts</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            overflow-x: hidden;
        }
        
        /* Landing Header */
        .landing-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 100;
            padding: 1.25rem 2rem;
        }
        
        .landing-nav {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .landing-logo {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--gradient-green);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }
        
        .landing-nav-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn-signin {
            padding: 0.75rem 1.5rem;
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-signin:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-getstarted {
            padding: 0.75rem 2rem;
            background: var(--gradient-green);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
            transition: all 0.2s;
        }
        
        .btn-getstarted:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        }
        
        /* Hero Section */
        .hero-section {
            margin-top: 80px;
            padding: 6rem 2rem;
            background: var(--gradient-hero);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .hero-content {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .hero-content h1 {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.03em;
        }
        
        .hero-content p {
            font-size: 1.5rem;
            opacity: 0.95;
            margin-bottom: 3rem;
            line-height: 1.6;
        }
        
        .hero-cta {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .hero-cta .btn-getstarted {
            font-size: 1.2rem;
            padding: 1.25rem 3rem;
        }
        
        /* Features Grid */
        .features-section {
            padding: 6rem 2rem;
            background: white;
        }
        
        .section-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 4rem;
        }
        
        .section-header h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--dark-text);
        }
        
        .section-header p {
            font-size: 1.25rem;
            color: var(--light-text);
        }
        
        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 2rem;
        }
        
        .feature-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark-text);
        }
        
        .feature-card p {
            font-size: 1.1rem;
            color: var(--light-text);
            line-height: 1.6;
        }
        
        /* Courses Section */
        .courses-section {
            padding: 6rem 2rem;
            background: var(--light-bg);
        }
        
        .courses-list {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .course-item {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-md);
            transition: transform 0.2s;
        }
        
        .course-item:hover {
            transform: translateX(8px);
        }
        
        .course-item h4 {
            color: var(--dark-text);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 6rem 2rem;
            background: var(--gradient-orange);
            color: white;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }
        
        .cta-section p {
            font-size: 1.25rem;
            margin-bottom: 3rem;
            opacity: 0.95;
        }
        
        .cta-section .btn-getstarted {
            background: white;
            color: var(--warning-color);
        }
        
        /* Login Modal */
        .login-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .login-modal.active {
            display: flex;
        }
        
        .login-modal-content {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: var(--shadow-xl);
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--light-text);
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }
        
        .modal-close:hover {
            background: var(--light-bg);
            color: var(--dark-text);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: var(--light-text);
        }
        
        .demo-badge {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .demo-badge strong {
            color: var(--primary-color);
        }
        
        .auth-link {
            text-align: center;
            margin-top: 1.5rem;
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
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .landing-nav-buttons {
                gap: 0.5rem;
            }
            
            .btn-signin, .btn-getstarted {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }
        
        .landing-page {
            min-height: 100vh;
            display: flex;
            background: var(--light-bg);
        }
        
        .hero-section {
            flex: 1;
            background: var(--gradient-hero);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
            max-width: 600px;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }
        
        .hero-content .tagline {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            line-height: 1.5;
        }
        
        .hero-features {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 3rem;
        }
        
        .hero-feature {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hero-feature-icon {
            font-size: 2rem;
            min-width: 50px;
        }
        
        .hero-feature-content h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .hero-feature-content p {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .login-section {
            flex: 0 0 500px;
            background: white;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .login-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }
        
        .demo-badge {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 2rem;
            text-align: center;
        }
        
        .demo-badge strong {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .auth-divider {
            text-align: center;
            margin: 2rem 0;
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        @media (max-width: 968px) {
            .landing-page {
                flex-direction: column;
            }
            
            .hero-section {
                min-height: 50vh;
                padding: 3rem 2rem;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .login-section {
                flex: 1;
                padding: 3rem 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Landing Header -->
    <header class="landing-header">
        <nav class="landing-nav">
            <div class="landing-logo">CS3 Quiz Platform</div>
            <div class="landing-nav-buttons">
                <?php if ($isLoggedIn): ?>
                    <button class="btn-signin" onclick="window.location.href='dashboard.php'">Dashboard</button>
                    <button class="btn-getstarted" onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
                <?php else: ?>
                    <button class="btn-signin" onclick="showLoginModal()">Sign In</button>
                    <button class="btn-getstarted" onclick="showLoginModal()">Get Started</button>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <?php if ($isLoggedIn): ?>
                <h1>Welcome back, <?php echo htmlspecialchars(getCurrentUsername()); ?>! 🎓</h1>
                <p>Ready to continue your computer science mastery? Choose a course below or go to your dashboard.</p>
            <?php else: ?>
                <h1>Master Computer Science with AI-Powered Quizzes</h1>
                <p>Your comprehensive platform for third-year CS exam preparation and concept mastery</p>
            <?php endif; ?>
            <div class="hero-cta">
                <?php if ($isLoggedIn): ?>
                    <button class="btn-getstarted" onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
                <?php else: ?>
                    <button class="btn-getstarted" onclick="showLoginModal()">Get Started Free</button>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section">
        <div class="section-header">
            <h2>Everything You Need to Succeed</h2>
            <p>Comprehensive tools designed specifically for computer science students</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>AI-Generated Questions</h3>
                <p>Get unique, contextually accurate questions powered by Gemini AI, based on authoritative textbooks used in your courses.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Instant Feedback</h3>
                <p>Receive immediate grading with detailed explanations to help you learn from mistakes and understand concepts better.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Customizable Difficulty</h3>
                <p>Choose from beginner, intermediate, or advanced levels tailored to your current understanding and learning goals.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Track Your Progress</h3>
                <p>Monitor your performance across all courses with detailed analytics and identify areas for improvement.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏰</div>
                <h3>Timed Practice</h3>
                <p>Experience realistic exam conditions with smart timers that adapt based on question difficulty and type.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Study Anywhere</h3>
                <p>Fully responsive design works seamlessly on desktop, tablet, and mobile devices for learning on the go.</p>
            </div>
        </div>
    </section>
    
    <!-- Courses Section -->
    <section class="courses-section">
        <div class="section-header">
            <h2>11 Core CS Courses Covered</h2>
            <p>Comprehensive coverage of all third-year computer science subjects</p>
        </div>
        <div class="courses-list">
            <div class="course-item" onclick="selectCourse('algorithms')" style="cursor: pointer;">
                <h4>📐 Algorithm Design & Analysis</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('computer_architecture')" style="cursor: pointer;">
                <h4>💻 Computer Architecture</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('hardware_systems')" style="cursor: pointer;">
                <h4>🔧 Hardware & Systems</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('web_technologies')" style="cursor: pointer;">
                <h4>🌐 Web Technologies</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('cpp_programming')" style="cursor: pointer;">
                <h4>⚙️ Intermediate C++ Programming</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('research_methods')" style="cursor: pointer;">
                <h4>📊 Research Methods</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('modeling_simulation')" style="cursor: pointer;">
                <h4>🎲 Modeling & Simulation</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('software_engineering')" style="cursor: pointer;">
                <h4>🛠️ Software Engineering</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('operating_systems')" style="cursor: pointer;">
                <h4>🖥️ Operating Systems</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('database_systems')" style="cursor: pointer;">
                <h4>🗄️ Database Systems</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
            <div class="course-item" onclick="selectCourse('computer_networks')" style="cursor: pointer;">
                <h4>🌍 Computer Networks</h4>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--light-text);">Click to start quiz →</p>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <h2>Ready to Ace Your Exams?</h2>
        <p>Join CS3 Quiz Platform today and start mastering computer science concepts</p>
        <?php if ($isLoggedIn): ?>
            <button class="btn-getstarted" onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
        <?php else: ?>
            <button class="btn-getstarted" onclick="showLoginModal()">Get Started Free</button>
        <?php endif; ?>
    </section>
    
    <!-- Login Modal -->
    <div id="loginModal" class="login-modal">
        <div class="login-modal-content">
            <button class="modal-close" onclick="hideLoginModal()">&times;</button>
            
            <div class="login-header">
                <h2 id="modalTitle">Welcome Back</h2>
                <p id="modalSubtitle">Sign in to start your learning journey</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" action="api/login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" id="redirect_course" name="redirect_course" value="">
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" required autocomplete="username" placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div class="demo-badge">
                <p style="margin: 0; color: var(--light-text); font-size: 0.9rem; margin-bottom: 0.5rem;">Try it out with demo credentials:</p>
                <p style="margin: 0;"><strong>demo</strong> / <strong>demo123</strong></p>
            </div>
            
            <div class="auth-link">
                <p>Don't have an account? <a href="#" onclick="goToRegister(event)">Create one here</a></p>
            </div>
        </div>
    </div>
    
    <script src="../js/validation.js"></script>
    <script>
        // Course name mapping for display
        const courseNames = {
            'algorithms': 'Algorithm Design & Analysis',
            'computer_architecture': 'Computer Architecture',
            'hardware_systems': 'Hardware & Systems',
            'web_technologies': 'Web Technologies',
            'cpp_programming': 'Intermediate C++ Programming',
            'research_methods': 'Research Methods',
            'modeling_simulation': 'Modeling & Simulation',
            'software_engineering': 'Software Engineering',
            'operating_systems': 'Operating Systems',
            'database_systems': 'Database Systems',
            'computer_networks': 'Computer Networks'
        };
        
        function showLoginModal() {
            document.getElementById('loginModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function hideLoginModal() {
            document.getElementById('loginModal').classList.remove('active');
            document.body.style.overflow = 'auto';
            // Clear selected course when closing modal
            document.getElementById('redirect_course').value = '';
            document.getElementById('modalTitle').textContent = 'Welcome Back';
            document.getElementById('modalSubtitle').textContent = 'Sign in to start your learning journey';
        }
        
        function selectCourse(courseId) {
            <?php if ($isLoggedIn): ?>
            // User is logged in, go directly to quiz config
            window.location.href = 'quiz-config.php?course=' + encodeURIComponent(courseId);
            <?php else: ?>
            // User not logged in, show login modal
            // Store the selected course
            document.getElementById('redirect_course').value = courseId;
            
            // Update modal title to show selected course
            const courseName = courseNames[courseId] || 'this course';
            document.getElementById('modalTitle').textContent = 'Start Quiz';
            document.getElementById('modalSubtitle').textContent = 'Sign in to take ' + courseName + ' quiz';
            
            // Show login modal
            showLoginModal();
            <?php endif; ?>
        }
        
        function goToRegister(event) {
            event.preventDefault();
            const courseId = document.getElementById('redirect_course').value;
            
            // Redirect to register page with course parameter if one is selected
            if (courseId) {
                window.location.href = 'register.php?redirect_course=' + encodeURIComponent(courseId);
            } else {
                window.location.href = 'register.php';
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideLoginModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideLoginModal();
            }
        });
        
        // Show modal if there's a flash message
        <?php if ($flashMessage): ?>
        window.addEventListener('DOMContentLoaded', function() {
            showLoginModal();
        });
        <?php endif; ?>
    </script>
</body>
</html>

