import logging
import time

from app.schemas.chat_schema import (
    ChatRequest,
    ChatResponse,
    SourceDocument,
)

from app.services.RetrievalService import RetrievalService
from app.services.prompt_builder import PromptBuilder
from app.providers.ollama_llm_provider import LLMProvider

logger = logging.getLogger(__name__)


class ChatService:

    def __init__(
        self,
        retrieval_service: RetrievalService,
        llm_provider: LLMProvider,
    ) -> None:

        self.retrieval_service = retrieval_service
        self.llm_provider = llm_provider

    async def chat(
        self,
        request: ChatRequest,
    ) -> ChatResponse:

        start = time.perf_counter()

        logger.info(
            "Processing chat request"
        )

        chunks = await self.retrieval_service.retrieve(
            question=request.question,
            collection=request.collection,
            top_k=request.top_k,
        )

        prompt = PromptBuilder.build(
            question=request.question,
            chunks=chunks,
        )

        answer = await self.llm_provider.generate(
            prompt=prompt,
        )

        elapsed = time.perf_counter() - start

        logger.info(
            "Chat completed in %.2f seconds",
            elapsed,
        )

        sources = [
            SourceDocument(
                document_id=chunk.document_id,
                page=chunk.page,
                chunk_index=chunk.chunk_index,
                similarity=chunk.similarity,
                text=chunk.text,
            )
            for chunk in chunks
        ]

        return ChatResponse(
            success=True,
            answer=answer,
            sources=sources,
            processing_time=elapsed,
        )