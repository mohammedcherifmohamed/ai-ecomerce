from pydantic import BaseModel,Field
from typing import List, Optional

class ChatRequest(BaseModel):
    
    question:str =Field(
        ...,
        min_length=3,
        max_length=5000,
        description="user question"
    )
    
    collection: str = Field(
        default="ecom_documents",
        description="Chroma collection"
    )
    
    top_k:int = Field(
        default=5,
        ge=1,
        le=20,
        description="Max retrieved chunks"
    )

    customer_id: Optional[int] = Field(
        default=None,
        description="Customer ID for order-related actions"
    )

    execute_tools: bool = Field(
        default=True,
        description="If True, AI executes tools internally. If False, returns TOOL_CALL as-is for caller to handle."
    )

    tool_result: Optional[dict] = Field(
        default=None,
        description="Pre-executed tool result. When provided, AI uses this to generate final answer."
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