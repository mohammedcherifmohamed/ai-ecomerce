import asyncio
import json
import logging
import time

import httpx

from app.core.config import settings
from app.schemas.chat_schema import (
    ChatRequest,
    ChatResponse,
    SourceDocument,
)

from app.services.RetrievalService import RetrievalService
from app.services.async_result_store import result_store
from app.services.prompt_builder import PromptBuilder
from app.services.tool_executor import ToolExecutor
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
        self.tool_executor = ToolExecutor()

    async def chat(self, request: ChatRequest) -> ChatResponse:
        print('______________ starting the chat function ________________')
        start = time.perf_counter()
        logger.info("Processing chat request")

        chunks = await self.retrieval_service.retrieve(
            question=request.question,
            collection=request.collection,
            top_k=request.top_k,
        )

        if request.tool_result:
            logger.info("Using provided tool result: %s", request.tool_result)
            tool_used = True
            final_prompt = PromptBuilder.build(
                question=request.question,
                chunks=chunks,
                tool_result=request.tool_result,
                customer_id=request.customer_id,
            )
            final_answer = await self.llm_provider.generate(prompt=final_prompt)
        else:
            prompt = PromptBuilder.build(
                question=request.question,
                chunks=chunks,
                customer_id=request.customer_id,
            )

            answer = await self.llm_provider.generate(prompt=prompt)
            final_answer = answer
            tool_used = False

            if "TOOL_CALL:" in answer and request.execute_tools:
                try:
                    tool_line = [line.strip() for line in answer.split("\n") if "TOOL_CALL:" in line][0]
                    idx = tool_line.index("TOOL_CALL:")
                    tool_call_str = tool_line[idx + 10:].strip()
                    tool_call = json.loads(tool_call_str)
                    tool_name = tool_call["tool"]
                    tool_used = True
                    logger.info("Tool detected: %s %s", tool_name, tool_call)

                    if tool_name == "get_order_status":
                        print("---------> calling get_order_status ... with : "+ tool_call.get("order_id"))
                        
                        cid = tool_call.get("customer_id")
                        order_id = tool_call.get("order_id")
                        if not cid or not order_id:
                            tool_result = {"success": False, "error": "Missing customer_id or order_id for get_order_status"}
                        else:
                            tool_result = await self.tool_executor.get_order_status(int(cid), int(order_id))
                    elif tool_name == "cancel_order":
                        print("---------> calling cancel_order ... with : "+ tool_call.get("order_id"))
                        
                        cid = tool_call.get("customer_id")
                        order_id = tool_call.get("order_id")
                        if not cid or not order_id:
                            tool_result = {"success": False, "error": "Missing customer_id or order_id for cancel_order"}
                        else:
                            tool_result = await self.tool_executor.cancel_order(int(cid), int(order_id))
                    elif tool_name == "create_inquiry":
                        print("---------> calling create_inquiry ... with : "+ tool_call.get("inquiry"))
                        inquiry = tool_call.get("inquiry")
                        if not inquiry:
                            tool_result = {"success": False, "error": "Missing inquiry text for create_inquiry"}
                        else:
                            category = tool_call.get("category")
                            tool_result = await self.tool_executor.create_inquiry(inquiry, category)
                    else:
                        tool_result = {"success": False, "error": f"Unknown tool: {tool_name}"}

                    final_prompt = PromptBuilder.build(
                        question=request.question,
                        chunks=chunks,
                        tool_result=tool_result,
                        customer_id=request.customer_id,
                    )

                    final_answer = await self.llm_provider.generate(prompt=final_prompt)
                except Exception as e:
                    logger.error("Tool execution failed: %s", str(e))

        elapsed = time.perf_counter() - start
        logger.info("Chat completed in %.2f seconds (tool_used=%s)", elapsed, tool_used)

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
            answer=final_answer,
            sources=sources,
            processing_time=elapsed,
        )

    async def chat_async(self, request: ChatRequest) -> str:
        """Submit for background LLM processing. When done, POSTs to callback_url."""
        chunks = await self.retrieval_service.retrieve(
            question=request.question,
            collection=request.collection,
            top_k=request.top_k,
        )

        request_id = result_store.generate_id()
        await result_store.set_pending(request_id)

        asyncio.create_task(self._process_async(request_id, request, chunks))
        logger.info("ChatService spawned background task: %s", request_id)
        return request_id

    async def _process_async(self, request_id: str, request: ChatRequest, chunks: list) -> None:
        try:
            final_prompt = PromptBuilder.build(
                question=request.question,
                chunks=chunks,
                tool_result=request.tool_result,
                customer_id=request.customer_id,
            )
            final_answer = await self.llm_provider.generate(prompt=final_prompt)
            payload = {"success": True, "answer": final_answer}

            await result_store.set_result(request_id, payload)
            logger.info("ChatService async result ready: %s", request_id)

            if request.callback_url:
                await self._send_callback(request.callback_url, request_id, payload)

        except Exception as e:
            logger.error("ChatService async processing failed: %s", str(e))
            error_payload = {"success": False, "error": str(e)}
            await result_store.set_error(request_id, str(e))
            if request.callback_url:
                await self._send_callback(request.callback_url, request_id, error_payload)

    async def _send_callback(self, url: str, request_id: str, payload: dict) -> None:
        try:
            async with httpx.AsyncClient() as client:
                response = await client.post(
                    url,
                    json={"request_id": request_id, **payload},
                    headers={
                        "Accept": "application/json",
                        "Authorization": f"Bearer {settings.AI_API_KEY}",
                    },
                    timeout=30,
                )
                logger.info("Callback sent to %s — status %s", url, response.status_code)
        except Exception as e:
            logger.error("Callback to %s failed: %s", url, str(e))
