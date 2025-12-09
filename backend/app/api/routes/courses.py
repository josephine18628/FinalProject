from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
from app.database import get_db
from app.models.course import Course
from pydantic import BaseModel

router = APIRouter()


class CourseResponse(BaseModel):
    id: str  # UUID as string
    name: str
    code: str
    description: str | None
    created_at: str

    class Config:
        from_attributes = True


@router.get("/", response_model=List[CourseResponse])
def list_courses(db: Session = Depends(get_db)):
    courses = db.query(Course).all()
    return courses

