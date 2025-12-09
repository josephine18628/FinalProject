from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from datetime import datetime
from app.database import get_db
from app.api.deps import get_current_user
from app.models.user import User
from app.models.course import Course
from app.models.question import Question, QuestionOption, QuestionType, Difficulty
from app.models.quiz_session import QuizSession, QuizResponse, QuizStatus
from app.models.ai_log import AIGenerationLog
from app.schemas.quiz import QuizGenerate, QuizSessionResponse, QuestionResponse, QuestionOptionResponse, QuizSubmit, QuizResults, QuestionResult
from app.services.ai_service import generate_questions
from app.services.deduplication import filter_duplicates, check_duplicate
from app.services.grading_service import grade_question, grade_essay
import json

router = APIRouter()


@router.post("/generate", response_model=QuizSessionResponse, status_code=status.HTTP_201_CREATED)
async def generate_quiz(
    quiz_config: QuizGenerate,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    # Verify course exists
    course = db.query(Course).filter(Course.id == quiz_config.course_id).first()
    if not course:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Course not found"
        )
    
    # Generate questions using AI
    try:
        ai_response = await generate_questions(
            course_name=course.name,
            format=quiz_config.format.value,
            difficulty=quiz_config.difficulty.value,
            question_count=quiz_config.question_count,
            mixed_config=quiz_config.mixed_config
        )
    except Exception as e:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Error generating questions: {str(e)}"
        )
    
    duration_minutes = ai_response.get("duration_minutes", 30)
    ai_questions = ai_response.get("questions", [])
    
    # Filter duplicates
    filtered_questions, duplicates_found = await filter_duplicates(
        ai_questions,
        db,
        str(quiz_config.course_id)
    )
    
    # Store questions in database
    stored_questions = []
    questions_stored_count = 0
    
    for q_data in filtered_questions:
        # Check again for duplicates (redundant but safe)
        duplicate = await check_duplicate(q_data["question"], db, str(quiz_config.course_id))
        if duplicate:
            stored_questions.append(duplicate)
            continue
        
        # Create question
        question = Question(
            course_id=quiz_config.course_id,
            type=QuestionType(q_data["type"]),
            difficulty=Difficulty(q_data["difficulty"]),
            question_text=q_data["question"],
            correct_answer={"answer": q_data["correct_answer"]},
            explanation=q_data.get("explanation", ""),
            is_ai_generated=True
        )
        db.add(question)
        db.flush()
        
        # Add options for MCQ and True/False
        if question.type == QuestionType.MCQ:
            options = q_data.get("options", [])
            letters = ["A", "B", "C", "D"]
            for i, opt_text in enumerate(options[:4]):
                is_correct = (letters[i] if i < len(letters) else str(i)) == q_data["correct_answer"].upper()
                option = QuestionOption(
                    question_id=question.id,
                    option_text=opt_text,
                    option_letter=letters[i] if i < len(letters) else None,
                    is_correct=is_correct
                )
                db.add(option)
        elif question.type == QuestionType.TRUE_FALSE:
            for opt_text, is_correct in [("True", q_data["correct_answer"].lower() == "true"), ("False", q_data["correct_answer"].lower() == "false")]:
                option = QuestionOption(
                    question_id=question.id,
                    option_text=opt_text,
                    is_correct=is_correct
                )
                db.add(option)
        
        stored_questions.append(question)
        questions_stored_count += 1
    
    # Store question IDs for the session (as strings for MySQL compatibility)
    question_ids = [str(q.id) for q in stored_questions]
    
    # Create quiz session
    quiz_session = QuizSession(
        student_id=current_user.id,
        course_id=quiz_config.course_id,
        config={
            "format": quiz_config.format.value,
            "difficulty": quiz_config.difficulty.value,
            "question_count": quiz_config.question_count,
            "mixed_config": quiz_config.mixed_config,
            "question_ids": question_ids  # Store question IDs as strings
        },
        duration_minutes=duration_minutes,
        status=QuizStatus.PENDING
    )
    db.add(quiz_session)
    
    # Log AI generation
    ai_log = AIGenerationLog(
        user_id=current_user.id,
        course_id=quiz_config.course_id,
        prompt_sent=json.dumps({
            "course": course.name,
            "format": quiz_config.format.value,
            "difficulty": quiz_config.difficulty.value,
            "count": quiz_config.question_count
        }),
        response_received=ai_response,
        questions_generated=len(ai_questions),
        questions_stored=questions_stored_count,
        duplicates_found=duplicates_found
    )
    db.add(ai_log)
    
    db.commit()
    db.refresh(quiz_session)
    
    # Format response
    question_responses = []
    for q in stored_questions:
        options = [
            QuestionOptionResponse(
                id=opt.id,
                option_text=opt.option_text,
                option_letter=opt.option_letter,
                is_correct=False  # Don't reveal correct answers
            )
            for opt in q.options
        ]
        question_responses.append(
            QuestionResponse(
                id=q.id,
                type=q.type,
                difficulty=q.difficulty,
                question_text=q.question_text,
                options=options,
                explanation=None  # Don't reveal explanation until grading
            )
        )
    
    return QuizSessionResponse(
        session_id=quiz_session.id,
        duration_minutes=duration_minutes,
        questions=question_responses
    )


