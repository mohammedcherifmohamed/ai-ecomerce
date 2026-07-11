from typing import List

from app.models.chunk import Chunk


class PromptBuilder:
    """
    Builds prompts sent to the LLM.
    """

    SYSTEM_PROMPT = """
You are an AI assistant for an enterprise e-commerce platform.

Answer ONLY using the provided context.

Rules:
- If the answer exists in the context, answer clearly.
- If the answer does not exist, say:
  "I couldn't find this information in the documentation."
- Do not invent information.
- Keep answers concise and professional.
"""

    @classmethod
    def build(
        cls,
        question: str,
        chunks: List[Chunk],
    ) -> str:
        """
        Create the final prompt.
        """

        context = "\n\n".join(
            chunk.text
            for chunk in chunks
        )

        return f"""
{cls.SYSTEM_PROMPT}

Context:

{context}

Question:

{question}

Answer:
"""