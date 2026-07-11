from app.services.pdf_service import PDFService
from app.services.cleaner_service import TextCleanerService
from app.services.chunk_service import ChunkService
from app.services.embedding_service import EmbeddingService
from app.services.chroma_service import ChromaService
from app.core.config import settings, EMBEDDING_PROVIDER
import logging
from typing import Dict, Any, List
import time

logger = logging.getLogger(__name__)


class IngestionService:
    def __init__(self):
        self.pdf_service = PDFService()
        self.cleaner_service = TextCleanerService()
        self.chunk_service = ChunkService()
        self.embedding_service = EmbeddingService(provider=EMBEDDING_PROVIDER)
        self.chroma_service = ChromaService()

        logger.info("Ingestion service initialized.")

    async def process_document(self, document_id: int, file_path: str, collection: str = None) -> Dict[str, Any]:
        start_time = time.time()
        logger.info(f"Starting document processing: document_id={document_id}, file={file_path}")

        try:
            # step 1 extract text
            logger.info("Step 1: Extracting text from PDF...")
            pages = self.pdf_service.extract_text(file_path)

            if not pages:
                logger.warning(f"No text extracted from document {document_id}.")
                return {"status": "no_text", "document_id": document_id}

            # step 2 clean text
            clean_pages = []
            for page in pages:
                text = page.get("text", "")
                if text:
                    clean_text = self.cleaner_service.clean(text)
                    clean_pages.append({"page": page["page"], "text": clean_text})
                else:
                    clean_pages.append({"page": page["page"], "text": ""})
                    logger.warning(f"Page {page.get('page', 'unknown')} has no text")

            logger.info(f"Cleaned {len(clean_pages)} pages")

            # step 3 chunking
            logger.info("Splitting into chunks...")
            all_chunks = []
            for page_data in clean_pages:
                page_chunks = self.chunk_service.split_page(
                    document_id=document_id,
                    page=page_data["page"],
                    text=page_data["text"]
                )
                all_chunks.extend(page_chunks)

            # step 4 embeddings
            chunk_texts = [chunk.text for chunk in all_chunks]
            embeddings = await self.embedding_service.embed_batch(chunk_texts)

            # step 5 store embeddings
            logger.info("Storing embeddings in ChromaDB...")
            self.chroma_service.store_embeddings(
                chunks=chunk_texts,
                embeddings=embeddings,
                document_id=document_id,
                collection=collection
            )

            elapsed = time.time() - start_time
            logger.info(f"Document {document_id} processed in {elapsed:.2f}s")
            return {
                "status": "success",
                "document_id": document_id,
                "chunks": len(all_chunks),
                "elapsed_seconds": round(elapsed, 2)
            }

        except Exception as e:
            logger.error(f"Error processing document {document_id}: {str(e)}")
            raise
