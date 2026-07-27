from pydantic import BaseModel, Field
from typing import Optional


class SqlQueryRequest(BaseModel):
    query: str = Field(
        ...,
        min_length=1,
        max_length=8000,
        description="SQL query to execute (SELECT / SHOW / DESCRIBE / EXPLAIN only)",
    )
    callback_url: Optional[str] = Field(
        default=None,
        description="URL for webhook callback when async processing completes",
    )


class SqlQueryResponse(BaseModel):
    success: bool = Field(..., description="Whether the query executed successfully")
    columns: list[str] = Field(
        default_factory=list,
        description="Column names from the result set",
    )
    rows: list[list] = Field(
        default_factory=list,
        description="Result rows as arrays of values",
    )
    row_count: int = Field(default=0, description="Number of rows returned")
    execution_time_ms: float = Field(default=0.0, description="Query execution time")
    error: Optional[str] = Field(default=None, description="Error message if failed")


class AsyncSqlQueryResult(BaseModel):
    request_id: str = Field(..., description="Unique request ID for polling")
    status: str = Field(..., description="pending | completed | error")
    result: Optional[SqlQueryResponse] = Field(default=None)
