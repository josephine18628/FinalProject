# CS3 Quiz Web App - Full Project Development Plan

## Project Structure

```
cs3-quiz-app/
├── backend/
│   ├── app/
│   │   ├── __init__.py
│   │   ├── main.py                 # FastAPI app entry point
│   │   ├── config.py               # Configuration settings
│   │   ├── database.py             # Database connection & session
│   │   ├── models/                 # SQLAlchemy models
│   │   │   ├── __init__.py
│   │   │   ├── user.py
│   │   │   ├── course.py
│   │   │   ├── question.py
│   │   │   ├── quiz_session.py
│   │   │   └── ai_log.py
│   │   ├── schemas/                # Pydantic schemas
│   │   │   ├── __init__.py
│   │   │   ├── user.py
│   │   │   ├── quiz.py
│   │   │   └── question.py
│   │   ├── api/
│   │   │   ├── __init__.py
│   │   │   ├── deps.py             # Dependencies (auth, db)
│   │   │   ├── routes/
│   │   │   │   ├── __init__.py
│   │   │   │   ├── auth.py         # Login, register
│   │   │   │   ├── quiz.py         # Quiz generation, taking
│   │   │   │   ├── questions.py    # Question CRUD
│   │   │   │   ├── courses.py      # Course CRUD
│   │   │   │   ├── admin.py        # Admin dashboard endpoints
│   │   │   │   └── grading.py      # Grading logic
│   │   ├── services/
│   │   │   ├── __init__.py
│   │   │   ├── ai_service.py       # OpenRouter integration
│   │   │   ├── deduplication.py    # Question deduplication
│   │   │   └── grading_service.py  # Automated grading
│   │   └── utils/
│   │       ├── __init__.py
│   │       ├── embeddings.py       # Semantic similarity
│   │       └── jwt.py              # JWT utilities
│   ├── alembic/                    # Database migrations
│   ├── requirements.txt
│   └── .env.example
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── components/
│   │   │   ├── auth/
│   │   │   │   ├── Login.jsx
│   │   │   │   └── Register.jsx
│   │   │   ├── quiz/
│   │   │   │   ├── QuizBuilder.jsx
│   │   │   │   ├── QuizTimer.jsx
│   │   │   │   ├── QuizQuestion.jsx
│   │   │   │   └── QuizResults.jsx
│   │   │   ├── admin/
│   │   │   │   ├── AdminDashboard.jsx
│   │   │   │   ├── QuestionManager.jsx
│   │   │   │   ├── CourseManager.jsx
│   │   │   │   └── QuizManager.jsx
│   │   │   └── common/
│   │   │       ├── Navbar.jsx
│   │   │       └── ProtectedRoute.jsx
│   │   ├── pages/
│   │   │   ├── Dashboard.jsx
│   │   │   ├── QuizPage.jsx
│   │   │   └── AdminPage.jsx
│   │   ├── services/
│   │   │   ├── api.js              # API client
│   │   │   └── auth.js             # Auth utilities
│   │   ├── context/
│   │   │   └── AuthContext.jsx
│   │   ├── hooks/
│   │   │   └── useAuth.js
│   │   ├── App.jsx
│   │   └── index.js
│   ├── package.json
│   ├── tailwind.config.js
│   └── postcss.config.js
├── database/
│   └── init.sql                    # Initial schema (optional)
├── docker-compose.yml              # PostgreSQL + app containers
├── README.md
└── .gitignore
```

## Database Schema

### Core Tables

**users**

- id (PK, UUID)
- email (unique)
- password_hash
- role (student/admin)
- created_at
- updated_at

**courses**

- id (PK, UUID)
- name
- code (e.g., "CS301")
- description
- created_at

**questions**

- id (PK, UUID)
- course_id (FK → courses)
- type (mcq/tf/essay/calculation/mixed)
- difficulty (beginner/intermediate/advanced)
- question_text
- correct_answer (JSON - varies by type)
- explanation
- created_by_user_id (FK → users, nullable)
- is_ai_generated (boolean)
- created_at
- updated_at

**question_options**

- id (PK, UUID)
- question_id (FK → questions)
- option_text
- option_letter (A, B, C, D for MCQ)
- is_correct (boolean, for MCQ/TF)

**quiz_sessions**

- id (PK, UUID)
- student_id (FK → users)
- course_id (FK → courses)
- config (JSON - format, difficulty, question_count)
- duration_minutes
- started_at
- completed_at
- status (pending/in_progress/completed)
- score (nullable until graded)

**quiz_responses**

- id (PK, UUID)
- quiz_session_id (FK → quiz_sessions)
- question_id (FK → questions)
- student_answer (JSON)
- is_correct (boolean)
- points_earned
- feedback (text, nullable)

**ai_generation_logs**

- id (PK, UUID)
- user_id (FK → users)
- course_id (FK → courses)
- prompt_sent (text)
- response_received (JSON)
- questions_generated (int)
- questions_stored (int)
- duplicates_found (int)
- created_at

## Backend Implementation

### FastAPI Application Structure

