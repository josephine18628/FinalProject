/**
 * Quiz Timer Functionality
 * CS3 Quiz Platform
 */

(function() {
    'use strict';

    const timerElement = document.getElementById('timerValue');
    const timerContainer = document.querySelector('.timer');
    const quizForm = document.getElementById('quizForm');
    
    if (!timerElement || !quizForm) {
        return;
    }

    // Get quiz start time and time allowed
    const startTime = parseInt(window.startTime || 0);
    const timeAllowed = parseInt(window.timeAllowed || 0);
    
    if (!startTime || !timeAllowed) {
        console.error('Timer configuration missing', { startTime, timeAllowed });
        return;
    }
    
    console.log('Timer initialized:', {
        startTime: startTime,
        timeAllowed: timeAllowed,
        timeAllowedFormatted: formatTime(timeAllowed),
        currentTime: Math.floor(Date.now() / 1000)
    });

    let intervalId = null;
    let hasSubmitted = false;

    /**
     * Format seconds to MM:SS or HH:MM:SS
     */
    function formatTime(seconds) {
        if (seconds < 0) seconds = 0;
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        } else {
            return `${minutes}:${String(secs).padStart(2, '0')}`;
        }
    }

    /**
     * Update timer display
     */
    function updateTimer() {
        const currentTime = Math.floor(Date.now() / 1000);
        const elapsedTime = currentTime - startTime;
        const remainingTime = timeAllowed - elapsedTime;

        if (remainingTime <= 0) {
            // Time's up!
            timerElement.textContent = '0:00';
            timerContainer.classList.add('timer-expired');
            clearInterval(intervalId);
            
            if (!hasSubmitted) {
                autoSubmitQuiz();
            }
            return;
        }

        // Update display
        timerElement.textContent = formatTime(remainingTime);

        // Visual warnings
        if (remainingTime <= 60) {
            // Less than 1 minute - urgent warning
            timerContainer.classList.add('timer-critical');
            timerContainer.classList.remove('timer-warning');
        } else if (remainingTime <= 300) {
            // Less than 5 minutes - warning
            timerContainer.classList.add('timer-warning');
        } else {
            timerContainer.classList.remove('timer-warning', 'timer-critical');
        }

        // Save progress to local storage
        saveProgress();
    }

    /**
     * Auto-submit quiz when time expires
     */
    function autoSubmitQuiz() {
        hasSubmitted = true;
        alert('Time is up! Your quiz will be submitted automatically.');
        
        // Disable all form inputs
        const inputs = quizForm.querySelectorAll('input, textarea, button');
        inputs.forEach(input => input.disabled = true);
        
        // Submit the form
        quizForm.submit();
    }

    /**
     * Save progress to local storage
     */
    function saveProgress() {
        try {
            const formData = new FormData(quizForm);
            const answers = {};
            
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('answer_')) {
                    answers[key] = value;
                }
            }
            
            localStorage.setItem('quiz_progress', JSON.stringify({
                answers: answers,
                timestamp: Date.now()
            }));
        } catch (e) {
            console.error('Failed to save progress:', e);
        }
    }

    /**
     * Restore progress from local storage
     */
    function restoreProgress() {
        try {
            const saved = localStorage.getItem('quiz_progress');
            if (!saved) return;
            
            const data = JSON.parse(saved);
            
            // Check if progress is recent (within last hour)
            const hourAgo = Date.now() - (60 * 60 * 1000);
            if (data.timestamp < hourAgo) {
                localStorage.removeItem('quiz_progress');
                return;
            }
            
            // Restore answers
            for (let [key, value] of Object.entries(data.answers)) {
                const input = quizForm.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'radio') {
                        const radio = quizForm.querySelector(`[name="${key}"][value="${value}"]`);
                        if (radio) radio.checked = true;
                    } else {
                        input.value = value;
                    }
                }
            }
            
            console.log('Progress restored from local storage');
        } catch (e) {
            console.error('Failed to restore progress:', e);
        }
    }

    /**
     * Clear saved progress
     */
    function clearProgress() {
        try {
            localStorage.removeItem('quiz_progress');
        } catch (e) {
            console.error('Failed to clear progress:', e);
        }
    }

    // Start timer
    updateTimer();
    intervalId = setInterval(updateTimer, 1000);

    // Restore progress on page load
    restoreProgress();

    // Clear progress on successful submit
    quizForm.addEventListener('submit', function() {
        if (!hasSubmitted) {
            hasSubmitted = true;
            clearProgress();
        }
    });

    // Warn user before leaving page
    window.addEventListener('beforeunload', function(e) {
        if (!hasSubmitted) {
            e.preventDefault();
            e.returnValue = '';
            return 'Are you sure you want to leave? Your quiz progress may be lost.';
        }
    });

    // Handle visibility change (tab switching)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // User came back to tab - update timer immediately
            updateTimer();
        }
    });

})();

