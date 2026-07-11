import logging
from app.schemas.interfaces.embedding_provider import EmbeddingProvider

logger = logging.getLogger("app.services.embedding")

class EmbeddingService:
    def __init__(self, provider: EmbeddingProvider):
        self.provider = provider
        logger.info("Embedding service initialized with provider: %s", type(provider).__name__)

    async def embed_text(self, text: str):
        logger.debug("Embedding single text (%d chars)", len(text))
        return await self.provider.embed(text)

    async def embed_batch(self, texts: list[str]) -> list[list[float]]:
        logger.info("Embedding batch: %d texts", len(texts))
        return await self.provider.embed_batch(texts)