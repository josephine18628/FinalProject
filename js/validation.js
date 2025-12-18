/**
 * Form Validation JavaScript
 * CS3 Quiz Platform
 */

document.addEventListener('DOMContentLoaded', function() {
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (username === '' || password === '') {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
        });
    }

    // Register form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrengthDiv = document.getElementById('passwordStrength');

        // Real-time password strength checker
        if (passwordInput && passwordStrengthDiv) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                
                passwordStrengthDiv.className = 'password-strength ' + strength.class;
                passwordStrengthDiv.textContent = strength.text;
            });
        }

        // Form submission validation
        registerForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Check if all fields are filled
            if (username === '' || email === '' || password === '' || confirmPassword === '') {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }

            // Validate username format
            const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
            if (!usernameRegex.test(username)) {
                e.preventDefault();
                alert('Username must be 3-20 characters with letters, numbers, and underscores only');
                return false;
            }

            // Validate email format
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }

            // Validate password strength
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return false;
            }

            if (!/[A-Za-z]/.test(password)) {
                e.preventDefault();
                alert('Password must contain at least one letter');
                return false;
            }

            if (!/[0-9]/.test(password)) {
                e.preventDefault();
                alert('Password must contain at least one number');
                return false;
            }

            // Check if passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
        });
    }

    // Auto-hide flash messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
});

/**
 * Check password strength
 * @param {string} password Password to check
 * @returns {object} Strength object with class and text
 */
function checkPasswordStrength(password) {
    let strength = 0;
    const result = { class: '', text: '' };

    if (password.length === 0) {
        return result;
    }

    // Length check
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;

    // Character variety checks
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    // Determine strength level
    if (strength <= 2) {
        result.class = 'weak';
        result.text = 'Weak password';
    } else if (strength <= 4) {
        result.class = 'medium';
        result.text = 'Medium password';
    } else {
        result.class = 'strong';
        result.text = 'Strong password';
    }

    return result;
}

/**
 * Validate email format
 * @param {string} email Email to validate
 * @returns {boolean} True if valid, false otherwise
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

