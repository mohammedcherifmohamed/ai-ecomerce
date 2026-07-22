import json
import logging
import time

from app.schemas.chat_schema import (
    ChatRequest,
    ChatResponse,
    SourceDocument,
)

from app.services.RetrievalService import RetrievalService
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
                    tool_call_str = tool_line.replace("TOOL_CALL:", "").strip()
                    tool_call = json.loads(tool_call_str)
                    tool_name = tool_call["tool"]
                    cid = int(tool_call["customer_id"])
                    order_id = int(tool_call["order_id"])

                    print('___________ printing info : '+ tool_name, cid, order_id)
                    logger.info("Tool detected: %s(customer=%s, order=%s)", tool_name, cid, order_id)
                    tool_used = True

                    if tool_name == "get_order_status":
                        tool_result = await self.tool_executor.get_order_status(cid, order_id)
                    elif tool_name == "cancel_order":
                        tool_result = await self.tool_executor.cancel_order(cid, order_id)
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
