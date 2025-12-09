from pydantic_settings import BaseSettings
from typing import Optional


class Settings(BaseSettings):
    database_url: str
    jwt_secret: str = "default-secret-key-change-in-production"
    jwt_algorithm: str = "HS256"
    openrouter_api_key: str = ""
    openrouter_model: str = "tngtech/deepseek-r1t2-chimera:free"
    embedding_api_url: Optional[str] = None
    embedding_api_key: Optional[str] = None
    embedding_model: str = "text-embedding-ada-002"
    
    class Config:
        env_file = ".env"
        case_sensitive = False


settings = Settings()

