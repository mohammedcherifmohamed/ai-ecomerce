from dataclasses import dataclass, field

@dataclass(slots=True)
class Chunk:
    document_id: int
    page: int
    chunk_index: int
    text: str
    similarity: float = 0.0
    