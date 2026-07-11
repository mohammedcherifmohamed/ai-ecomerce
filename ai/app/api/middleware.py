import time
import logging
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.requests import Request

logger = logging.getLogger("app.http")


class RequestLoggingMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request: Request, call_next):
        start = time.time()
        client_ip = request.client.host if request.client else "unknown"

        logger.info(
            ">>> %s %s from %s",
            request.method,
            request.url.path,
            client_ip,
        )

        try:
            response = await call_next(request)
        except Exception as exc:
            elapsed = round((time.time() - start) * 1000, 2)
            logger.error(
                "<<< %s %s -> 500 (%sms) ERROR: %s",
                request.method,
                request.url.path,
                elapsed,
                str(exc),
            )
            raise

        elapsed = round((time.time() - start) * 1000, 2)

        if response.status_code >= 500:
            logger.error(
                "<<< %s %s -> %s (%sms)",
                request.method,
                request.url.path,
                response.status_code,
                elapsed,
            )
        elif response.status_code >= 400:
            logger.warning(
                "<<< %s %s -> %s (%sms)",
                request.method,
                request.url.path,
                response.status_code,
                elapsed,
            )
        else:
            logger.info(
                "<<< %s %s -> %s (%sms)",
                request.method,
                request.url.path,
                response.status_code,
                elapsed,
            )

        return response
