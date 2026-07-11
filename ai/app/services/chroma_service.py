import chromadb
from chromadb.config import settings as Chromasettings
from typing import List , Dict,Any
from app.core.config import settings
import logging

logger = logging.getLogger(__name__)

class ChromaService:
    def __init__(self):
        self.client = chromadb.PersistentClient(
            path=settings.CHROMA_PERSIST_DIRECTORY,
            settings=Chromasettings(anonymized_telemetry=False)
        )
        
        self.collection = self.get_or_create_collection()
        
    def _get_or_create_collection(self):
        try:
            return self.client.get_collection(settings.CHROMA_COLLECTION_NAME)
        except:
            return self.client.create_collection(
                                                name=settings.CHROMA_COLLECTION_NAME,
                                                 metadata={"hnsw:space":"cosine"}
                                                 )
    def store_embeddings(self,chunks:List[str],embeddings:List[List[float]],
                         document_id:int , collection:str = None):
        collection_name = collection or settings.CHROMA_COLLECTION_NAME
        collection = self.client.get_collection(name=collection_name,metadata={"hnsw:space":"cosine"})
        
        ids =[f"{document_id}_{i}" for i in range(len(chunks))]
        
        metadatas = [
            {
                "document_id":document_id,
                "chunk_index" : i ,
                "text":chunks[:100]
            }
            for i ,chunk in enumerate(chunks)
        ]
        
        collection.add(
            embeddings=embeddings,
            documents=chunks,
            metadatas=metadatas,
            ids=ids
        )
        
        logger.info(f"Stored {len(chunks)} embeddings for document_id {document_id} in collection {collection_name}")
        return {'stored':len(chunks)}
            
    def search(self, embedding: List[float], top_k: int = 5, collection: str = None):
 
        collection_name = collection or settings.CHROMA_COLLECTION_NAME
        collection = self.client.get_collection(collection_name)
        
        results = collection.query(
            query_embeddings=[embedding],
            n_results=top_k,
            include=["documents", "metadatas", "distances"]
        )
        
        formatted_results = []
        if results['documents']:
            for i, doc in enumerate(results['documents'][0]):
                formatted_results.append({
                    "text": doc,
                    "metadata": results['metadatas'][0][i] if results['metadatas'] else {},
                    "distance": results['distances'][0][i] if results['distances'] else 0.0
                })
        
        return formatted_results
    
    def delete_by_document_id(self, document_id: int, collection: str = None):
   
        collection_name = collection or settings.CHROMA_COLLECTION_NAME
        collection = self.client.get_collection(collection_name)
        
        all_ids = collection.get()['ids']
        ids_to_delete = [id for id in all_ids if id.startswith(f"{document_id}_")]
        
        if ids_to_delete:
            collection.delete(ids=ids_to_delete)
            logger.info(f"Deleted {len(ids_to_delete)} chunks for document {document_id}")
        
        return {"deleted": len(ids_to_delete)}
        