# CS3 Quiz Web Application

A full-stack web application for third-year computer science students to practice quizzes. The app generates questions using AI (OpenRouter) with textbook-based knowledge, includes deduplication logic, and provides an admin dashboard for managing questions and courses.

## Tech Stack

- **Frontend**: React, TailwindCSS
- **Backend**: FastAPI, Python
- **Database**: PostgreSQL
- **AI Integration**: OpenRouter (LLM)
- **Authentication**: JWT

## Features

- **AI-Generated Quizzes**: Generate questions using OpenRouter LLM
- **Question Deduplication**: Semantic similarity checking to prevent duplicate questions
- **Multiple Question Formats**: MCQ, True/False, Essay, Calculation, and Mixed
- **Automatic Grading**: Grading for MCQ, True/False, and Calculation questions
- **AI Essay Grading**: LLM-based rubric scoring for essay questions
- **Admin Dashboard**: Full CRUD operations for questions, courses, and analytics
- **Student Dashboard**: Create and take quizzes with timer functionality

## Prerequisites

- Python 3.8+
- Node.js 16+
- PostgreSQL 15+
- Docker (optional, for PostgreSQL)

## Setup Instructions

### 1. Backend Setup

1. Navigate to the backend directory:
```bash
cd backend
```

2. Create a virtual environment:
```bash
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

3. Install dependencies:
```bash
pip install -r requirements.txt
```

4. Set up environment variables:
```bash
cp .env.example .env
```

Edit `.env` with your configuration:
```
DATABASE_URL=mysql+pymysql://root:@localhost:3306/cs3
JWT_SECRET=your-secret-key-change-this
OPENROUTER_API_KEY=your-openrouter-api-key
OPENROUTER_MODEL=tngtech/deepseek-r1t2-chimera:free
EMBEDDING_API_URL=https://api.openai.com/v1/embeddings
EMBEDDING_API_KEY=your-embedding-api-key
EMBEDDING_MODEL=text-embedding-ada-002
```

5. Start PostgreSQL (using Docker):
```bash
docker-compose up -d
```

Or use your own PostgreSQL instance.

6. Run database migrations:
```bash
# Initialize Alembic (if not already done)
alembic revision --autogenerate -m "Initial migration"
alembic upgrade head
```

7. Start the FastAPI server:
```bash
uvicorn app.main:app --reload --port 8000
```

### 2. Frontend Setup

1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Create `.env` file:
```
REACT_APP_API_URL=http://localhost:8000
```

4. Start the development server:
```bash
npm start
```

The frontend will be available at `http://localhost:3000`.

## Database Schema

The application uses the following main tables:

- **users**: User accounts (students and admins)
- **courses**: Course information
- **questions**: Question bank with metadata
- **question_options**: Options for MCQ and True/False questions
- **quiz_sessions**: Quiz session tracking
- **quiz_responses**: Student answers and grading results
- **ai_generation_logs**: AI generation tracking and analytics

## Usage

### Student Registration and Login

1. Navigate to the registration page
2. Create a student account
3. Login to access the dashboard

### Creating a Quiz

1. Click "Create New Quiz" on the dashboard
2. Select:
   - Course
   - Question format (MCQ, True/False, Essay, Calculation, or Mixed)
   - Difficulty level (Beginner, Intermediate, Advanced)
   - Number of questions
3. Click "Generate Quiz"
4. The AI will generate questions and calculate duration
5. Start the quiz and answer questions
6. Submit to receive automatic grading and feedback

### Admin Dashboard

1. Create an admin account using the `/auth/admin-register` endpoint (temporary endpoint)
2. Login as admin
3. Access the admin dashboard with:
   - **Statistics**: Overview of questions, courses, quizzes, and users
   - **Question Management**: CRUD operations with filtering
   - **Course Management**: CRUD operations for courses
   - **AI Logs**: View AI generation history and analytics

## API Endpoints

### Public Endpoints
- `POST /auth/register` - Student registration
- `POST /auth/login` - Login
- `POST /auth/admin-register` - Admin registration (temporary)

### Student Endpoints
- `GET /quiz/{session_id}` - Get quiz details
- `POST /quiz/generate` - Generate new quiz
- `POST /quiz/{session_id}/start` - Start quiz
- `POST /quiz/{session_id}/submit` - Submit answers

### Admin Endpoints
- `GET /admin/questions` - List questions (with filters)
- `POST /admin/questions` - Create question
- `PUT /admin/questions/{id}` - Update question
- `DELETE /admin/questions/{id}` - Delete question
- `GET /admin/courses` - List courses
- `POST /admin/courses` - Create course
- `PUT /admin/courses/{id}` - Update course
- `DELETE /admin/courses/{id}` - Delete course
- `GET /admin/logs` - AI generation logs
- `GET /admin/stats` - Dashboard statistics

## Project Structure

```
cs3-quiz-app/
├── backend/
│   ├── app/
│   │   ├── api/routes/      # API route handlers
│   │   ├── models/          # SQLAlchemy models
│   │   ├── schemas/         # Pydantic schemas
│   │   ├── services/        # Business logic (AI, grading, deduplication)
│   │   └── utils/           # Utilities (JWT, embeddings)
│   ├── alembic/             # Database migrations
│   └── requirements.txt
├── frontend/
│   ├── src/
│   │   ├── components/      # React components
│   │   ├── pages/           # Page components
│   │   ├── services/        # API client
│   │   └── context/         # React context
│   └── package.json
└── docker-compose.yml       # PostgreSQL setup
```

## Key Features Implementation

### Question Deduplication

The system uses semantic similarity (embedding-based cosine similarity) to detect duplicates:
- Threshold: 0.90 cosine similarity
- Uses external embedding API (OpenAI or Hugging Face)
- Checks both exact matches and semantic similarity

### AI Question Generation

- Uses OpenRouter API for LLM access
- Structured JSON prompts ensure accuracy
- Textbook-based knowledge constraints
- Automatic duration calculation based on question type and difficulty

### Grading System

- **MCQ/True-False**: Exact match comparison
- **Calculation**: Numeric tolerance (configurable)
- **Essay**: LLM-based rubric scoring via OpenRouter

## Environment Variables

### Backend (.env)
- `DATABASE_URL`: PostgreSQL connection string
- `JWT_SECRET`: Secret key for JWT tokens
- `OPENROUTER_API_KEY`: OpenRouter API key
- `OPENROUTER_MODEL`: Model to use (default: tngtech/deepseek-r1t2-chimera:free)
- `EMBEDDING_API_URL`: Embedding service URL
- `EMBEDDING_API_KEY`: Embedding service API key
- `EMBEDDING_MODEL`: Embedding model name

### Frontend (.env)
- `REACT_APP_API_URL`: Backend API URL

## Troubleshooting

1. **Database Connection Issues**: Ensure PostgreSQL is running and DATABASE_URL is correct
2. **Migration Errors**: Run `alembic upgrade head` to apply migrations
3. **OpenRouter API Errors**: Verify API key and model name in .env
4. **CORS Issues**: Check CORS settings in `backend/app/main.py`

## License

This project is for educational purposes.

