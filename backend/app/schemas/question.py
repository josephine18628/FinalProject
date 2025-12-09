from pydantic import BaseModel
from typing import List, Optional, Any, Dict
from app.models.question import QuestionType, Difficulty


class QuestionOptionCreate(BaseModel):
    option_text: str
    option_letter: Optional[str] = None
    is_correct: bool


class QuestionOption(BaseModel):
    id: str  # UUID as string
    option_text: str
    option_letter: Optional[str] = None
    is_correct: bool

    class Config:
        from_attributes = True


class QuestionBase(BaseModel):
    course_id: str  # UUID as string
    type: QuestionType
    difficulty: Difficulty
    question_text: str
    correct_answer: Dict[str, Any]
    explanation: Optional[str] = None


class QuestionCreate(QuestionBase):
    options: Optional[List[QuestionOptionCreate]] = None


class QuestionUpdate(BaseModel):
    course_id: Optional[str] = None  # UUID as string
    type: Optional[QuestionType] = None
    difficulty: Optional[Difficulty] = None
    question_text: Optional[str] = None
    correct_answer: Optional[Dict[str, Any]] = None
    explanation: Optional[str] = None
    options: Optional[List[QuestionOptionCreate]] = None


class Question(QuestionBase):
    id: str  # UUID as string
    is_ai_generated: bool
    created_at: str
    updated_at: Optional[str] = None
    options: List[QuestionOption] = []

    class Config:
        from_attributes = True

