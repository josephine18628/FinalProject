from sqlalchemy.orm import Session
from typing import Optional, List
from app.models.question import Question
from app.utils.embeddings import get_embedding, cosine_similarity
from app.config import settings

SIMILARITY_THRESHOLD = 0.90


async def check_duplicate(
    question_text: str,
    db: Session,
    course_id: Optional[str] = None
) -> Optional[Question]:
    """
    Check if a question is a duplicate using semantic similarity.
    Returns existing question if duplicate found, None otherwise.
    """
    
    # First, check for exact matches (case-insensitive)
    # MySQL: Use LIKE (case-insensitive with utf8mb4_ci) or func.lower()
    from sqlalchemy import func
    exact_match = db.query(Question).filter(
        func.lower(Question.question_text) == func.lower(question_text.strip())
    ).first()
    
    if exact_match:
        return exact_match
    
    # Get embedding for the new question
    new_embedding = await get_embedding(question_text)
    if not new_embedding:
        # If embedding API fails, skip semantic check
        return None
    
    # Get all questions from the same course (or all if course_id not specified)
    query = db.query(Question)
    if course_id:
        query = query.filter(Question.course_id == course_id)
    
    existing_questions = query.all()
    
    # For each existing question, check similarity
    # Note: In production, you'd want to store embeddings in the database
    # For now, we'll compute them on-the-fly (slower but works)
    for existing_q in existing_questions:
        existing_embedding = await get_embedding(existing_q.question_text)
        if existing_embedding:
            similarity = cosine_similarity(new_embedding, existing_embedding)
            if similarity >= SIMILARITY_THRESHOLD:
                return existing_q
    
    return None


async def filter_duplicates(
    questions: List[dict],
    db: Session,
    course_id: Optional[str] = None
) -> tuple[List[dict], int]:
    """
    Filter out duplicate questions from a list.
    Returns (filtered_questions, duplicates_found_count)
    """
    filtered = []
    duplicates_count = 0
    
    for q in questions:
        duplicate = await check_duplicate(q["question"], db, course_id)
        if duplicate:
            duplicates_count += 1
            # Optionally, add existing question to filtered list
            # For now, we'll skip it to use only new questions
        else:
            filtered.append(q)
    
    return filtered, duplicates_count

