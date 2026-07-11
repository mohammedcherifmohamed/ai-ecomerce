from app.services.chunk_service import ChunkService


service = ChunkService(
    chunk_size=80,
    chunk_overlap=20,
)

text = """
Laravel is a modern PHP framework.

It supports dependency injection.

It has queues.

It has jobs.

It has events.

It has notifications.

It has caching.
"""

chunks = service.split_page(
    document_id=1,
    page=1,
    text=text,
)

for chunk in chunks:
    print("--------------------")
    print(chunk)