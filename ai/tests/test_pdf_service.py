from pathlib import Path

from app.services.pdf_service import PDFService

pdf = PDFService()

pages = pdf.extract_text(
    Path("pdf.pdf")

)

print(f"Pages: {len(pages)}")

for page in pages:
    print(page["page"])
    print(page["text"][:100])