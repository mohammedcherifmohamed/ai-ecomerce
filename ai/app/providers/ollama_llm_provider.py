from abc import ABC, abstractmethod


class LLMProvider(ABC):
    @abstractmethod
    async def generate(self,prompt:str,temperature:float = 0.0,)->str:
        pass
    