**main.py**

- FastAPI app initialization
- CORS configuration
- Router includes
- Exception handlers

**config.py**

- Environment variables (DB URL, JWT secret, OpenRouter API key)
- Settings class using Pydantic BaseSettings

**database.py**

- SQLAlchemy engine and session management
- Database dependency for routes

### Authentication & Authorization

**api/deps.py**

- `get_current_user()` - JWT token validation
- `get_current_admin()` - Admin role check
- `get_db()` - Database session dependency

**api/routes/auth.py**

- POST `/auth/register` - Student self-registration
- POST `/auth/login` - JWT token generation
- POST `/auth/admin-register` - Admin registration (temporary endpoint)
- GET `/auth/me` - Get current user

### Quiz Generation & Taking

**api/routes/quiz.py**

- POST `/quiz/generate` - Generate quiz questions
  - Input: course_id, format, difficulty, question_count
  - Calls AI service + deduplication
  - Returns: quiz_session_id, questions, duration
- GET `/quiz/{session_id}` - Get quiz details
- POST `/quiz/{session_id}/submit` - Submit answers
  - Triggers automatic grading
  - Returns detailed results

**services/ai_service.py**

- `generate_questions()` - OpenRouter API calls
- Structured JSON prompt with textbook-based requirements
- Duration calculation based on question type/difficulty
- Error handling and retries

**services/deduplication.py**

- `check_duplicate()` - Embedding-based similarity
- Uses API service for embeddings (e.g., OpenAI, Hugging Face)
- Cosine similarity threshold (0.90)
- Returns existing question if duplicate found

### Grading System

**services/grading_service.py**

- `grade_quiz()` - Main grading orchestrator
- `grade_mcq()` - Exact match
- `grade_tf()` - Boolean match
- `grade_calculation()` - Numeric tolerance (configurable)
- `grade_essay()` - LLM-based rubric scoring via OpenRouter

**api/routes/grading.py**

- POST `/grading/essay` - Essay grading endpoint

### Admin Dashboard API

**api/routes/admin.py**

- GET `/admin/questions` - List all questions (filterable)
  - Query params: course_id, difficulty, type, is_ai_generated, search
- GET `/admin/questions/{id}` - Get question details
- PUT `/admin/questions/{id}` - Edit question
- POST `/admin/questions` - Create manual question
- DELETE `/admin/questions/{id}` - Delete question
- GET `/admin/courses` - List courses
- POST `/admin/courses` - Create course
- PUT `/admin/courses/{id}` - Update course
- DELETE `/admin/courses/{id}` - Delete course
- GET `/admin/logs` - AI generation logs
- GET `/admin/stats` - Dashboard statistics

### CRUD Endpoints

**api/routes/questions.py**

- Student-facing question retrieval (filtered, no editing)

**api/routes/courses.py**

- Public course listing

## Frontend Implementation

### Authentication

**components/auth/Login.jsx**

- Email/password form
- JWT token storage
- Redirect based on role

**components/auth/Register.jsx**

- Student registration form
- Password validation
- Auto-login after registration

**context/AuthContext.jsx**

- Global auth state
- Token management
- User role checking

### Student Dashboard

**pages/Dashboard.jsx**

- Quiz builder form:
  - Course selection (dropdown)
  - Question format (radio/checkboxes for mixed)
  - Difficulty (radio)
  - Question count (number input)
- Display past quiz results
- Start new quiz button

**components/quiz/QuizBuilder.jsx**

- Form validation
- Submit to `/quiz/generate`
- Navigate to QuizPage on success

### Quiz Taking

**pages/QuizPage.jsx**

- Timer display (countdown)
- Question rendering based on type
- Answer collection
- Auto-submit on timer expiry
- Submit button

**components/quiz/QuizTimer.jsx**

- Countdown timer component
- Warning at 1 minute remaining
- Auto-submit callback

**components/quiz/QuizQuestion.jsx**

- Render MCQ (radio buttons)
- Render True/False (toggle)
- Render Essay (textarea)
- Render Calculation (number input)
- Conditional rendering by type

**components/quiz/QuizResults.jsx**

- Score display
- Correct/incorrect breakdown
- Show correct answers
- Display explanations
- Highlight mistakes
- Return to dashboard button

### Admin Dashboard

**pages/AdminPage.jsx**

- Navigation tabs:
  - Questions
  - Courses
  - Analytics/Logs
- Overview statistics cards

**components/admin/QuestionManager.jsx**

- Data table with filters:
  - Course filter
  - Difficulty filter
  - Type filter
  - AI/Manual toggle
  - Search bar
- CRUD actions:
  - Edit modal
  - Delete confirmation
  - Add new question form
- Pagination

**components/admin/CourseManager.jsx**

- List courses
- Add/Edit/Delete courses
- Course form modal

**components/admin/QuizManager.jsx** (Bonus)

- Create quizzes for students
- Assign to specific students
- Schedule quizzes

### Routing & Protection

**App.jsx**

