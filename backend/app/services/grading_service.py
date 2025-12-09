from typing import Dict, Any, List
from app.models.question import Question, QuestionType
from app.services.ai_service import generate_questions
from app.config import settings
import httpx
import json


def grade_mcq(student_answer: str, correct_answer: str) -> bool:
    """Grade multiple choice question."""
    # Normalize answers (trim, lowercase for comparison)
    student = student_answer.strip().upper()
    correct = correct_answer.strip().upper()
    return student == correct


def grade_true_false(student_answer: Any, correct_answer: str) -> bool:
    """Grade true/false question."""
    # Handle both string and boolean inputs
    if isinstance(student_answer, bool):
        student_str = "true" if student_answer else "false"
    else:
        student_str = str(student_answer).strip().lower()
    
    correct_str = str(correct_answer).strip().lower()
    return student_str == correct_str


def grade_calculation(student_answer: float, correct_answer: float, tolerance: float = 0.01) -> bool:
    """Grade calculation question with tolerance."""
    try:
        student_num = float(student_answer)
        correct_num = float(correct_answer)
        return abs(student_num - correct_num) <= tolerance
    except (ValueError, TypeError):
        return False


async def grade_essay(student_answer: str, question_text: str, correct_answer: str, explanation: str) -> tuple[bool, str]:
    """
    Grade essay question using LLM with rubric.
    Returns (is_correct: bool, feedback: str)
    """
    prompt = f"""You are grading a student's essay response for a computer science quiz question.

Question: {question_text}

Expected Answer/Key Points: {correct_answer}

Explanation: {explanation}

Student's Answer:
{student_answer}

Please evaluate the student's answer based on:
1. Accuracy of understanding (40%)
2. Completeness of response (30%)
3. Clarity and organization (20%)
4. Use of appropriate terminology (10%)

Provide:
1. A score from 0-100
2. Brief feedback explaining strengths and weaknesses

Output ONLY valid JSON:
{{
  "score": <number 0-100>,
  "feedback": "<detailed feedback>",
  "is_passing": <true if score >= 70>
}}
"""
    
    try:
        async with httpx.AsyncClient(timeout=60.0) as client:
            response = await client.post(
                "https://openrouter.ai/api/v1/chat/completions",
                headers={
                    "Authorization": f"Bearer {settings.openrouter_api_key}",
                    "HTTP-Referer": "http://localhost:3000",
                    "Content-Type": "application/json"
                },
                json={
                    "model": settings.openrouter_model,
                    "messages": [
                        {
                            "role": "system",
                            "content": "You are a helpful assistant that grades student essays. Always respond with valid JSON only."
                        },
                        {
                            "role": "user",
                            "content": prompt
                        }
                    ],
                    "temperature": 0.3,
                    "response_format": {"type": "json_object"}
                }
            )
            response.raise_for_status()
            data = response.json()
            content = data["choices"][0]["message"]["content"]
            
            # Parse JSON
            try:
                result = json.loads(content)
            except json.JSONDecodeError:
                # Try to extract JSON if wrapped
                if "```json" in content:
                    json_start = content.find("```json") + 7
                    json_end = content.find("```", json_start)
                    content = content[json_start:json_end].strip()
                result = json.loads(content)
            
            score = result.get("score", 0)
            feedback = result.get("feedback", "No feedback provided.")
            is_passing = result.get("is_passing", score >= 70)
            
            return is_passing, feedback
            
    except Exception as e:
        # Fallback: simple keyword matching if AI fails
        student_lower = student_answer.lower()
        key_points = correct_answer.lower().split()
        matches = sum(1 for point in key_points if point in student_lower)
        is_correct = matches >= len(key_points) * 0.5  # 50% match threshold
        return is_correct, f"Automatic grading unavailable. Error: {str(e)}"


def grade_question(
    question: Question,
    student_answer: Any
) -> tuple[bool, float, str]:
    """
    Grade a single question based on its type.
    Returns (is_correct: bool, points_earned: float, feedback: str)
    """
    points_per_question = 1.0
    
    # Get correct answer from dict or direct value
    if isinstance(question.correct_answer, dict):
        correct_answer = question.correct_answer.get("answer", "")
    else:
        correct_answer = question.correct_answer
    
    if question.type == QuestionType.MCQ:
        is_correct = grade_mcq(str(student_answer), str(correct_answer))
        feedback = question.explanation if is_correct else f"Incorrect. {question.explanation or ''}"
        return is_correct, points_per_question if is_correct else 0.0, feedback
    
    elif question.type == QuestionType.TRUE_FALSE:
        is_correct = grade_true_false(student_answer, str(correct_answer))
        feedback = question.explanation if is_correct else f"Incorrect. {question.explanation or ''}"
        return is_correct, points_per_question if is_correct else 0.0, feedback
    
    elif question.type == QuestionType.CALCULATION:
        try:
            correct_num = float(correct_answer)
            is_correct = grade_calculation(student_answer, correct_num)
        except (ValueError, TypeError):
            is_correct = False
        feedback = question.explanation if is_correct else f"Incorrect. {question.explanation or ''}"
        return is_correct, points_per_question if is_correct else 0.0, feedback
    
    elif question.type == QuestionType.ESSAY:
        # Essay grading is async, so this will be handled separately
        # Default return - will be updated by async grading
        return False, 0.0, "Essay grading in progress..."
    
    return False, 0.0, "Unknown question type"

