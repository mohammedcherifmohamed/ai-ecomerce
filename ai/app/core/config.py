from dotenv import load_dotenv
from pydantic_settings import BaseSettings
from app.providers.OllamaEmbeddingProvider import OllamaEmbeddingProvider


class Settings(BaseSettings):
    AI_API_KEY: str
    LARAVEL_API_URL:str="http://127.0.0.1:8000/api"
    
    APP_NAME: str = "ECOM AI SERVICE"
    APP_ENV:  str = "development"
    DEBUG:bool = True
    
    OLLAMA_HOST: str = "http://localhost:11434"
    EMBEDDING_MODEL: str = "nomic-embed-text"
    LLM_MODEL : str = "mistral"
    
    CHROMA_PERSIST_DIRECTORY: str = "./chroma_db"
    CHROMA_COLLECTION_NAME: str = "ecom_documents"
    
    CHUNK_SIZE: int = 500  
    CHUNK_OVERLAP: int = 50  
    
    DEFAULT_TOP_K: int = 5  
    
    class Config:
        env_file = ".env"
        extra = "ignore"

settings = Settings()

EMBEDDING_PROVIDER = OllamaEmbeddingProvider(
    base_url=settings.OLLAMA_HOST,
    model=settings.EMBEDDING_MODEL,
)
