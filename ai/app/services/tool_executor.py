from app.tools.tool_registry import ToolRegistry


class ToolExecutor:

    def __init__(self):

        self.registry = ToolRegistry()

    def execute(
        self,
        tool_name: str,
        customer_id: int,
        arguments: dict,
    ):

        tool = self.registry.get(tool_name)

        if tool is None:
            raise Exception(
                f"Unknown tool '{tool_name}'"
            )

        return tool.execute(
            customer_id=customer_id,
            **arguments,
        )
        