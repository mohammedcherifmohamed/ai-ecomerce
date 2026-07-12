from app.providers.laravel_api import LaravelAPI
from app.tools.base_tool import BaseTool


class GetOrderStatusTool(BaseTool):
    def __init__(self):
        self.api = LaravelAPI()

    @property
    def name(self):
        return "get_order_status"

    @property
    def description(self):
        return (
            "Returns the status of a customer's order."
        )

    def execute(
        self,
        customer_id: int,
        order_id: int,
    ):
        return self.api.post(
            "/ai/orders/status",
            {
                "customer_id":customer_id,
                "order_id":order_id,
            }
        )