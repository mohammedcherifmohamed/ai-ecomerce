import logging
from fastapi import APIRouter
from app.schemas.document_schema import ProcessDocumentRequest

logger = logging.getLogger("app.api.document")

router = APIRouter(
    prefix="/documents",
    tags=["documents"],
)

@router.post('/process')
async def process_document(request: ProcessDocumentRequest):
    logger.info("Processing document: id=%s, file=%s", request.document_id, request.file_path)

    try:
        from app.services.ingestion_service import IngestionService
        service = IngestionService()
        result = await service.process_document(request.document_id, request.file_path)
        logger.info("Document processed: id=%s, result=%s", request.document_id, result)
    except Exception as e:
        logger.error("Failed to process document id=%s: %s", request.document_id, str(e))
        raise

    return {
        "message": "Received",
        "document_id": request.document_id,
        "file_path": request.file_path,
    }
