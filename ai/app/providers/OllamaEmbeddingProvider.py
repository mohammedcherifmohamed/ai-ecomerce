from abc import abstractmethod
import logging

import ollama 
import httpx
from app.schemas.interfaces.embedding_provider import EmbeddingProvider

logger = logging.getLogger("app.providers.ollama")

class OllamaEmbeddingProvider(EmbeddingProvider):

    def __init__(self,base_url: str , model:str):
        self.model = model
        self.base_url = base_url

    @abstractmethod
    async def embed(self,texts:list[str]) -> list[list[str]]:
        logger.debug("Requesting embedding from Ollama (model=%s)", self.model)

        async with httpx.AsyncClient() as client:
            
            response = await client.post(
                f"{self.base_url}/api/embeddings",
                json={
                    "model":self.model,
                    "prompt" : text
                }
                ,timeout=30.0
            )
           
            if response.status_code == 200:
                data = response.json()
                logger.debug("Embedding received: %d dimensions", len(data.get("embedding", [])))
                return data.get("embedding", [])
            else:
                logger.error("Ollama embedding failed: %s - %s", response.status_code, response.text)
                raise Exception(f"Ollama embedding ERROR:{response.status_code} - {response.text}")
            
    async def embed_batch(self,texts:list[str]) -> list[list[float]]:
        logger.info("Embedding batch: %d texts via Ollama", len(texts))
        embeddings = []
        for text in texts : 
            embedding = await self.embed(text)
            embeddings.append(embedding) 
        logger.info("Batch embedding complete: %d vectors", len(embeddings))
        return embeddings
    