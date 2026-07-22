import logging
from fastapi import APIRouter, HTTPException

from app.schemas.chat_schema import ChatRequest, ChatResponse
from app.services.chat_service import ChatService
from app.services.RetrievalService import RetrievalService
from app.services.embedding_service import EmbeddingService
from app.services.chroma_service import ChromaService
from app.providers.OllamaLLMProvider import OllamaLLMProvider
from app.core.config import EMBEDDING_PROVIDER

logger = logging.getLogger("app.api.chat")

router = APIRouter(
    prefix="/chat",
    tags=["Chat"],
)


def _build_chat_service():
    embedding_service = EmbeddingService(provider=EMBEDDING_PROVIDER)
    chroma_service = ChromaService()
    retrieval_service = RetrievalService(
        embedding_service=embedding_service,
        chroma_service=chroma_service,
    )
    llm_provider = OllamaLLMProvider()
    return ChatService(
        retrieval_service=retrieval_service,
        llm_provider=llm_provider,
    )


@router.post("", response_model=ChatResponse)
async def chat(request: ChatRequest) -> ChatResponse:
    chat_service = _build_chat_service()
    return await chat_service.chat(request)


@router.post("/async")
async def chat_async(request: ChatRequest) -> dict:
    """Submit tool_result for background LLM processing.
    Python processes it asynchronously and POSTs the result to request.callback_url when done.
    Returns immediately with a request_id.
    """
    if not request.tool_result:
        raise HTTPException(status_code=400, detail="tool_result is required for async processing")
    if not request.callback_url:
        raise HTTPException(status_code=400, detail="callback_url is required for async processing")

    chat_service = _build_chat_service()
    request_id = await chat_service.chat_async(request)

    return {"request_id": request_id, "status": "accepted"}
