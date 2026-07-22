import asyncio
import logging
import uuid
from typing import Optional

logger = logging.getLogger(__name__)


class AsyncResultStore:
    """In-memory store for async LLM results. Results expire after 10 minutes."""

    def __init__(self):
        self._results: dict[str, dict] = {}
        self._lock = asyncio.Lock()

    def generate_id(self) -> str:
        return uuid.uuid4().hex[:12]

    async def set_pending(self, request_id: str) -> None:
        async with self._lock:
            self._results[request_id] = {"status": "processing", "result": None}

    async def set_result(self, request_id: str, result: dict) -> None:
        async with self._lock:
            self._results[request_id] = {"status": "completed", "result": result}

    async def set_error(self, request_id: str, error: str) -> None:
        async with self._lock:
            self._results[request_id] = {"status": "error", "error": error, "result": None}

    async def get(self, request_id: str) -> Optional[dict]:
        async with self._lock:
            return self._results.get(request_id)

    async def cleanup_expired(self) -> None:
        """Remove results older than 10 minutes (called periodically)."""
        async with self._lock:
            now = asyncio.get_event_loop().time()
            to_remove = []
            for rid, data in self._results.items():
                if data.get("status") == "completed" and data.get("timestamp", 0) < now - 600:
                    to_remove.append(rid)
            for rid in to_remove:
                del self._results[rid]


result_store = AsyncResultStore()
