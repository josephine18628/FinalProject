from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.orm import Session
from sqlalchemy import or_
from typing import List, Optional
from app.database import get_db
from app.api.deps import get_current_admin
from app.models.user import User
from app.models.course import Course
from app.models.question import Question, QuestionOption, QuestionType, Difficulty
from app.models.ai_log import AIGenerationLog
from app.models.quiz_session import QuizSession
from app.schemas.question import QuestionCreate, QuestionUpdate, Question as QuestionSchema
from pydantic import BaseModel

router = APIRouter()


class CourseCreate(BaseModel):
    name: str
    code: str
    description: Optional[str] = None


class CourseUpdate(BaseModel):
    name: Optional[str] = None
    code: Optional[str] = None
    description: Optional[str] = None


class CourseResponse(BaseModel):
    id: str  # UUID as string
    name: str
    code: str
    description: Optional[str] = None
    created_at: str

    class Config:
        from_attributes = True


# Question CRUD
@router.get("/questions", response_model=List[QuestionSchema])
def list_questions(
    course_id: Optional[str] = Query(None),
    difficulty: Optional[Difficulty] = Query(None),
    question_type: Optional[QuestionType] = Query(None),
    is_ai_generated: Optional[bool] = Query(None),
    search: Optional[str] = Query(None),
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    query = db.query(Question)
    
    if course_id:
        query = query.filter(Question.course_id == course_id)
    if difficulty:
        query = query.filter(Question.difficulty == difficulty)
    if question_type:
        query = query.filter(Question.type == question_type)
    if is_ai_generated is not None:
        query = query.filter(Question.is_ai_generated == is_ai_generated)
    if search:
        # Case-insensitive search for MySQL
        from sqlalchemy import func
        search_lower = search.lower()
        query = query.filter(
            or_(
                func.lower(Question.question_text).like(f"%{search_lower}%"),
                func.lower(Question.explanation).like(f"%{search_lower}%")
            )
        )
    
    questions = query.all()
    return questions


@router.get("/questions/{question_id}", response_model=QuestionSchema)
def get_question(
    question_id: str,
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    question = db.query(Question).filter(Question.id == question_id).first()
    if not question:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Question not found"
        )
    return question


@router.post("/questions", response_model=QuestionSchema, status_code=status.HTTP_201_CREATED)
def create_question(
    question_data: QuestionCreate,
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    # Verify course exists
    course = db.query(Course).filter(Course.id == question_data.course_id).first()
    if not course:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Course not found"
        )
    
    question = Question(
        course_id=question_data.course_id,
        type=question_data.type,
        difficulty=question_data.difficulty,
        question_text=question_data.question_text,
        correct_answer=question_data.correct_answer,
        explanation=question_data.explanation,
        created_by_user_id=admin.id,
        is_ai_generated=False
    )
    db.add(question)
    db.flush()
    
    # Add options if provided
    if question_data.options:
        for opt_data in question_data.options:
            option = QuestionOption(
                question_id=question.id,
                option_text=opt_data.option_text,
                option_letter=opt_data.option_letter,
                is_correct=opt_data.is_correct
            )
            db.add(option)
    
    db.commit()
    db.refresh(question)
    return question


@router.put("/questions/{question_id}", response_model=QuestionSchema)
def update_question(
    question_id: str,
    question_data: QuestionUpdate,
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    question = db.query(Question).filter(Question.id == question_id).first()
    if not question:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Question not found"
        )
    
    # Update fields
    if question_data.course_id is not None:
        question.course_id = question_data.course_id
    if question_data.type is not None:
        question.type = question_data.type
    if question_data.difficulty is not None:
        question.difficulty = question_data.difficulty
    if question_data.question_text is not None:
        question.question_text = question_data.question_text
    if question_data.correct_answer is not None:
        question.correct_answer = question_data.correct_answer
    if question_data.explanation is not None:
        question.explanation = question_data.explanation
    
    # Update options if provided
    if question_data.options is not None:
        # Delete existing options
        db.query(QuestionOption).filter(QuestionOption.question_id == question_id).delete()
        # Add new options
        for opt_data in question_data.options:
            option = QuestionOption(
                question_id=question.id,
                option_text=opt_data.option_text,
                option_letter=opt_data.option_letter,
                is_correct=opt_data.is_correct
            )
            db.add(option)
    
    db.commit()
    db.refresh(question)
    return question


@router.delete("/questions/{question_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_question(
    question_id: str,
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    question = db.query(Question).filter(Question.id == question_id).first()
    if not question:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Question not found"
        )
    
    db.delete(question)
    db.commit()
    return None


# Course CRUD
@router.get("/courses", response_model=List[CourseResponse])
def list_courses(
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    courses = db.query(Course).all()
    return courses


@router.post("/courses", response_model=CourseResponse, status_code=status.HTTP_201_CREATED)
def create_course(
    course_data: CourseCreate,
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    # Check if code already exists
    # Check if code already exists
    existing = db.query(Course).filter(Course.code == course_data.code).first()
    if existing:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Course code already exists"
        )
    
    course = Course(
        name=course_data.name,
        code=course_data.code,
        description=course_data.description
    )
    db.add(course)
    db.commit()
    db.refresh(course)
    return course


@router.put("/courses/{course_id}", response_model=CourseResponse)
def update_course(
    course_id: str,
    course_data: CourseUpdate,
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    course = db.query(Course).filter(Course.id == course_id).first()
    if not course:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Course not found"
        )
    
    if course_data.name is not None:
        course.name = course_data.name
    if course_data.code is not None:
        # Check if new code conflicts
        existing = db.query(Course).filter(
            Course.code == course_data.code,
            Course.id != course_id
        ).first()
        if existing:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Course code already exists"
            )
        course.code = course_data.code
    if course_data.description is not None:
        course.description = course_data.description
    
    db.commit()
    db.refresh(course)
    return course


@router.delete("/courses/{course_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_course(
    course_id: str,
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    course = db.query(Course).filter(Course.id == course_id).first()
    if not course:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Course not found"
        )
    
    db.delete(course)
    db.commit()
    return None


# AI Generation Logs
@router.get("/logs")
def get_ai_logs(
    limit: int = Query(50, le=100),
    offset: int = Query(0, ge=0),
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    logs = db.query(AIGenerationLog).order_by(AIGenerationLog.created_at.desc()).offset(offset).limit(limit).all()
    total = db.query(AIGenerationLog).count()
    
    # Convert logs to dict format for JSON serialization
    logs_data = []
    for log in logs:
        logs_data.append({
            "id": str(log.id),
            "user_id": str(log.user_id),
            "course_id": str(log.course_id),
            "prompt_sent": log.prompt_sent,
            "response_received": log.response_received,
            "questions_generated": log.questions_generated,
            "questions_stored": log.questions_stored,
            "duplicates_found": log.duplicates_found,
            "created_at": log.created_at.isoformat() if hasattr(log.created_at, 'isoformat') else str(log.created_at)
        })
    
    return {
        "logs": logs_data,
        "total": total,
        "limit": limit,
        "offset": offset
    }


# Statistics
@router.get("/stats")
def get_statistics(
    db: Session = Depends(get_db),
    admin: User = Depends(get_current_admin)
):
    total_questions = db.query(Question).count()
    ai_questions = db.query(Question).filter(Question.is_ai_generated == True).count()
    manual_questions = db.query(Question).filter(Question.is_ai_generated == False).count()
    total_courses = db.query(Course).count()
    total_quizzes = db.query(QuizSession).count()
    total_users = db.query(User).count()
    
    return {
        "total_questions": total_questions,
        "ai_questions": ai_questions,
        "manual_questions": manual_questions,
        "total_courses": total_courses,
        "total_quizzes": total_quizzes,
        "total_users": total_users
    }

