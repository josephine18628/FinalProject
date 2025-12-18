/**
 * Quiz Interaction Logic
 * CS3 Quiz Platform
 */

(function() {
    'use strict';

    const quizForm = document.getElementById('quizForm');
    const submitBtn = document.getElementById('submitBtn');

    if (!quizForm || !submitBtn) {
        return;
    }

    /**
     * Check if all required questions are answered
     */
    function checkCompletion() {
        const questions = quizForm.querySelectorAll('.question-card');
        let answered = 0;
        let unanswered = [];

        questions.forEach((questionCard, index) => {
            const questionNumber = index;
            const inputs = questionCard.querySelectorAll('input[type="radio"], textarea, input[type="text"]');
            
            let isAnswered = false;
            
            // Check radio buttons
            const radios = questionCard.querySelectorAll('input[type="radio"]');
            if (radios.length > 0) {
                radios.forEach(radio => {
                    if (radio.checked) isAnswered = true;
                });
            }
            
            // Check text inputs and textareas
            const textInputs = questionCard.querySelectorAll('textarea[required], input[type="text"][required]');
            if (textInputs.length > 0) {
                textInputs.forEach(input => {
                    if (input.value.trim() !== '') isAnswered = true;
                });
            }

            if (isAnswered) {
                answered++;
                questionCard.classList.add('answered');
            } else {
                questionCard.classList.remove('answered');
                unanswered.push(index + 1);
            }
        });

        return {
            total: questions.length,
            answered: answered,
            unanswered: unanswered
        };
    }

    /**
     * Update progress display
     */
    function updateProgress() {
        const progress = checkCompletion();
        const progressInfo = document.querySelector('.progress-info');
        
        if (progressInfo) {
            progressInfo.innerHTML = `
                <p>Answered: <strong>${progress.answered}/${progress.total}</strong></p>
            `;
        }
    }

    /**
     * Scroll to first unanswered question
     */
    function scrollToFirstUnanswered() {
        const progress = checkCompletion();
        
        if (progress.unanswered.length > 0) {
            const firstUnanswered = progress.unanswered[0];
            const questionCard = document.getElementById(`question-${firstUnanswered - 1}`);
            
            if (questionCard) {
                questionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                questionCard.classList.add('highlight-unanswered');
                
                setTimeout(() => {
                    questionCard.classList.remove('highlight-unanswered');
                }, 2000);
            }
        }
    }

    /**
     * Handle form submission
     */
    quizForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const progress = checkCompletion();
        
        // Check if all questions are answered
        if (progress.unanswered.length > 0) {
            const confirmMessage = `You have ${progress.unanswered.length} unanswered question(s). Do you want to submit anyway?\n\nUnanswered questions: ${progress.unanswered.join(', ')}`;
            
            if (!confirm(confirmMessage)) {
                scrollToFirstUnanswered();
                return false;
            }
        } else {
            // All questions answered - confirm submission
            if (!confirm(`Are you sure you want to submit your quiz? You have answered all ${progress.total} questions.`)) {
                return false;
            }
        }

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        submitBtn.classList.add('loading');

        // Submit the form
        this.submit();
    });

    // Monitor answer changes
    quizForm.addEventListener('change', function() {
        updateProgress();
    });

    // Monitor text input for essay and calculation questions
    const textInputs = quizForm.querySelectorAll('textarea, input[type="text"]');
    textInputs.forEach(input => {
        input.addEventListener('input', function() {
            updateProgress();
        });
    });

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            submitBtn.click();
        }
    });

    // Auto-save functionality (already handled in timer.js, but we can add indicators)
    let saveTimeout;
    quizForm.addEventListener('input', function() {
        clearTimeout(saveTimeout);
        
        // Show saving indicator
        const indicator = document.createElement('div');
        indicator.className = 'save-indicator';
        indicator.textContent = 'Saving...';
        indicator.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; z-index: 9999;';
        document.body.appendChild(indicator);
        
        saveTimeout = setTimeout(() => {
            indicator.textContent = 'Saved';
            setTimeout(() => {
                indicator.remove();
            }, 1000);
        }, 500);
    });

    // Smooth scroll for question navigation
    function addQuestionNavigation() {
        const questions = quizForm.querySelectorAll('.question-card');
        
        questions.forEach((question, index) => {
            question.style.scrollMarginTop = '100px'; // Account for fixed header
        });
    }

    // Initialize
    updateProgress();
    addQuestionNavigation();

    // Add visual feedback for radio buttons and checkboxes
    const radioLabels = document.querySelectorAll('.option-label');
    radioLabels.forEach(label => {
        const input = label.querySelector('input[type="radio"]');
        if (input) {
            input.addEventListener('change', function() {
                // Remove selected class from siblings
                const siblings = label.parentElement.querySelectorAll('.option-label');
                siblings.forEach(sib => sib.classList.remove('selected'));
                
                // Add selected class to this label
                if (this.checked) {
                    label.classList.add('selected');
                }
            });
        }
    });

})();

