import logging
from ast import Dict, List
from pathlib import Path
import fitz

logger = logging.getLogger("app.services.pdf")

class PDFService:

    def extract_text(self, path: str) -> List[Dict[str, any]]:
        pdf_path = Path(path)
        if not pdf_path.exists():
            raise FileNotFoundError(pdf_path)

        if not pdf_path.suffix.lower() == '.pdf':
            raise ValueError(f"File is not a PDF: {path}")

        pages = []
        try:
            with fitz.open(pdf_path) as document:
                logger.info("Extracting text from PDF: %s (%d pages)", path, len(document))
                for index, page in enumerate(document):
                    text = page.get_text("text").strip()

                    if not text:
                        continue

                    pages.append({
                        "page": index + 1,
                        "text": text,
                    })
            logger.info("Extracted %d pages with text from %s", len(pages), path)
        except Exception as e:
            logger.error("Failed to extract text from PDF %s: %s", path, str(e))
            raise RuntimeError(f"Failed to extract text from PDF: {e}")

        return pages
