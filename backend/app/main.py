from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api.routes import auth, quiz, admin, courses, questions
from app.config import settings

app = FastAPI(title="CS3 Quiz API", version="1.0.0")

# CORS configuration
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:3000", "http://localhost:5173"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include routers
app.include_router(auth.router, prefix="/auth", tags=["auth"])
app.include_router(quiz.router, prefix="/quiz", tags=["quiz"])
app.include_router(admin.router, prefix="/admin", tags=["admin"])
app.include_router(courses.router, prefix="/courses", tags=["courses"])
app.include_router(questions.router, prefix="/questions", tags=["questions"])


@app.get("/")
def root():
    return {"message": "CS3 Quiz API"}


@app.get("/health")
def health_check():
    return {"status": "healthy"}

