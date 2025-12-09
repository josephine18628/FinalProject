import httpx
import json
from typing import List, Dict, Any
from app.config import settings


async def generate_questions(
    course_name: str,
    format: str,
    difficulty: str,
    question_count: int,
    mixed_config: Dict[str, int] = None
) -> Dict[str, Any]:
    """
    Generate questions using OpenRouter API.
    Returns a dict with 'duration_minutes' and 'questions' list.
    """
    
    # Build format description
    if format == "mixed" and mixed_config:
        format_desc = ", ".join([f"{count} {qtype}" for qtype, count in mixed_config.items()])
    else:
        format_desc = format
    
    prompt = f"""You are an expert computer science educator. Generate quiz questions based on 
reliable textbook knowledge from standard CS3 curriculum textbooks.

Requirements:
- Use only well-established CS concepts (no fictional content)
- Questions must be accurate and clear
- Provide detailed explanations for each answer
- Ensure questions are appropriate for {difficulty} level students

Generate {question_count} {format_desc} question(s) for the course: {course_name}.

For each question:
- Multiple choice (MCQ): Provide 4 options labeled A, B, C, D with exactly one correct answer
- True/False: Provide a statement that is clearly true or false
- Essay: Provide a thought-provoking question requiring detailed explanation
- Calculation: Provide a problem requiring numerical computation with a specific numeric answer

Calculate quiz duration considering:
- Question type complexity
- Cognitive load
- Standard reading/thinking time (estimate 2-3 minutes per MCQ/TF, 5-10 minutes per essay, 3-5 minutes per calculation)

Output ONLY valid JSON in this exact format:
{{
  "duration_minutes": <number>,
  "questions": [
    {{
      "type": "<mcq|tf|essay|calculation>",
      "difficulty": "<beginner|intermediate|advanced>",
      "question": "<question text>",
      "options": ["<option A>", "<option B>", "<option C>", "<option D>"],
      "correct_answer": "<answer text or letter for MCQ>",
      "explanation": "<detailed explanation>"
    }}
  ]
}}

Ensure the JSON is valid and complete. For True/False, use "options": ["True", "False"].
For Essay and Calculation, "options" can be an empty array or omitted.
"""
    
    try:
        async with httpx.AsyncClient(timeout=120.0) as client:
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
                            "content": "You are a helpful assistant that generates educational quiz questions. Always respond with valid JSON only."
                        },
                        {
                            "role": "user",
                            "content": prompt
                        }
                    ],
                    "temperature": 0.7,
                    "response_format": {"type": "json_object"}
                }
            )
            response.raise_for_status()
            data = response.json()
            
            # Extract JSON from response
            content = data["choices"][0]["message"]["content"]
            
            # Parse JSON
            try:
                result = json.loads(content)
                return result
            except json.JSONDecodeError:
                # Try to extract JSON if wrapped in markdown
                if "```json" in content:
                    json_start = content.find("```json") + 7
                    json_end = content.find("```", json_start)
                    content = content[json_start:json_end].strip()
                elif "```" in content:
                    json_start = content.find("```") + 3
                    json_end = content.find("```", json_start)
                    content = content[json_start:json_end].strip()
                
                result = json.loads(content)
                return result
                
    except httpx.HTTPStatusError as e:
        raise Exception(f"OpenRouter API error: {e.response.status_code} - {e.response.text}")
    except Exception as e:
        raise Exception(f"Error generating questions: {str(e)}")