@router.get("/{session_id}", response_model=QuizSessionResponse)
def get_quiz(
    session_id: str,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    quiz_session = db.query(QuizSession).filter(
        QuizSession.id == session_id,
        QuizSession.student_id == current_user.id
    ).first()
    
    if not quiz_session:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Quiz session not found"
        )
    
    # Get questions from config (question_ids stored during generation)
    question_ids = quiz_session.config.get("question_ids", [])
    if question_ids:
        questions = db.query(Question).filter(Question.id.in_(question_ids)).all()
    else:
        questions = []
    
    question_responses = []
    for q in questions:
        options = [
            QuestionOptionResponse(
                id=str(opt.id),
                option_text=opt.option_text,
                option_letter=opt.option_letter,
                is_correct=False
            )
            for opt in q.options
        ]
        question_responses.append(
            QuestionResponse(
                id=str(q.id),
                type=q.type,
                difficulty=q.difficulty,
                question_text=q.question_text,
                options=options,
                explanation=None
            )
        )
    
    return QuizSessionResponse(
        session_id=str(quiz_session.id),
        duration_minutes=quiz_session.duration_minutes,
        questions=question_responses
    )


@router.post("/{session_id}/start")
def start_quiz(
    session_id: str,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    quiz_session = db.query(QuizSession).filter(
        QuizSession.id == session_id,
        QuizSession.student_id == current_user.id
    ).first()
    
    if not quiz_session:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Quiz session not found"
        )
    
    if quiz_session.status != QuizStatus.PENDING:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Quiz already started or completed"
        )
    
    quiz_session.status = QuizStatus.IN_PROGRESS
    quiz_session.started_at = datetime.utcnow()
    db.commit()
    
    return {"message": "Quiz started", "session_id": str(session_id)}


@router.post("/{session_id}/submit", response_model=QuizResults)
async def submit_quiz(
    session_id: str,
    submit_data: QuizSubmit,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    quiz_session = db.query(QuizSession).filter(
        QuizSession.id == session_id,
        QuizSession.student_id == current_user.id
    ).first()
    
    if not quiz_session:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Quiz session not found"
        )
    
    if quiz_session.status == QuizStatus.COMPLETED:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Quiz already submitted"
        )
    
    # Get questions from config (question_ids stored during generation)
    question_ids = quiz_session.config.get("question_ids", [])
    if not question_ids:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Quiz session has no questions"
        )
    
    questions = db.query(Question).filter(Question.id.in_(question_ids)).all()
    
    # Grade answers
    total_points = 0.0
    earned_points = 0.0
    results = []
    
    # Create a mapping of question_id to student answer (ensure IDs are strings)
    answer_map = {str(ans.question_id): ans.answer for ans in submit_data.answers}
    
    for question in questions:
        student_answer = answer_map.get(str(question.id))
        if student_answer is None:
            # No answer provided
            is_correct = False
            points = 0.0
            feedback = "No answer provided."
        else:
            if question.type == QuestionType.ESSAY:
                # Async essay grading
                correct_ans = question.correct_answer.get("answer", "") if isinstance(question.correct_answer, dict) else question.correct_answer
                is_correct, feedback = await grade_essay(
                    str(student_answer),
                    question.question_text,
                    str(correct_ans),
                    question.explanation or ""
                )
                points = 1.0 if is_correct else 0.0
            else:
                is_correct, points, feedback = grade_question(question, student_answer)
        
        total_points += 1.0
        earned_points += points
        
        # Create quiz response
        quiz_response = QuizResponse(
            quiz_session_id=quiz_session.id,
            question_id=question.id,
            student_answer=json.dumps(student_answer) if student_answer is not None else "{}",
            is_correct=is_correct,
            points_earned=points,
            feedback=feedback
        )
        db.add(quiz_response)
        
        # Format correct answer for display
        correct_ans_display = question.correct_answer.get("answer", "") if isinstance(question.correct_answer, dict) else question.correct_answer
        
        # Get question type
        question_type = question.type.value if hasattr(question.type, 'value') else str(question.type)
        
        results.append(
            QuestionResult(
                question_id=str(question.id),
                question_text=question.question_text,
                correct_answer=correct_ans_display,
                student_answer=student_answer if student_answer is not None else "",
                is_correct=is_correct,
                explanation=question.explanation or "",
                feedback=feedback,
                question_type=question_type
            )
        )
    
    # Update quiz session
    quiz_session.status = QuizStatus.COMPLETED
    quiz_session.completed_at = datetime.utcnow()
    quiz_session.score = (earned_points / total_points * 100) if total_points > 0 else 0.0
    
    db.commit()
    
    return QuizResults(
        session_id=str(quiz_session.id),
        score=quiz_session.score,
        total_questions=len(results),
        correct_answers=sum(1 for r in results if r.is_correct),
        results=results
    )


@router.get("/history")
def get_quiz_history(
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """Get quiz history for the current user"""
    from sqlalchemy import desc
    
    quiz_sessions = db.query(QuizSession).filter(
        QuizSession.student_id == current_user.id
    ).order_by(desc(QuizSession.created_at)).all()
    
    history = []
    for session in quiz_sessions:
        # Get course name
        course = db.query(Course).filter(Course.id == session.course_id).first()
        course_name = course.name if course else "Unknown Course"
        
        history.append({
            "session_id": str(session.id),
            "course_id": str(session.course_id),
            "course_name": course_name,
            "score": float(session.score) if session.score is not None else None,
            "status": session.status.value if hasattr(session.status, 'value') else str(session.status),
            "started_at": session.started_at.isoformat() if session.started_at else None,
            "completed_at": session.completed_at.isoformat() if session.completed_at else None,
            "duration_minutes": session.duration_minutes,
            "question_count": session.config.get("question_count", 0) if session.config else 0
        })
    
    return {"history": history}

