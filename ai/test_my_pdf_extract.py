# test_my_pdf_extract.py
from app.services.pdf_service import PDFService
import json

print("=" * 60)
print("TEST: Extract Text from Your PDF")
print("=" * 60)

# Replace with your PDF filename
PDF_FILENAME = "pdf.pdf"  # CHANGE THIS!

pdf_service = PDFService()

try:
    # Get PDF info first
    print(f"\n📄 Checking PDF: {PDF_FILENAME}")
    info = pdf_service.get_pdf_info(PDF_FILENAME)
    print(f"  - Pages: {info['pages']}")
    print(f"  - Size: {info['size_mb']} MB")
    print(f"  - Title: {info.get('metadata', {}).get('title', 'N/A')}")
    
    # Extract text
    print(f"\n📖 Extracting text...")
    pages = pdf_service.extract_text(PDF_FILENAME)
    
    print(f"✅ Extracted {len(pages)} pages with text")
    
    # Show preview of each page
    print("\n📝 Page Previews:")
    print("-" * 60)
    
    for page in pages[:5]:  # Show first 5 pages
        text_preview = page['text'][:300] + "..." if len(page['text']) > 300 else page['text']
        print(f"\nPage {page['page']} ({len(page['text'])} characters):")
        print(f"  {text_preview}")
    
    if len(pages) > 5:
        print(f"\n... and {len(pages) - 5} more pages")
    
    print("\n✅ PDF Extraction Successful!")
    
except FileNotFoundError:
    print(f"\n❌ PDF file not found: {PDF_FILENAME}")
    print("Please check the filename and try again.")
except Exception as e:
    print(f"\n❌ Error: {e}")