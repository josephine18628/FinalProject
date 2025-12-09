from sqlalchemy import Column, String, DateTime, Text, Boolean, ForeignKey, JSON, Enum as SQLEnum, CHAR
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import uuid
import enum
from app.database import Base


class QuestionType(str, enum.Enum):
    MCQ = "mcq"
    TRUE_FALSE = "tf"
    ESSAY = "essay"
    CALCULATION = "calculation"
    MIXED = "mixed"


class Difficulty(str, enum.Enum):
    BEGINNER = "beginner"
    INTERMEDIATE = "intermediate"
    ADVANCED = "advanced"


class Question(Base):
    __tablename__ = "questions"

    id = Column(CHAR(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    course_id = Column(CHAR(36), ForeignKey("courses.id"), nullable=False)
    type = Column(SQLEnum(QuestionType), nullable=False)
    difficulty = Column(SQLEnum(Difficulty), nullable=False)
    question_text = Column(Text, nullable=False)
    correct_answer = Column(JSON, nullable=False)  # Varies by type
    explanation = Column(Text, nullable=True)
    created_by_user_id = Column(CHAR(36), ForeignKey("users.id"), nullable=True)
    is_ai_generated = Column(Boolean, default=False, nullable=False)
    created_at = Column(DateTime(timezone=False), server_default=func.now())
    updated_at = Column(DateTime(timezone=False), onupdate=func.now())

    # Relationships
    course = relationship("Course", backref="questions")
    creator = relationship("User", backref="questions_created")
    options = relationship("QuestionOption", back_populates="question", cascade="all, delete-orphan")
    quiz_responses = relationship("QuizResponse", back_populates="question")


class QuestionOption(Base):
    __tablename__ = "question_options"

    id = Column(CHAR(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    question_id = Column(CHAR(36), ForeignKey("questions.id"), nullable=False)
    option_text = Column(Text, nullable=False)
    option_letter = Column(String(1), nullable=True)  # A, B, C, D for MCQ
    is_correct = Column(Boolean, default=False, nullable=False)

    # Relationships
    question = relationship("Question", back_populates="options")

