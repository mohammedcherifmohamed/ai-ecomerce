import re
import time
import logging

import mysql.connector

from app.core.config import settings
from app.schemas.sql_schema import SqlQueryResponse

logger = logging.getLogger(__name__)

ALLOWED_KEYWORDS = {"SELECT", "SHOW", "DESCRIBE", "DESC", "EXPLAIN", "WITH"}

BLOCKED_KEYWORDS = {
    "INSERT", "UPDATE", "DELETE", "DROP", "ALTER", "TRUNCATE",
    "CREATE", "GRANT", "REVOKE", "EXEC", "EXECUTE", "CALL",
    "INTO OUTFILE", "LOAD DATA",
}


def validate(query: str) -> str:
    cleaned = re.sub(r"--.*$", "", query, flags=re.MULTILINE)
    cleaned = re.sub(r"/\*.*?\*/", "", cleaned, flags=re.DOTALL)
    cleaned = cleaned.strip().rstrip(";").strip()

    if not cleaned:
        raise ValueError("Query is empty after removing comments.")

    if ";" in cleaned:
        raise ValueError("Only a single SQL statement is allowed.")

    match = re.match(r"(\w+)", cleaned, re.IGNORECASE)
    if not match:
        raise ValueError("Could not parse SQL statement.")

    leading = match.group(1).upper()
    if leading not in ALLOWED_KEYWORDS:
        raise ValueError(
            f"Statement type '{leading}' not allowed. "
            f"Only: {', '.join(sorted(ALLOWED_KEYWORDS))}."
        )

    upper = cleaned.upper()
    for blocked in BLOCKED_KEYWORDS:
        if re.search(rf"\b{re.escape(blocked)}\b", upper):
            raise ValueError(f"Use of '{blocked}' is not allowed.")

    if "@" in cleaned:
        raise ValueError("Use of user variables (@) is not allowed.")

    return cleaned


def execute(query: str) -> dict:
    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(
            host=settings.DB_HOST,
            port=settings.DB_PORT,
            user=settings.DB_READ_USERNAME,
            password=settings.DB_READ_PASSWORD,
            database=settings.DB_DATABASE,
            charset="utf8mb4",
            connection_timeout=10,
        )

        cursor = conn.cursor()
        cursor.execute(query)

        columns = [desc[0] for desc in cursor.description] if cursor.description else []
        rows = []
        row_count = 0
        for row in cursor:
            cleaned_row = [
                str(item) if not isinstance(item, (str, int, float, type(None)))
                else item
                for item in row
            ]
            rows.append(cleaned_row)
            row_count += 1
            if row_count >= 500:
                logger.warning("Query result truncated at 500 rows.")
                break

        return {
            "columns": columns,
            "rows": rows,
            "row_count": row_count,
        }

    except mysql.connector.Error as e:
        raise RuntimeError(f"MySQL error: {e.msg}") from e
    finally:
        if cursor:
            try:
                cursor.close()
            except Exception:
                pass
        if conn and conn.is_connected():
            try:
                conn.close()
            except Exception:
                pass


def execute_sql_query(query: str) -> SqlQueryResponse:
    start = time.perf_counter()
    try:
        validated = validate(query)
        result = execute(validated)
        elapsed = (time.perf_counter() - start) * 1000

        logger.info(
            "SQL query executed in %.2fms — %d rows returned",
            elapsed,
            result["row_count"],
        )

        return SqlQueryResponse(
            success=True,
            columns=result["columns"],
            rows=result["rows"],
            row_count=result["row_count"],
            execution_time_ms=round(elapsed, 2),
        )

    except (ValueError, RuntimeError) as e:
        elapsed = (time.perf_counter() - start) * 1000
        logger.warning("SQL query failed after %.2fms: %s", elapsed, str(e))
        return SqlQueryResponse(
            success=False,
            error=str(e),
            execution_time_ms=round(elapsed, 2),
        )
