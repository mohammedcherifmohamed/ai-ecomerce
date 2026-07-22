import logging
from fastapi import APIRouter, HTTPException

from app.schemas.chat_schema import ChatRequest, ChatResponse
from app.services.admin_agent import AdminAgent
from app.providers.OllamaLLMProvider import OllamaLLMProvider

logger = logging.getLogger("app.api.chat_admin")

router = APIRouter(
    prefix="/chat",
    tags=["Chat Admin"],
)


@router.post("/admin", response_model=ChatResponse)
async def chat_admin(request: ChatRequest) -> ChatResponse:
    llm_provider = OllamaLLMProvider()
    agent = AdminAgent(llm_provider=llm_provider)
    return await agent.chat(request)


@router.post("/admin/async")
async def chat_admin_async(request: ChatRequest) -> dict:
    """Submit tool_result for background LLM processing.
    Python processes it asynchronously and POSTs the result to request.callback_url when done.
    Returns immediately with a request_id.
    """
    if not request.tool_result:
        raise HTTPException(status_code=400, detail="tool_result is required for async processing")
    if not request.callback_url:
        raise HTTPException(status_code=400, detail="callback_url is required for async processing")

    llm_provider = OllamaLLMProvider()
    agent = AdminAgent(llm_provider=llm_provider)
    request_id = await agent.chat_async(request)

    return {"request_id": request_id, "status": "accepted"}
