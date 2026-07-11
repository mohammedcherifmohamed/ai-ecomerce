from dataclasses import dataclass

@dataclass(slots=True)

class chunk:
    document_id:int
    page:int
    chunk_index:int
    text:str
    