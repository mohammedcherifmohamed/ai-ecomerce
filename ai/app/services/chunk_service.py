import logging
from langchain_text_splitters import RecursiveCharacterTextSplitter
from app.models.chunk import Chunk

logger = logging.getLogger("app.services.chunk")


class ChunkService:

    def __init__(
        self,
        chunk_size: int = 1000,
        chunk_overlap: int = 200,
        ):
        self.splitter = RecursiveCharacterTextSplitter(
            chunk_size=chunk_size,
            chunk_overlap=chunk_overlap,
            separators=[
                "\n\n",
                "\n",
                ". ",
                "? ",
                "! ",
                " ",
                "",
            ],
        )

    def split_page(
        self,
        *,
        document_id: int,
        page: int,
        text: str,
        ) -> list[chunk]:

        pieces = self.splitter.split_text(text)
        logger.debug("Split page %d into %d chunks", page, len(pieces))

        chunks = []

        for index, piece in enumerate(pieces):
            chunks.append(
                Chunk(
                    document_id=document_id,
                    page=page,
                    chunk_index=index,
                    text=piece,
                )
            )

        return chunks