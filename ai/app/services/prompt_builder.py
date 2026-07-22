import json
from typing import List, Optional

from app.models.chunk import Chunk


TOOLS_JSON = json.dumps([
    {
        "tool": "get_order_status",
        "description": "Check the status of a customer's order by order ID. Returns current status (pending, processing, shipped, delivered, cancelled).",
        "parameters": {"customer_id": "int (required)", "order_id": "int (required)"},
    },
    {
        "tool": "cancel_order",
        "description": "Cancel a customer's order by order ID. Only orders with status pending or processing can be cancelled. Restocks inventory automatically.",
        "parameters": {"customer_id": "int (required)", "order_id": "int (required)"},
    },
], indent=2)

AUTH_PROMPT_TEMPLATE = """
You are an AI assistant for an enterprise e-commerce platform.

You have access to:
1. Documentation (product info, policies, guides)
2. Business tools for customer actions

Available tools (JSON):
{tools_json}

CRITICAL RULES FOR TOOL CALLS:

1. The ONLY valid TOOL_CALL format is JSON:
   TOOL_CALL:{{"tool":"tool_name","customer_id":INTEGER,"order_id":INTEGER}}

2. **Customer ID**: Use the EXACT number from "USER INFORMATION" below.

3. **Order ID**: The user MUST explicitly say their order number.
   If missing, ask: "What is your order number?"

4. When you call a tool, your ENTIRE response must be ONLY the TOOL_CALL line.
   Do NOT add any other text before or after it.

5. Never invent information. If documentation is insufficient, say so.

Be professional and helpful.
"""

NOAUTH_PROMPT_TEMPLATE = """
You are an AI assistant for an enterprise e-commerce platform.
You answer questions from documentation only.
However, the following tools exist on this platform:

{tools_json}

You do NOT have access to these tools because the user is not authenticated.
If the user asks about their order or wants to cancel an order, tell them they need to log in first.
Never invent information. Be professional and helpful.
"""

RESULT_PROMPT = """
You are an AI assistant for an enterprise e-commerce platform.

A tool was already executed and the result is shown below.
Use the tool result to answer the user's question professionally.
Do NOT call any tools - the result is already available.

Be professional and helpful.
"""


class PromptBuilder:

    @classmethod
    def build(
        cls,
        question: str,
        chunks: List[Chunk],
        tool_result: Optional[dict] = None,
        history: Optional[str] = None,
        customer_id: Optional[int] = None,
    ) -> str:
        print(customer_id)
        context = "\n\n".join(
            chunk.text
            for chunk in chunks
        )

        if tool_result:
            prompt = RESULT_PROMPT
            user_info = ""
        elif customer_id:
            prompt = AUTH_PROMPT_TEMPLATE.format(tools_json=TOOLS_JSON)
            user_info = f"Authenticated customer_id: {customer_id}"
        else:
            prompt = NOAUTH_PROMPT_TEMPLATE.format(tools_json=TOOLS_JSON)
            user_info = ""

        return f"""
{prompt}
{user_info}
Conversation:

{history or "No previous conversation."}


Documentation:

{context if context else "No relevant documentation."}


Tool Result:

{tool_result if tool_result else "No tool executed."}


Question:

{question}


Answer:
"""
