from pydantic import BaseModel

class ProcessDocumentRequest(BaseModel):
    document_id : int
    file_path:str