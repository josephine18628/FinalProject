<?php
/**
 * Quiz Configuration Page
 * CS3 Quiz Platform
 */

require_once 'config/courses.php';
require_once 'config/topics.php';
require_once 'includes/functions.php';

// Require authentication
requireLogin();

// Get course ID from query string
$courseId = $_GET['course'] ?? '';
if (empty($courseId)) {
    redirectWithMessage('dashboard.php', 'error', 'Please select a course');
}

// Get course details
$course = getCourseById($courseId);
if (!$course) {
    redirectWithMessage('dashboard.php', 'error', 'Course not found');
}

// Get course topics
$topics = getOrganizedTopics($courseId);

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Quiz - <?php echo htmlspecialchars($course['name']); ?></title>
    <link rel="stylesheet" href="css/style.css?v=2.0">
    <link rel="stylesheet" href="css/responsive.css?v=2.0">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="quiz-config-container">
            <div class="page-header">
                <h1>Configure Your Quiz</h1>
                <p class="course-name"><?php echo htmlspecialchars($course['name']); ?></p>
            </div>

            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>

            <form id="quizConfigForm" class="quiz-config-form">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($courseId); ?>">

                <!-- Topics Selection -->
                <div class="form-section">
                    <h3>Step 1: Select Topics</h3>
                    <p class="section-description">Choose the topics you want to be included in your quiz. You can select all topics or specific ones based on your study focus. Questions will only be generated from the topics you select below.</p>
                    
                    <div class="topics-selection">
                        <div class="topics-control">
                            <button type="button" id="selectAllTopics" class="btn btn-sm btn-outline">Select All</button>
                            <button type="button" id="deselectAllTopics" class="btn btn-sm btn-outline">Deselect All</button>
                            <div id="topicsCounter" class="topics-counter">
                                <span class="counter-badge">0 topics selected</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($topics)): ?>
                            <?php foreach ($topics as $category => $categoryTopics): ?>
                                <div class="topic-category">
                                    <h4 class="category-title">
                                        <label>
                                            <input type="checkbox" class="category-checkbox" data-category="<?php echo htmlspecialchars($category); ?>">
                                            <?php echo htmlspecialchars($category); ?>
                                        </label>
                                    </h4>
                                    <div class="topic-items" style="display: flex; flex-direction: column; width: 100%;">
                                        <?php foreach ($categoryTopics as $index => $topic): ?>
                                            <label class="topic-checkbox" style="width: 100%; max-width: 100%; display: flex;">
                                                <input type="checkbox" name="topics[]" value="<?php echo htmlspecialchars($topic); ?>" 
                                                       class="topic-item" data-category="<?php echo htmlspecialchars($category); ?>">
                                                <span><?php echo htmlspecialchars($topic); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-topics-message">No topics available for this course.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Question Format -->
                <div class="form-section">
                    <h3>Step 2: Question Format</h3>
                    <div class="form-group">
                        <select id="questionFormat" name="question_format" class="form-control" required>
                            <option value="">Select question format...</option>
                            <option value="mcq">Multiple Choice Questions</option>
                            <option value="true_false">True or False</option>
                            <option value="essay">Essay Questions</option>
                            <option value="calculation">Calculation Problems</option>
                            <option value="all_types">All Types (Mixed)</option>
                        </select>
                    </div>
                </div>

                <!-- Difficulty Level -->
                <div class="form-section">
                    <h3>Step 3: Difficulty Level</h3>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="difficulty" value="beginner" required>
                            <span class="radio-label">
                                <strong>Beginner</strong>
                                <small>Basic concepts and fundamentals</small>
                            </span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="difficulty" value="intermediate" checked>
                            <span class="radio-label">
                                <strong>Intermediate</strong>
                                <small>Standard difficulty for exam preparation</small>
                            </span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="difficulty" value="advanced">
                            <span class="radio-label">
                                <strong>Advanced</strong>
                                <small>Complex problems and deep understanding</small>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Number of Questions -->
                <div class="form-section" id="singleTypeSection">
                    <h3>Step 4: Number of Questions</h3>
                    <div class="form-group">
                        <input type="number" id="numQuestions" name="num_questions" 
                               class="form-control" min="1" max="50" value="10">
                        <small class="form-text">Choose between 1 and 50 questions</small>
                    </div>
                </div>

                <!-- Mixed Type Questions -->
                <div class="form-section" id="mixedTypeSection" style="display: none;">
                    <h3>Step 4: Number of Questions per Type</h3>
                    <div class="mixed-type-grid">
                        <div class="form-group">
                            <label for="mcqCount">Multiple Choice</label>
                            <input type="number" id="mcqCount" name="mcq_count" 
                                   class="form-control" min="0" max="25" value="5">
                        </div>
                        <div class="form-group">
                            <label for="tfCount">True/False</label>
                            <input type="number" id="tfCount" name="tf_count" 
                                   class="form-control" min="0" max="25" value="5">
                        </div>
                        <div class="form-group">
                            <label for="essayCount">Essay</label>
                            <input type="number" id="essayCount" name="essay_count" 
                                   class="form-control" min="0" max="10" value="2">
                        </div>
                        <div class="form-group">
                            <label for="calcCount">Calculation</label>
                            <input type="number" id="calcCount" name="calc_count" 
                                   class="form-control" min="0" max="25" value="3">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="generateBtn">
                        Generate Quiz
                    </button>
                </div>

                <div id="loadingMessage" class="loading-message" style="display: none;">
                    <div class="spinner"></div>
                    <p>Generating your quiz... This may take a moment.</p>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Topic selection handlers
        const selectAllBtn = document.getElementById('selectAllTopics');
        const deselectAllBtn = document.getElementById('deselectAllTopics');
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
        const topicCheckboxes = document.querySelectorAll('.topic-item');
        const topicsCounter = document.getElementById('topicsCounter');

        // Update topics counter
        function updateTopicsCounter() {
            const selectedCount = document.querySelectorAll('.topic-item:checked').length;
            const totalCount = topicCheckboxes.length;
            const counterBadge = topicsCounter.querySelector('.counter-badge');
            
            if (counterBadge) {
                counterBadge.textContent = `${selectedCount} of ${totalCount} topics selected`;
                
                // Change color based on selection
                if (selectedCount === 0) {
                    counterBadge.style.background = '#94a3b8';
                } else if (selectedCount === totalCount) {
                    counterBadge.style.background = 'var(--secondary-color)';
                } else {
                    counterBadge.style.background = 'var(--primary-color)';
                }
            }
        }

        // Initialize counter on page load
        updateTopicsCounter();

        // Select all topics
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                topicCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                categoryCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateTopicsCounter();
            });
        }

        // Deselect all topics
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                topicCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                categoryCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateTopicsCounter();
            });
        }

        // Category checkbox handler
        categoryCheckboxes.forEach(categoryCheckbox => {
            categoryCheckbox.addEventListener('change', function() {
                const category = this.dataset.category;
                const categoryTopics = document.querySelectorAll(`.topic-item[data-category="${category}"]`);
                
                categoryTopics.forEach(topic => {
                    topic.checked = this.checked;
                });
                updateTopicsCounter();
            });
        });

        // Topic checkbox handler - update category checkbox state
        topicCheckboxes.forEach(topicCheckbox => {
            topicCheckbox.addEventListener('change', function() {
                const category = this.dataset.category;
                const categoryCheckbox = document.querySelector(`.category-checkbox[data-category="${category}"]`);
                const categoryTopics = document.querySelectorAll(`.topic-item[data-category="${category}"]`);
                
                // Check if all topics in category are checked
                const allChecked = Array.from(categoryTopics).every(t => t.checked);
                const someChecked = Array.from(categoryTopics).some(t => t.checked);
                
                if (categoryCheckbox) {
                    categoryCheckbox.checked = allChecked;
                    categoryCheckbox.indeterminate = someChecked && !allChecked;
                }
                updateTopicsCounter();
            });
        });

        // Toggle between single type and mixed type question inputs
        const questionFormatSelect = document.getElementById('questionFormat');
        const singleTypeSection = document.getElementById('singleTypeSection');
        const mixedTypeSection = document.getElementById('mixedTypeSection');
        const numQuestionsInput = document.getElementById('numQuestions');

        questionFormatSelect.addEventListener('change', function() {
            if (this.value === 'all_types') {
                singleTypeSection.style.display = 'none';
                mixedTypeSection.style.display = 'block';
                numQuestionsInput.removeAttribute('required');
            } else {
                singleTypeSection.style.display = 'block';
                mixedTypeSection.style.display = 'none';
                numQuestionsInput.setAttribute('required', 'required');
            }
        });

        // Handle form submission
        const quizConfigForm = document.getElementById('quizConfigForm');
        const generateBtn = document.getElementById('generateBtn');
        const loadingMessage = document.getElementById('loadingMessage');

        quizConfigForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate topics selection
            const selectedTopics = document.querySelectorAll('.topic-item:checked');
            if (selectedTopics.length === 0) {
                alert('Please select at least one topic for your quiz');
                return;
            }

            // Get form data
            const formData = new FormData(this);
            const data = {};
            
            // Handle regular fields
            for (const [key, value] of formData.entries()) {
                if (key === 'topics[]') {
                    // Collect all topics into an array
                    if (!data.topics) {
                        data.topics = [];
                    }
                    data.topics.push(value);
                } else {
                    data[key] = value;
                }
            }

            // Validate mixed type questions
            if (data.question_format === 'all_types') {
                const total = parseInt(data.mcq_count || 0) + 
                            parseInt(data.tf_count || 0) + 
                            parseInt(data.essay_count || 0) + 
                            parseInt(data.calc_count || 0);
                
                if (total === 0) {
                    alert('Please select at least one question type');
                    return;
                }
            }

            // Show loading state
            generateBtn.disabled = true;
            loadingMessage.style.display = 'block';

            try {
                const response = await fetch('api/generate-quiz.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Redirect to quiz page
                    window.location.href = 'quiz.php';
                } else {
                    alert('Error: ' + (result.message || 'Failed to generate quiz'));
                    generateBtn.disabled = false;
                    loadingMessage.style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while generating the quiz. Please try again.');
                generateBtn.disabled = false;
                loadingMessage.style.display = 'none';
            }
        });
    </script>
    <script src="js/validation.js"></script>
</body>
</html>

