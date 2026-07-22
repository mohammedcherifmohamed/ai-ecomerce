import asyncio
import logging

import httpx

from app.core.config import settings

logger = logging.getLogger("app.services.tool_executor")


class ToolExecutor:
    def __init__(self):
        self.base_url = settings.LARAVEL_API_URL.rstrip("/")
        self.api_key = settings.AI_API_KEY

    async def _post(self, endpoint: str, data: dict) -> dict:
        logger.info("Calling Laravel: %s %s", endpoint, data)
        for attempt in range(2):
            try:
                async with httpx.AsyncClient() as client:
                    response = await client.post(
                        f"{self.base_url}{endpoint}",
                        data=data,
                        headers={
                            "Authorization": f"Bearer {self.api_key}",
                            "Accept": "application/json",
                        },
                        timeout=10.0,
                    )
                    if response.is_success:
                        result = response.json()
                        logger.info("Result: %s", result)
                        return result
                    else:
                        logger.error("Laravel API error: %s - %s", response.status_code, response.text)
                        return {"success": False, "error": f"API returned {response.status_code}"}
            except Exception as e:
                err_msg = str(e) or type(e).__name__
                if attempt == 0:
                    logger.warning("Laravel API attempt 1 failed, retrying: %s", err_msg)
                    await asyncio.sleep(1)
                else:
                    logger.error("Laravel API call failed after retry: %s", err_msg)
                    return {"success": False, "error": err_msg}

    async def get_order_status(self, customer_id: int, order_id: int) -> dict:
        return await self._post("/ai/orders/status", {
            "customer_id": customer_id,
            "order_id": order_id,
        })

    async def cancel_order(self, customer_id: int, order_id: int) -> dict:
        return await self._post("/ai/orders/cancel", {
            "customer_id": customer_id,
            "order_id": order_id,
        })

    async def create_inquiry(self, inquiry: str, category: str | None = None) -> dict:
        data = {"inquiry": inquiry}
        if category:
            data["category"] = category
        return await self._post("/ai/inquiry/create", data)

    async def search_inquiries(self, keyword: str | None = None, category: str | None = None, date_from: str | None = None, date_to: str | None = None, limit: int | None = None) -> dict:
        data = {}
        if keyword:
            data["keyword"] = keyword
        if category:
            data["category"] = category
        if date_from:
            data["date_from"] = date_from
        if date_to:
            data["date_to"] = date_to
        if limit:
            data["limit"] = limit
        return await self._post("/ai/inquiries/search", data)

    async def customer_summary(self, customer_id: int | None = None, email: str | None = None) -> dict:
        data = {}
        if customer_id:
            data["customer_id"] = customer_id
        if email:
            data["email"] = email
        return await self._post("/ai/customers/summary", data)

    async def trends_statistics(self, period: str | None = None, date_from: str | None = None, date_to: str | None = None) -> dict:
        data = {}
        if period:
            data["period"] = period
        if date_from:
            data["date_from"] = date_from
        if date_to:
            data["date_to"] = date_to
        return await self._post("/ai/orders/trends", data)

    async def ticket_analysis(self, date_from: str | None = None, date_to: str | None = None, category: str | None = None) -> dict:
        data = {}
        if date_from:
            data["date_from"] = date_from
        if date_to:
            data["date_to"] = date_to
        if category:
            data["category"] = category
        return await self._post("/ai/tickets/analysis", data)
    
    