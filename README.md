Web App Link:
https://josephineallan.rf.gd

# CS3 Quiz Platform

A dynamic web-based quiz application for third-year Computer Science students, featuring AI-powered question generation, intelligent grading, and comprehensive quiz management.

## 🎯 Overview

The CS3 Quiz Platform is designed to help Computer Science students practice and assess their knowledge across 11 core courses. The platform uses Google's Gemini AI to generate contextually relevant questions from course textbooks and provides intelligent, content-based grading for essay and calculation questions.

## ✨ Key Features

### 🤖 AI-Powered Quiz Generation
- Integration with Google Gemini AI for dynamic question generation
- Questions generated from official course textbooks
- Support for multiple question formats:
  - Multiple Choice Questions (MCQ)
  - True/False Questions
  - Essay Questions
  - Calculation Problems
  - Mixed Format Quizzes

### 📚 Course Coverage
The platform covers 11 Computer Science courses:
1. Hardware Systems
2. Web Technologies
3. C++ Programming
4. Algorithms & Data Structures
5. Research Methods
6. Modeling & Simulation
7. Software Engineering
8. Computer Architecture
9. Operating Systems
10. Database Systems
11. Computer Networks

### 🎓 Topic-Based Learning
- Comprehensive topic outlines for each course (based on textbook chapters)
- Select specific topics for focused practice
- Topics organized by categories for easy navigation

### 🧠 Intelligent Grading System
**Per-Question Grading with Content-Based Analysis:**

| Match Range | Score | Description |
|-------------|-------|-------------|
| >80% | 100% | Full Marks - Contains all/nearly all key concepts |
| 50-80% | 50% | Half Marks - Contains main concepts |
| 20-50% | 25% | Quarter Marks - Shows some understanding |
| <20% | 10% | Minimal Credit - Limited coverage |

**For Calculation Questions:**
- Correct answer → 100%
- Correct method, wrong answer → 50%
- Partial method → 25%
- Show your working to earn partial credit!

### 💾 Question Bank System
- Generated questions saved to database
- Questions reused across users to reduce API calls
- Track user history to prevent repeat questions
- Smart question selection algorithm

### ⏱️ Quiz Features
- Configurable difficulty levels (Beginner, Intermediate, Advanced)
- Adjustable number of questions (1-50)
- Built-in timer with visual countdown
- Auto-submission when time expires
- Retake quiz option from results page

### 📊 Results & Analytics
- Detailed feedback for each question
- Content coverage analysis showing matched concepts
- Side-by-side comparison with model answers
- Overall quiz statistics
- Performance tracking across attempts

### 🎨 Modern UI/UX
- Wayground-inspired design
- Green, white, and orange color scheme
- Responsive layout for all devices
- Smooth animations and transitions
- Intuitive navigation

## 🛠️ Technology Stack

### Backend
- **PHP 8.x** - Server-side logic
- **MySQL** - Database management
- **Apache** - Web server (via XAMPP)

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling with custom variables
- **JavaScript (Vanilla)** - Client-side functionality

### APIs & Services
- **Google Gemini AI** - Question generation
- **Gemini 2.5 Flash** - Current model (December 2025)

### Development Tools
- **XAMPP** - Local development environment
- **Git** - Version control

## 📋 Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache web server
- XAMPP (recommended) or similar local server environment
- Google Gemini API key

## 🚀 Installation

### 1. Clone or Download the Project
```bash
git clone <repository-url>
cd Individual-Project
```

### 2. Set Up Database
```bash
# Start XAMPP and ensure MySQL is running
# Open phpMyAdmin (http://localhost/phpmyadmin)
# Import the database schema
```

Run the database setup file:
```bash
# Using command line
mysql -u root < database.sql

# OR import via phpMyAdmin
# 1. Open http://localhost/phpmyadmin
# 2. Click "Import" tab
# 3. Choose database.sql file
# 4. Click "Go"
```

This will create:
- Database: `cs3_quiz_platform`
- All required tables with indexes
- Demo user account (username: demo, password: demo123)

