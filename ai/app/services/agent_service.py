from app.services.tool_executor import ToolExecutor
from app.services.RetrievalService import RAGService
from app.services.prompt_builder import PromptBuilder
from app.providers.ollama_llm_provider import OllamaLLMProvider

class AgentService:
    
    def __init__(self,llm:OllamaLLMProvider,rag:RAGService,
                 tool_executor:ToolExecutor,prompt_builder:PromptBuilder
                 ):
        self.llm = llm
        self.rag = rag
        self.tool_executor = tool_executor 
        self.prompt_builder = prompt_builder
    def chat(self,customer_id:int,message:str)->str:
        pass
    