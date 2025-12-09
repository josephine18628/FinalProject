# Quick Setup Guide

## Prerequisites
- Python 3.8+
- Node.js 16+
- PostgreSQL 15+ (or Docker)

## Quick Start

### 1. Start Database
```bash
docker-compose up -d
```

### 2. Backend Setup
```bash
cd backend
python -m venv venv
# On Windows:
venv\Scripts\activate
# On Unix/Mac:
source venv/bin/activate

pip install -r requirements.txt
cp .env.example .env
# Edit .env with your API keys

# Run migrations (after setting up .env)
alembic revision --autogenerate -m "Initial migration"
alembic upgrade head

# Start server
uvicorn app.main:app --reload --port 8000
```

### 3. Frontend Setup
```bash
cd frontend
npm install
# Create .env file with REACT_APP_API_URL=http://localhost:8000
npm start
```

## First Steps

1. **Create Admin Account**: Use POST `/auth/admin-register` endpoint
2. **Create Courses**: Login as admin and create courses in the admin dashboard
3. **Student Registration**: Students can register at `/register`
4. **Generate Quiz**: Students can create quizzes from the dashboard

## Important Notes

- Ensure OpenRouter API key is set in backend/.env
- Embedding API key is optional but recommended for deduplication
- JWT_SECRET should be changed in production
- Database migrations must be run before starting the server

