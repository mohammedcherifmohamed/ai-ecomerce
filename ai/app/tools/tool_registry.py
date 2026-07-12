from app.tools.order_tools import GetOrderStatusTool

class ToolRegistry:
    def __init__(self):
        
        self.tools = {}
        
        self.register(
            GetOrderStatusTool()
        )
        
    def register(self,tool):
        self.tools[tool.name] = tool
        
    def get(self,name):
        return self.tools.get(name)

    def all(self):
        return list(self.tools.values())