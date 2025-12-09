from sqlalchemy import Column, String, DateTime, Integer, ForeignKey, Text, JSON, CHAR
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import uuid
from app.database import Base


class AIGenerationLog(Base):
    __tablename__ = "ai_generation_logs"

    id = Column(CHAR(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    user_id = Column(CHAR(36), ForeignKey("users.id"), nullable=False)
    course_id = Column(CHAR(36), ForeignKey("courses.id"), nullable=False)
    prompt_sent = Column(Text, nullable=False)
    response_received = Column(JSON, nullable=True)
    questions_generated = Column(Integer, default=0, nullable=False)
    questions_stored = Column(Integer, default=0, nullable=False)
    duplicates_found = Column(Integer, default=0, nullable=False)
    created_at = Column(DateTime(timezone=False), server_default=func.now())

    # Relationships
    user = relationship("User", backref="ai_generation_logs")
    course = relationship("Course", backref="ai_generation_logs")

