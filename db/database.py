"""
Database connection and initialization for GoalFeed.
MySQL/MariaDB backend via PyMySQL.
"""
import pymysql
import pymysql.cursors
import os
import logging
from typing import Optional
from contextlib import contextmanager

logger = logging.getLogger(__name__)


class Database:
    """MySQL/MariaDB database connection manager."""

    def __init__(self, host: str, user: str, password: str, database: str, charset: str = "utf8mb4"):
        self.host = host
        self.user = user
        self.password = password
        self.database = database
        self.charset = charset
        self._connection: Optional[pymysql.connections.Connection] = None

    def connect(self) -> pymysql.connections.Connection:
        """Get or create a database connection."""
        if self._connection is None or not self._connection.open:
            self._connection = pymysql.connect(
                host=self.host,
                user=self.user,
                password=self.password,
                database=self.database,
                charset=self.charset,
                cursorclass=pymysql.cursors.DictCursor,
                autocommit=False,
            )
            logger.info(f"Connected to MySQL database: {self.database}@{self.host}")
        return self._connection

    def _ping(self):
        """Reconnect if the connection was lost."""
        try:
            self.connect().ping(reconnect=True)
        except Exception:
            self._connection = None
            self.connect()

    @contextmanager
    def get_cursor(self):
        """Context manager for database cursor."""
        self._ping()
        conn = self.connect()
        cursor = conn.cursor()
        try:
            yield cursor
            conn.commit()
        except Exception as e:
            conn.rollback()
            logger.error(f"Database error: {e}")
            raise
        finally:
            cursor.close()

    def execute(self, query: str, params: tuple = ()):
        """Execute a query and return the cursor."""
        self._ping()
        conn = self.connect()
        cursor = conn.cursor()
        cursor.execute(query, params)
        conn.commit()
        return cursor

    def executemany(self, query: str, params_list: list):
        """Execute a query with multiple parameter sets."""
        self._ping()
        conn = self.connect()
        cursor = conn.cursor()
        cursor.executemany(query, params_list)
        conn.commit()
        return cursor

    def fetchone(self, query: str, params: tuple = ()) -> Optional[dict]:
        """Fetch a single row as dict."""
        self._ping()
        conn = self.connect()
        cursor = conn.cursor()
        cursor.execute(query, params)
        conn.commit()
        return cursor.fetchone()

    def fetchall(self, query: str, params: tuple = ()) -> list[dict]:
        """Fetch all rows as list of dicts."""
        self._ping()
        conn = self.connect()
        cursor = conn.cursor()
        cursor.execute(query, params)
        conn.commit()
        return cursor.fetchall()

    def init_schema(self, schema_path: Optional[str] = None):
        """Initialize database schema from SQL file."""
        if schema_path is None:
            schema_path = os.path.join(os.path.dirname(__file__), 'schema.sql')

        if not os.path.exists(schema_path):
            raise FileNotFoundError(f"Schema file not found: {schema_path}")

        with open(schema_path, 'r', encoding='utf-8') as f:
            schema_sql = f.read()

        self._ping()
        conn = self.connect()
        cursor = conn.cursor()
        for statement in schema_sql.split(';'):
            statement = statement.strip()
            if statement:
                cursor.execute(statement)
        conn.commit()

        # Also load web schema if it exists
        web_schema_path = os.path.join(os.path.dirname(__file__), 'schema_web.sql')
        if os.path.exists(web_schema_path):
            with open(web_schema_path, 'r', encoding='utf-8') as f:
                web_schema_sql = f.read()
            for statement in web_schema_sql.split(';'):
                statement = statement.strip()
                if statement:
                    cursor.execute(statement)
            conn.commit()
            logger.info("Web schema initialized")

        cursor.close()
        logger.info("Database schema initialized")

    def close(self):
        """Close the database connection."""
        if self._connection and self._connection.open:
            self._connection.close()
            self._connection = None
            logger.info("Database connection closed")


# Global database instance
_db_instance: Optional[Database] = None


def get_database() -> Database:
    """Get or create the global database instance."""
    global _db_instance

    if _db_instance is None:
        from config import get_config
        config = get_config()
        _db_instance = Database(
            host=config.db_host,
            user=config.db_user,
            password=config.db_password,
            database=config.db_name,
            charset=config.db_charset,
        )

    return _db_instance


def init_db() -> Database:
    """Initialize the database with schema."""
    db = get_database()
    db.init_schema()
    return db
