import httpx
import numpy as np
from typing import List, Optional
from app.config import settings


async def get_embedding(text: str) -> Optional[List[float]]:
    """Get embedding vector for text using API service."""
    if not settings.embedding_api_url or not settings.embedding_api_key:
        return None
    
    try:
        async with httpx.AsyncClient() as client:
            response = await client.post(
                settings.embedding_api_url,
                headers={
                    "Authorization": f"Bearer {settings.embedding_api_key}",
                    "Content-Type": "application/json"
                },
                json={
                    "input": text,
                    "model": settings.embedding_model
                },
                timeout=30.0
            )
            response.raise_for_status()
            data = response.json()
            return data["data"][0]["embedding"]
    except Exception as e:
        print(f"Error getting embedding: {e}")
        return None


def cosine_similarity(vec1: List[float], vec2: List[float]) -> float:
    """Calculate cosine similarity between two vectors."""
    vec1_array = np.array(vec1)
    vec2_array = np.array(vec2)
    
    dot_product = np.dot(vec1_array, vec2_array)
    norm1 = np.linalg.norm(vec1_array)
    norm2 = np.linalg.norm(vec2_array)
    
    if norm1 == 0 or norm2 == 0:
        return 0.0
    
    return float(dot_product / (norm1 * norm2))