- React Router setup
- Protected routes:
  - `/dashboard` - Students only
  - `/quiz/:sessionId` - Students only
  - `/admin/*` - Admin only
- Public routes:
  - `/login`
  - `/register`
  - `/admin-register` (temporary)

**components/common/ProtectedRoute.jsx**

- Role-based access control
- Redirect to login if unauthorized

### API Client

**services/api.js**

- Axios instance with base URL
- Request interceptor (add JWT token)
- Response interceptor (handle 401)
- API methods for all endpoints

## AI Integration

### OpenRouter Configuration

**Prompt Template** (in `services/ai_service.py`):

```
You are an expert computer science educator. Generate quiz questions based on 
reliable textbook knowledge from standard CS3 curriculum textbooks.

Requirements:
- Use only well-established CS concepts (no fictional content)
- Questions must be accurate and clear
- Provide detailed explanations

Generate {count} {format} questions for {course} at {difficulty} level.

Calculate quiz duration considering:
- Question type complexity
- Cognitive load
- Standard reading/thinking time

Output strict JSON format:
{
  "duration_minutes": <number>,
  "questions": [
    {
      "type": "<mcq|tf|essay|calculation>",
      "difficulty": "<beginner|intermediate|advanced>",
      "question": "<text>",
      "options": ["A", "B", "C", "D"],  // for MCQ
      "correct_answer": "<answer>",
      "explanation": "<detailed explanation>"
    }
  ]
}
```

### Semantic Similarity

**utils/embeddings.py**

- API client for embedding service (OpenAI embeddings or Hugging Face Inference)
- `get_embedding()` - Convert text to vector
- `cosine_similarity()` - Calculate similarity between vectors

## Configuration & Environment

### Environment Variables

**backend/.env**

```
DATABASE_URL=mysql+pymysql://root:@localhost:3306/cs3
JWT_SECRET=<generate-secure-secret>
JWT_ALGORITHM=HS256
OPENROUTER_API_KEY=<your-key>
OPENROUTER_MODEL=tngtech/deepseek-r1t2-chimera:free  # Default model
EMBEDDING_API_URL=<optional>
EMBEDDING_API_KEY=<optional>
```

**frontend/.env**

```
REACT_APP_API_URL=http://localhost:8000
```

## Implementation Steps

1. **Project Setup**

   - Initialize FastAPI backend with dependencies
   - Initialize React frontend with TailwindCSS
   - Set up PostgreSQL database
   - Create docker-compose.yml for local development

2. **Database Layer**

   - Define SQLAlchemy models
   - Create Alembic migrations
   - Seed initial data (courses)

3. **Authentication System**

   - Implement JWT utilities
   - Create auth routes (register, login)
   - Add admin registration endpoint
   - Build frontend auth components

4. **AI Integration**

   - Set up OpenRouter client
   - Implement question generation service
   - Create embedding service for deduplication
   - Build deduplication logic

5. **Quiz Generation**

   - Create quiz generation endpoint
   - Integrate AI service + deduplication
   - Return structured quiz data

6. **Quiz Taking Interface**

   - Build quiz builder form
   - Create quiz taking page with timer
   - Implement question rendering by type
   - Handle answer submission

7. **Grading System**

   - Implement automatic grading for MCQ/TF/Calculation
   - Build essay grading via LLM
   - Create results display component

8. **Admin Dashboard**

   - Build question management UI
   - Implement CRUD endpoints for questions
   - Create course management
   - Add filtering and search
   - Display AI generation logs

9. **Testing & Polish**

   - Test all user flows
   - Error handling improvements
   - UI/UX refinements
   - Documentation

## Key Technical Decisions

- **JWT Authentication**: Stateless, role-based access control
- **SQLAlchemy ORM**: Type-safe database interactions
- **Pydantic Schemas**: Request/response validation
- **Alembic Migrations**: Version-controlled database schema
- **OpenRouter API**: Flexible LLM provider with multiple model options
- **Embedding API**: External service for semantic similarity (avoids local model download)
- **React Context**: Global auth state management
- **TailwindCSS**: Utility-first styling for rapid UI development
- **Axios**: HTTP client with interceptors for auth

## API Endpoints Summary

### Public

- `POST /auth/register` - Student registration
- `POST /auth/login` - Login
- `POST /auth/admin-register` - Admin registration (temporary)

### Student

- `GET /quiz/{session_id}` - Get quiz
- `POST /quiz/generate` - Generate new quiz
- `POST /quiz/{session_id}/submit` - Submit answers

### Admin

- `GET /admin/questions` - List questions (filtered)
- `POST /admin/questions` - Create question
- `PUT /admin/questions/{id}` - Update question
- `DELETE /admin/questions/{id}` - Delete question
- `GET /admin/courses` - List courses
- `POST /admin/courses` - Create course
- `PUT /admin/courses/{id}` - Update course
- `DELETE /admin/courses/{id}` - Delete course
- `GET /admin/logs` - AI generation logs
- `GET /admin/stats` - Statistics

This plan provides a complete foundation for building the CS3 Quiz Web Application with all specified features.