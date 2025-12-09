from pydantic import BaseModel
from typing import List, Optional, Dict, Any
from app.models.question import QuestionType, Difficulty


class QuizGenerate(BaseModel):
    course_id: str  # UUID as string
    format: QuestionType
    difficulty: Difficulty
    question_count: int
    mixed_config: Optional[Dict[str, int]] = None  # For mixed format: {"mcq": 5, "essay": 2}


class QuestionOptionResponse(BaseModel):
    id: str  # UUID as string
    option_text: str
    option_letter: Optional[str] = None
    is_correct: bool


class QuestionResponse(BaseModel):
    id: str  # UUID as string
    type: QuestionType
    difficulty: Difficulty
    question_text: str
    options: List[QuestionOptionResponse]
    explanation: Optional[str] = None


class QuizSessionResponse(BaseModel):
    session_id: str  # UUID as string
    duration_minutes: int
    questions: List[QuestionResponse]


class AnswerSubmission(BaseModel):
    question_id: str  # UUID as string
    answer: Any  # Can be string, number, boolean, etc.


class QuizSubmit(BaseModel):
    answers: List[AnswerSubmission]


class QuestionResult(BaseModel):
    question_id: str  # UUID as string
    question_text: str
    correct_answer: Any
    student_answer: Any
    is_correct: bool
    explanation: Optional[str] = None
    feedback: Optional[str] = None
    question_type: Optional[str] = None  # Add question type for frontend


class QuizResults(BaseModel):
    session_id: str  # UUID as string
    score: float
    total_questions: int
    correct_answers: int
    results: List[QuestionResult]

