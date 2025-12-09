from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List, Optional
from app.database import get_db
from app.models.question import Question, QuestionType, Difficulty
from app.schemas.question import Question as QuestionSchema

router = APIRouter()


@router.get("/", response_model=List[QuestionSchema])
def list_questions(
    course_id: Optional[str] = None,
    difficulty: Optional[Difficulty] = None,
    question_type: Optional[QuestionType] = None,
    db: Session = Depends(get_db)
):
    query = db.query(Question)
    
    if course_id:
        query = query.filter(Question.course_id == course_id)
    if difficulty:
        query = query.filter(Question.difficulty == difficulty)
    if question_type:
        query = query.filter(Question.type == question_type)
    
    questions = query.all()
    return questions

