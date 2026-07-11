from typing import List
import logging

from app.models.chunk import Chunk
from app.services.embedding_service import EmbeddingService
from app.services.chroma_service import ChromaService

logger = logging.getLogger(__name__)


class RetrievalService:
    def __init__(
        self,
        embedding_service: EmbeddingService,
        chroma_service: ChromaService,
    ) -> None:
        self.embedding_service = embedding_service
        self.chroma_service = chroma_service

    async def retrieve(
        self,
        question: str,
        collection: str,
        top_k: int = 5,
    ) -> List[Chunk]:
        logger.info("Searching collection '%s' for: %s", collection, question)

        query_embedding = await self.embedding_service.embed_text(question)

        results = self.chroma_service.search(
            embedding=query_embedding,
            top_k=top_k,
            collection=collection,
        )

        chunks = []
        for r in results:
            meta = r.get("metadata", {})
            chunks.append(Chunk(
                document_id=meta.get("document_id", 0),
                page=meta.get("page", 0),
                chunk_index=meta.get("chunk_index", 0),
                text=r.get("text", ""),
                similarity=r.get("distance", 0.0),
            ))

        logger.info("Retrieved %d chunks", len(chunks))
        return chunks
