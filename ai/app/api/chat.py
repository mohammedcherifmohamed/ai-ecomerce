import logging
from fastapi import APIRouter

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


@router.post(
    "",
    response_model=ChatResponse,
)
async def chat(request: ChatRequest) -> ChatResponse:
    embedding_service = EmbeddingService(provider=EMBEDDING_PROVIDER)
    chroma_service = ChromaService()
    retrieval_service = RetrievalService(
        embedding_service=embedding_service,
        chroma_service=chroma_service,
    )
    llm_provider = OllamaLLMProvider()
    chat_service = ChatService(
        retrieval_service=retrieval_service,
        llm_provider=llm_provider,
    )

    return await chat_service.chat(request)
