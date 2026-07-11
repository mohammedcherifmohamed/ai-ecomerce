from abc import ABC, abstractmethod


class EmbeddingProvider(ABC):

    @abstractmethod
    def embed(self, texts: list[str]) -> list[list[float]]:
        pass
    
    @abstractmethod
    async def embed_batch(self,texts: list[str]) -> list[list[float]]:
        """generate embeddings for multiple texts"""
        pass