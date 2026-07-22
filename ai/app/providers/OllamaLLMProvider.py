import logging

import httpx

from app.core.config import settings
from app.providers.ollama_llm_provider import LLMProvider

logger = logging.getLogger(__name__)

class OllamaLLMProvider(LLMProvider):
    def __init__(self) -> None:
        self.base_url = settings.OLLAMA_HOST.rstrip("/")
        self.model = settings.LLM_MODEL
        self.timeout = 120.0

        self.client = httpx.AsyncClient(
            timeout=self.timeout
        )
        
    async def generate(self, prompt: str, temperature: float = 0.0) -> str:
        logger.info("Generating response using model '%s'", self.model)
        try:
            response = await self.client.post(
                f"{self.base_url}/api/generate",
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "stream": False,
                    "options": {
                        "temperature": temperature,
                    },
                },
            )
            
            response.raise_for_status()
            
        except httpx.HTTPError as e:

            logger.exception(e)

            raise RuntimeError(
                "Failed to communicate with Ollama."
            )
            
        data = response.json()
            
        return data.get("response", "")

    async def close(self) -> None:
        await self.client.aclose()
    
