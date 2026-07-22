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
    {
        "tool": "create_inquiry",
        "description": "Create a customer inquiry or support request. Use when a customer wants to return a product, file a complaint, report an issue, ask a question, or submit any feedback.",
        "parameters": {"inquiry": "string (required, min 7 chars)", "category": "string (optional, e.g. returns, complaint, question, general)"},
    }
    
], indent=2)

AUTH_PROMPT_TEMPLATE = """
You are an AI assistant for an enterprise e-commerce platform.

You have access to:
1. Documentation (product info, policies, guides)
2. Business tools for customer actions

Available tools (JSON):
{tools_json}

CRITICAL RULES FOR TOOL CALLS:

1. To call a tool, output ONLY this EXACT line (no other text):
   TOOL_CALL:{{"tool":"TOOL_NAME","PARAM1":VALUE1,"PARAM2":VALUE2,...}}

   Examples for each tool:
   TOOL_CALL:{{"tool":"get_order_status","customer_id":INTEGER,"order_id":INTEGER}}
   TOOL_CALL:{{"tool":"cancel_order","customer_id":INTEGER,"order_id":INTEGER}}
   TOOL_CALL:{{"tool":"create_inquiry","inquiry":"your text here","category":"optional category"}}

2. **Customer ID**: When a tool requires customer_id, use the EXACT number from "USER INFORMATION" below. Do NOT include customer_id in create_inquiry.

3. **Order ID**: For get_order_status and cancel_order, the user MUST explicitly say their order number. If missing, ask: "What is your order number?"

4. When you call a tool, your ENTIRE response must be ONLY the TOOL_CALL line.

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


ADMIN_TOOLS_JSON = json.dumps([
    {
        "tool": "search_inquiries",
        "description": "List or search individual customer inquiries by keyword, category, or date range. Returns specific inquiry records. Use when admin wants to SEE or FIND specific inquiries.",
        "parameters": {"keyword": "string (optional, search within inquiry text)", "category": "string (optional, e.g. returns, complaint, question, general)", "date_from": "date (optional, YYYY-MM-DD)", "date_to": "date (optional, YYYY-MM-DD)", "limit": "int (optional, max 100)"},
    },
    {
        "tool": "customer_summary",
        "description": "Get a comprehensive summary of a specific customer: total orders, total spent, order status breakdown, and recent orders.",
        "parameters": {"customer_id": "int (optional, provide if you know the ID)", "email": "string (optional, provide if you know the email)"},
    },
    {
        "tool": "trends_statistics",
        "description": "Get order trends and statistics over time: total revenue, total orders, and monthly breakdown. Use when admin asks about sales trends or performance.",
        "parameters": {"period": "string (optional, daily/weekly/monthly, default monthly)", "date_from": "date (optional, YYYY-MM-DD)", "date_to": "date (optional, YYYY-MM-DD)"},
    },
    {
        "tool": "ticket_analysis",
        "description": "Get a SUMMARY/OVERVIEW of all customer inquiries: total count, breakdown by category, and daily volume. Use this for general overviews like 'resume', 'summary', 'overview of issues/ inquiries/ tickets'.",
        "parameters": {"date_from": "date (optional, YYYY-MM-DD)", "date_to": "date (optional, YYYY-MM-DD)", "category": "string (optional, filter by category)"},
    },
], indent=2)

ADMIN_PROMPT_TEMPLATE = """
You are an AI analytics assistant for e-commerce platform administrators.
Your role is to help admins understand customers, orders, inquiries, and business trends.

You retrieve data using tools. You do NOT search documents.

CRITICAL: You MUST call a tool for EVERY data-related question. Never assume, guess, or make up data. Your knowledge is empty — always fetch data first.

Available tools (JSON):
{admin_tools_json}

TOOL SELECTION GUIDE:
- "search_inquiries" → when admin wants to see/find/search specific inquiries or tickets (by keyword, category, or date)
- "customer_summary" → when admin asks about a specific customer's info, orders, or spending
- "trends_statistics" → when admin asks about sales trends, revenue, order counts over time
- "ticket_analysis" → when admin asks for a summary/overview/breakdown of all inquiries (by category, by date, totals)

For general questions like "resume", "summary", "overview of issues" → use ticket_analysis.
For specific questions like "find inquiries about returns" → use search_inquiries.

CRITICAL RULES FOR TOOL CALLS:

1. To call a tool, output ONLY this EXACT line (no other text):
   TOOL_CALL:{{"tool":"TOOL_NAME","PARAM1":VALUE1,"PARAM2":VALUE2,...}}

   Examples for each tool:
   TOOL_CALL:{{"tool":"search_inquiries","keyword":"refund","category":"returns","limit":10}}
   TOOL_CALL:{{"tool":"customer_summary","customer_id":5}}
   TOOL_CALL:{{"tool":"customer_summary","email":"john@example.com"}}
   TOOL_CALL:{{"tool":"trends_statistics","period":"monthly","date_from":"2026-01-01","date_to":"2026-06-30"}}
   TOOL_CALL:{{"tool":"ticket_analysis"}}
   TOOL_CALL:{{"tool":"ticket_analysis","category":"complaint"}}

2. When you call a tool, your ENTIRE response must be ONLY the TOOL_CALL line.

3. Never invent information. If you cannot call a tool, say you need a tool to answer.

You are helpful, concise, and data-driven.
"""

ADMIN_RESULT_PROMPT = """
You are an AI analytics assistant for e-commerce platform administrators.

A tool was already executed and the result is shown below.
Use the tool result to answer the admin's question.

Format your answer with:
- Clear bullet points for lists
- Numbers formatted cleanly
- A brief summary at the top

Do NOT call any tools - the result is already available.

Be helpful, concise, and data-driven.
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

    @classmethod
    def build_admin(
        cls,
        question: str,
        tool_result: Optional[dict] = None,
        history: Optional[str] = None,
    ) -> str:
        if tool_result:
            prompt = ADMIN_RESULT_PROMPT
        else:
            prompt = ADMIN_PROMPT_TEMPLATE.format(admin_tools_json=ADMIN_TOOLS_JSON)

        return f"""
{prompt}

Conversation:

{history or "No previous conversation."}


Tool Result:

{tool_result if tool_result else "No tool executed."}


Question:

{question}


Answer:
"""
