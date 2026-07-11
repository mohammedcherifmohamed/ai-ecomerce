import re
import unicodedata
import logging

logger = logging.getLogger("app.services.cleaner")


class TextCleanerService:
    """
    Cleans extracted text before chunking and embedding.

    Responsibilities:
    - Normalize unicode
    - Normalize line endings
    - Remove invisible characters
    - Replace tabs with spaces
    - Collapse multiple spaces
    - Collapse excessive blank lines
    - Trim whitespace
    """

    INVISIBLE_CHARACTERS = (
        "\u200b",  # Zero-width space
        "\u200c",  # Zero-width non-joiner
        "\u200d",  # Zero-width joiner
        "\ufeff",  # BOM
    )

    def clean(self, text: str) -> str:
        if not text:
            return ""

        original_len = len(text)
        text = self._normalize_unicode(text)
        text = self._normalize_line_endings(text)
        text = self._remove_invisible_characters(text)
        text = self._replace_tabs(text)
        text = self._collapse_spaces(text)
        text = self._collapse_blank_lines(text)

        logger.debug("Cleaned text: %d -> %d chars", original_len, len(text))
        return text.strip()

    def _normalize_unicode(self, text: str) -> str:
        return unicodedata.normalize("NFKC", text)

    def _normalize_line_endings(self, text: str) -> str:
        return text.replace("\r\n", "\n").replace("\r", "\n")

    def _remove_invisible_characters(self, text: str) -> str:
        for char in self.INVISIBLE_CHARACTERS:
            text = text.replace(char, "")
        return text

    def _replace_tabs(self, text: str) -> str:
        return text.replace("\t", " ")

    def _collapse_spaces(self, text: str) -> str:
        return re.sub(r"[ ]{2,}", " ", text)

    def _collapse_blank_lines(self, text: str) -> str:
        return re.sub(r"\n{3,}", "\n\n", text)