### 3. Configure Database Connection

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cs3_quiz_platform');
define('DB_USER', 'root');
define('DB_PASS', ''); // Your MySQL password
```

### 4. Configure Gemini API

Edit `config/gemini.php`:
```php
define('GEMINI_API_KEY', 'your-api-key-here');
```

Get your API key from: https://makersuite.google.com/app/apikey

### 5. Set Up Apache

Ensure your Apache DocumentRoot points to the project directory. The application will automatically redirect to the `src/` folder:
```
http://localhost/Individual%20Project/
```

Or access directly:
```
http://localhost/Individual%20Project/src/
```

### 6. File Permissions

Ensure the `logs/` directory is writable:
```bash
chmod -R 755 logs/
```

## 📁 Project Structure

```
Individual-Project/
├── src/                   # All PHP source code
│   ├── api/              # API endpoints
│   │   ├── generate-quiz.php   # Quiz generation
│   │   ├── submit-quiz.php     # Quiz submission & grading
│   │   ├── login.php          # User authentication
│   │   ├── register.php       # User registration
│   │   └── logout.php         # Session logout
│   ├── config/           # Configuration files
│   │   ├── database.php       # Database connection
│   │   ├── gemini.php         # Gemini AI configuration
│   │   ├── courses.php        # Course definitions
│   │   └── topics.php         # Course topics outline
│   ├── includes/         # Helper functions
│   │   ├── functions.php      # Utility functions
│   │   ├── question-bank-functions.php  # Question bank logic
│   │   ├── similarity-functions.php     # Grading algorithms
│   │   ├── header.php         # Common header
│   │   └── footer.php         # Common footer
│   ├── index.php         # Landing page
│   ├── register.php      # Registration page
│   ├── dashboard.php     # Course selection
│   ├── quiz-config.php   # Quiz configuration
│   ├── quiz.php          # Active quiz interface
│   ├── results.php       # Quiz results display
│   ├── test-gemini-api.php      # API testing tool
│   ├── test-question-bank.php   # Question bank testing
│   ├── diagnose-api.php         # API diagnostics
│   └── run-migration.php        # Database migration runner
├── css/                  # Stylesheets
│   ├── style.css         # Main styles
│   ├── quiz.css          # Quiz-specific styles
│   └── responsive.css    # Mobile responsive
├── js/                   # JavaScript files
│   ├── timer.js          # Quiz timer
│   └── validation.js     # Form validation
├── logs/                 # Application logs
├── index.php             # Root redirect to src/
└── database.sql          # Complete database schema (all features included)
```

## 🎮 Usage

### 1. Registration & Login
1. Navigate to `http://localhost/Individual%20Project/`
2. Click "Get Started" or select a course
3. Register with your details or login if you have an account

### 2. Select a Course
- View all 11 available courses on the dashboard
- Click on any course card to start configuring a quiz

### 3. Configure Your Quiz
**Step 1: Select Topics**
- Choose from categorized topics based on textbook chapters
- Use "Select All" or pick specific topics
- See live counter of selected topics

**Step 2: Question Format**
- Multiple Choice Questions (MCQ)
- True/False
- Essay Questions
- Calculation Problems
- All Types (Mixed)

**Step 3: Difficulty Level**
- Beginner - Basic concepts
- Intermediate - Standard exam level
- Advanced - Complex problems

**Step 4: Number of Questions**
- Single format: 1-50 questions
- Mixed format: Specify count per type

### 4. Take the Quiz
- Read each question carefully
- For calculations: Show your working to earn partial credit
- Monitor the countdown timer
- Submit before time runs out or quiz auto-submits

### 5. View Results
- See your overall score
- Review each question with detailed feedback
- Compare your answers with model answers
- See content coverage analysis
- Retake quiz if desired

## 🔧 Configuration Options

### Gemini API Settings
File: `config/gemini.php`
```php
define('GEMINI_API_KEY', 'your-key');
define('GEMINI_API_URL', 'v1beta/models/gemini-2.5-flash');
```

### Grading Thresholds
File: `includes/similarity-functions.php`
```php
// Modify these values to adjust grading strictness
if ($coveragePercent > 80) {
    $finalScore = 100;  // Full marks threshold
} elseif ($coveragePercent >= 50) {
    $finalScore = 50;   // Half marks threshold
} elseif ($coveragePercent >= 20) {
    $finalScore = 25;   // Quarter marks threshold
}
```

