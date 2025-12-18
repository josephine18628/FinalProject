<?php
/**
 * Course Dashboard
 * CS3 Quiz Platform
 */

require_once 'config/courses.php';
require_once 'includes/functions.php';

// Require authentication
requireLogin();

$courses = getCourses();
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CS3 Quiz Platform</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars(getCurrentUsername()); ?>! 🎓</h1>
            <p class="subtitle">Choose your course and start mastering computer science concepts</p>
        </div>

        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-bottom: 3rem;">
            <p style="font-size: 1.1rem; color: var(--light-text); max-width: 800px; margin: 0 auto;">
                Select from <strong>11 core computer science courses</strong> to generate AI-powered quizzes. 
                Each quiz is tailored to your chosen difficulty level with questions based on authoritative textbooks.
            </p>
        </div>

        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-card-header">
                        <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                    </div>
                    <div class="course-card-body">
                        <p class="course-description">
                            <?php echo htmlspecialchars($course['description']); ?>
                        </p>
                        <div class="course-textbooks">
                            <strong>Reference Textbooks:</strong>
                            <ul>
                                <?php foreach (array_slice($course['textbooks'], 0, 2) as $book): ?>
                                    <li><?php echo htmlspecialchars($book); ?></li>
                                <?php endforeach; ?>
                                <?php if (count($course['textbooks']) > 2): ?>
                                    <li><em>...and <?php echo count($course['textbooks']) - 2; ?> more</em></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="course-card-footer">
                        <a href="quiz-config.php?course=<?php echo urlencode($course['id']); ?>" 
                           class="btn btn-primary btn-block">
                            Start Quiz
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="../js/validation.js"></script>
</body>
</html>

