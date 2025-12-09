from sqlalchemy import Column, String, DateTime, Integer, Float, ForeignKey, JSON, Enum as SQLEnum, CHAR, Text, Boolean
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import uuid
import enum
from app.database import Base


class QuizStatus(str, enum.Enum):
    PENDING = "pending"
    IN_PROGRESS = "in_progress"
    COMPLETED = "completed"


class QuizSession(Base):
    __tablename__ = "quiz_sessions"

    id = Column(CHAR(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    student_id = Column(CHAR(36), ForeignKey("users.id"), nullable=False)
    course_id = Column(CHAR(36), ForeignKey("courses.id"), nullable=False)
    config = Column(JSON, nullable=False)  # format, difficulty, question_count
    duration_minutes = Column(Integer, nullable=False)
    started_at = Column(DateTime(timezone=False), nullable=True)
    completed_at = Column(DateTime(timezone=False), nullable=True)
    status = Column(SQLEnum(QuizStatus), default=QuizStatus.PENDING, nullable=False)
    score = Column(Float, nullable=True)

    # Relationships
    student = relationship("User", backref="quiz_sessions")
    course = relationship("Course", backref="quiz_sessions")
    responses = relationship("QuizResponse", back_populates="quiz_session", cascade="all, delete-orphan")


class QuizResponse(Base):
    __tablename__ = "quiz_responses"

    id = Column(CHAR(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    quiz_session_id = Column(CHAR(36), ForeignKey("quiz_sessions.id"), nullable=False)
    question_id = Column(CHAR(36), ForeignKey("questions.id"), nullable=False)
    student_answer = Column(JSON, nullable=False)
    is_correct = Column(Boolean, nullable=False)
    points_earned = Column(Float, default=0.0, nullable=False)
    feedback = Column(Text, nullable=True)

    # Relationships
    quiz_session = relationship("QuizSession", back_populates="responses")
    question = relationship("Question", back_populates="quiz_responses")

