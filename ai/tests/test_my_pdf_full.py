# test_my_pdf_full.py
import asyncio
import json
from app.services.ingestion_service import IngestionService
from app.core.config import settings

print("=" * 60)
print("TEST: Full Pipeline with Your PDF")
print("=" * 60)

# Replace with your PDF filename
PDF_FILENAME = "your_document.pdf"  # CHANGE THIS!

async def test_full_pipeline():
    ingestion = IngestionService()
    
    print(f"\n📄 Processing: {PDF_FILENAME}")
    print("-" * 60)
    
    try:
        # Process the document
        result = await ingestion.process_document(
            document_id=1,
            file_path=PDF_FILENAME,
            collection="test_collection"
        )
        
        print("\n✅ Document processed successfully!")
        print(f"\n📊 Results:")
        print(json.dumps(result, indent=2))
        
        # Check if document exists in ChromaDB
        print("\n🔍 Checking document status...")
        status = ingestion.get_document_status(
            document_id=1,
            collection="test_collection"
        )
        print(json.dumps(status, indent=2))
        
        # Test chat with questions about your document
        print("\n💬 Testing Chat with Your Document")
        print("-" * 60)
        
        # Ask a generic question first
        questions = [
            "What is this document about?",
            "What are the main topics covered?",
            "Can you summarize the key information?"
        ]
        
        import httpx
        async with httpx.AsyncClient() as client:
            for question in questions:
                print(f"\n❓ Question: {question}")
                
                try:
                    response = await client.post(
                        "http://localhost:8001/chat",
                        json={
                            "message": question,
                            "top_k": 3
                        },
                        timeout=30.0
                    )
                    
                    if response.status_code == 200:
                        data = response.json()
                        answer = data['response'][:300] + "..." if len(data['response']) > 300 else data['response']
                        print(f"🤖 Answer: {answer}")
                        print(f"📚 Sources: {len(data['sources'])} chunks")
                    else:
                        print(f"❌ Error: {response.status_code}")
                        
                except Exception as e:
                    print(f"❌ Error: {e}")
        
        print("\n✅ Full Test Completed!")
        
    except FileNotFoundError:
        print(f"\n❌ PDF file not found: {PDF_FILENAME}")
        print("Please check the filename and try again.")
    except Exception as e:
        print(f"\n❌ Error during processing: {e}")

# Run the test
asyncio.run(test_full_pipeline())