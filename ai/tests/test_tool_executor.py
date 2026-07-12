from app.services.tool_executor import ToolExecutor

executor = ToolExecutor()

response = executor.execute(
    tool_name="get_order_status",
    customer_id=2,
    arguments={
        "order_id": 4
    }
)

print(response)