import asyncio
import json
import logging
import time

import httpx

from app.core.config import settings
from app.schemas.chat_schema import ChatRequest, ChatResponse, SourceDocument
from app.services.async_result_store import result_store
from app.services.prompt_builder import PromptBuilder
from app.services.tool_executor import ToolExecutor
from app.providers.ollama_llm_provider import LLMProvider

logger = logging.getLogger(__name__)


class AdminAgent:

    def __init__(
        self,
        llm_provider: LLMProvider,
    ) -> None:
        self.llm_provider = llm_provider
        self.tool_executor = ToolExecutor()

    async def chat(self, request: ChatRequest) -> ChatResponse:
        logger.info("AdminAgent processing chat request")
        start = time.perf_counter()

        if request.tool_result:
            logger.info("AdminAgent using provided tool result")
            tool_used = True
            final_prompt = PromptBuilder.build_admin(
                question=request.question,
                tool_result=request.tool_result,
            )
            final_answer = await self.llm_provider.generate(prompt=final_prompt)
        else:
            prompt = PromptBuilder.build_admin(question=request.question)
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
                    logger.info("AdminAgent tool detected: %s %s", tool_name, tool_call)

                    tool_result = await self._execute_admin_tool(tool_name, tool_call)

                    final_prompt = PromptBuilder.build_admin(
                        question=request.question,
                        tool_result=tool_result,
                    )

                    final_answer = await self.llm_provider.generate(prompt=final_prompt)
                except Exception as e:
                    logger.error("AdminAgent tool execution failed: %s", str(e))

        elapsed = time.perf_counter() - start
        logger.info("AdminAgent chat completed in %.2f seconds (tool_used=%s)", elapsed, tool_used)

        return ChatResponse(
            success=True,
            answer=final_answer,
            sources=[],
            processing_time=elapsed,
        )

    async def chat_async(self, request: ChatRequest) -> str:
        """Submit tool_result for background LLM processing.

        When finished, POSTs result to request.callback_url (webhook).
        Returns request_id immediately.
        """
        request_id = result_store.generate_id()
        await result_store.set_pending(request_id)

        asyncio.create_task(self._process_async(request_id, request))
        logger.info("AdminAgent spawned background task: %s (callback: %s)", request_id, request.callback_url)
        return request_id

    async def _process_async(self, request_id: str, request: ChatRequest) -> None:
        try:
            final_prompt = PromptBuilder.build_admin(
                question=request.question,
                tool_result=request.tool_result,
            )
            final_answer = await self.llm_provider.generate(prompt=final_prompt)
            payload = {"success": True, "answer": final_answer}

            await result_store.set_result(request_id, payload)
            logger.info("AdminAgent async result ready: %s", request_id)

            if request.callback_url:
                await self._send_callback(request.callback_url, request_id, payload)

        except Exception as e:
            logger.error("AdminAgent async processing failed: %s", str(e))
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

    async def _execute_admin_tool(self, tool_name: str, tool_call: dict) -> dict:
        if tool_name == "search_inquiries":
            return await self.tool_executor.search_inquiries(
                keyword=tool_call.get("keyword"),
                category=tool_call.get("category"),
                date_from=tool_call.get("date_from"),
                date_to=tool_call.get("date_to"),
                limit=tool_call.get("limit"),
            )
        elif tool_name == "customer_summary":
            return await self.tool_executor.customer_summary(
                customer_id=tool_call.get("customer_id"),
                email=tool_call.get("email"),
            )
        elif tool_name == "trends_statistics":
            return await self.tool_executor.trends_statistics(
                period=tool_call.get("period"),
                date_from=tool_call.get("date_from"),
                date_to=tool_call.get("date_to"),
            )
        elif tool_name == "ticket_analysis":
            return await self.tool_executor.ticket_analysis(
                date_from=tool_call.get("date_from"),
                date_to=tool_call.get("date_to"),
                category=tool_call.get("category"),
            )
        else:
            return {"success": False, "error": f"Unknown admin tool: {tool_name}"}
