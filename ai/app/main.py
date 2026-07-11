import logging
from fastapi import FastAPI
from app.core.logging import setup_logging
from app.api.middleware import RequestLoggingMiddleware
from app.api.health import router as health_router
from app.api.document import router as document_router
from app.api.chat import router as chat_router

setup_logging()
logger = logging.getLogger("app")

app = FastAPI(
    title="AI Ecommerce",
    version="1.0.0",
)

app.add_middleware(RequestLoggingMiddleware)

app.include_router(health_router)
app.include_router(document_router)
app.include_router(chat_router)

logger.info("AI Ecommerce service started")