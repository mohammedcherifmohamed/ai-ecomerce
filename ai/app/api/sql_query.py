import asyncio
import logging

import httpx
from fastapi import APIRouter, HTTPException

from app.core.config import settings
from app.schemas.sql_schema import (
    AsyncSqlQueryResult,
    SqlQueryRequest,
    SqlQueryResponse,
)
from app.services.async_result_store import result_store
from app.services.sql_query_service import execute_sql_query

logger = logging.getLogger(__name__)

router = APIRouter(
    prefix="/sql",
    tags=["SQL Query"],
)


@router.post("/query", response_model=SqlQueryResponse)
async def query_sync(request: SqlQueryRequest) -> SqlQueryResponse:
    """Execute a SQL query synchronously and return results directly."""
    return execute_sql_query(request.query)


@router.post("/query/async")
async def query_async(request: SqlQueryRequest) -> dict:
    """Submit a SQL query for async processing with callback."""
    if not request.callback_url:
        raise HTTPException(status_code=400, detail="callback_url is required for async processing")

    request_id = result_store.generate_id()
    await result_store.set_pending(request_id)

    asyncio.create_task(_process_async(request_id, request))
    logger.info("SQL async query accepted: %s (callback: %s)", request_id, request.callback_url)

    return {"request_id": request_id, "status": "accepted"}


async def _process_async(request_id: str, request: SqlQueryRequest) -> None:
    try:
        response = execute_sql_query(request.query)

        await result_store.set_result(request_id, response.model_dump())
        logger.info("SQL async result ready: %s", request_id)

        if request.callback_url:
            await _send_callback(request.callback_url, request_id, response)

    except Exception as e:
        logger.error("SQL async processing failed: %s", str(e))
        error_response = SqlQueryResponse(success=False, error=str(e))
        await result_store.set_error(request_id, str(e))
        if request.callback_url:
            await _send_callback(request.callback_url, request_id, error_response)


async def _send_callback(url: str, request_id: str, response: SqlQueryResponse) -> None:
    try:
        async with httpx.AsyncClient() as client:
            await client.post(
                url,
                json={
                    "request_id": request_id,
                    "success": response.success,
                    "columns": response.columns,
                    "rows": response.rows,
                    "row_count": response.row_count,
                    "execution_time_ms": response.execution_time_ms,
                    "error": response.error,
                },
                headers={
                    "Accept": "application/json",
                    "Authorization": f"Bearer {settings.AI_API_KEY}",
                },
                timeout=30,
            )
        logger.info("SQL callback sent to %s", url)
    except Exception as e:
        logger.error("SQL callback to %s failed: %s", url, str(e))


@router.get("/query/result/{request_id}", response_model=AsyncSqlQueryResult)
async def query_result(request_id: str) -> AsyncSqlQueryResult:
    """Poll for async SQL query result."""
    entry = await result_store.get(request_id)
    if entry is None:
        raise HTTPException(status_code=404, detail="Request ID not found")

    return AsyncSqlQueryResult(
        request_id=request_id,
        status=entry.get("status", "error"),
        result=entry.get("result"),
    )