### Timer Duration
Default quiz duration is calculated based on question count and type. Modify in `quiz.php`:
```php
// 2 minutes per MCQ/True-False, 5 minutes per essay/calculation
$timePerQuestion = ($questionType === 'mcq' || $questionType === 'true_false') ? 2 : 5;
$totalMinutes = $numQuestions * $timePerQuestion;
```

## 🗄️ Database Schema

### Main Tables

**users**
- User accounts and authentication

**quiz_attempts**
- Quiz sessions and overall scores

**quiz_responses**
- Individual question responses and grading

**question_bank**
- Generated questions for reuse

**user_question_history**
- Track which questions each user has seen

## 🎨 Customization

### Changing Color Scheme
Edit `css/style.css`:
```css
:root {
    --primary-color: #22c55e;      /* Green */
    --secondary-color: #ff6b35;    /* Orange */
    --accent-color: #10b981;       /* Bright Green */
}
```

### Adding New Courses
1. Edit `config/courses.php` - Add course definition
2. Edit `config/topics.php` - Add course topics
3. Course will automatically appear on dashboard

## 🧪 Testing

### Test Gemini API Connection
```bash
php test-gemini-api.php
```

### Test Question Bank
```bash
php test-question-bank.php
```

### Test Database Connection
Access: `http://localhost/Individual%20Project/diagnose-api.php`

## 🔒 Security Features

- **Password Hashing** - Using PHP's `password_hash()`
- **SQL Injection Prevention** - PDO prepared statements
- **CSRF Protection** - Token-based validation
- **Session Security** - Secure session handling
- **Input Sanitization** - All user inputs sanitized
- **XSS Prevention** - Output escaping with `htmlspecialchars()`

## 📊 Key Algorithms

### Content Coverage Analysis
1. Extract key concepts (4+ character words) from model answer
2. Check presence in user answer
3. Calculate match percentage: `(found concepts / total concepts) × 100`
4. Apply grading thresholds per question

### Question Bank Selection
1. Check database for unused questions matching criteria
2. Exclude questions user has already seen
3. If insufficient questions, generate new ones via Gemini AI
4. Save new questions to bank for future use

## 🐛 Troubleshooting

### Gemini API Errors
- Verify API key is correct
- Check API is enabled in Google Cloud Console
- Ensure using correct model version
- Check internet connectivity

### Database Connection Issues
- Verify MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Ensure database exists and migrations are run

### Quiz Timer Not Working
- Clear browser cache (Ctrl + Shift + R)
- Check browser console for JavaScript errors
- Verify `js/timer.js` is loading

### Questions Not Saving
- Check question bank migration is applied
- Verify database permissions
- Check PHP error logs in `logs/` directory

## 📝 API Endpoints

### POST `/api/generate-quiz.php`
Generate a new quiz
```json
{
    "course_id": "web_technologies",
    "question_format": "mcq",
    "difficulty": "intermediate",
    "num_questions": 10,
    "topics": ["HTML5", "CSS3", "JavaScript"]
}
```

### POST `/api/submit-quiz.php`
Submit quiz for grading
```json
{
    "answer_0": "User's answer",
    "answer_1": "User's answer",
    "working_1": "Calculation steps"
}
```

### POST `/api/login.php`
User authentication
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

## 🤝 Contributing

This is an academic project. For improvements or bug fixes:
1. Test thoroughly in local environment
2. Ensure all features work as expected
3. Document any changes made

## 📄 License

This project is created for academic purposes.

## 👥 Credits

- **Gemini AI** - Question generation
- **Wayground** - UI/UX inspiration
- Course materials based on standard CS textbooks

## 📞 Support

For issues or questions:
1. Check troubleshooting section
2. Review error logs in `logs/` directory
3. Test API connectivity with diagnostic tools

## 🔄 Version History

**Current Version: 2.1**
- Enhanced content-based grading (>80%, 50-80%, 20-50%, <20%)
- Per-question grading implementation
- Topic selection feature
- Question bank system
- Similarity-based grading for essays and calculations
- Retake quiz functionality
- Wayground-inspired UI redesign

---

**Built with ❤️ for Computer Science Education**

