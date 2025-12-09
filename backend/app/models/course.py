from sqlalchemy import Column, String, DateTime, Text, CHAR
from sqlalchemy.sql import func
import uuid
from app.database import Base


class Course(Base):
    __tablename__ = "courses"

    id = Column(CHAR(36), primary_key=True, default=lambda: str(uuid.uuid4()))
    name = Column(String(255), nullable=False)
    code = Column(String(50), unique=True, nullable=False, index=True)
    description = Column(Text, nullable=True)
    created_at = Column(DateTime(timezone=False), server_default=func.now())

