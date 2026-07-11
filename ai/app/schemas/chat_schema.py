from pydantic import BaseModel,Field
from typing import List

class ChatRequest(BaseModel):
    
    question:str =Field(
        ...,
        min_length=3,
        max_length=5000,
        description="user question"
    )
    
    collection: str = Field(
        ...,
        description="Chroma collection"
    )
    
    top_k:int = Field(
        default=5,
        ge=1,
        le=20,
        description="Max retrieved chunks"
    )
    
    
class SourceDocument(BaseModel):
    document_id: int

    page: int

    chunk_index: int

    similarity: float

    text: str
class ChatResponse(BaseModel):
    success: bool
    answer: str
    sources: List[SourceDocument]
    processing_time: float