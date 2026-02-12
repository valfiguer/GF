"""
WSGI entry point for cPanel Passenger (BanaHost / shared hosting).
cPanel's "Setup Python App" looks for this file.
"""
import sys
import os
import asyncio
import io

# Ensure project root is in path
APP_DIR = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, APP_DIR)
os.chdir(APP_DIR)

from db import init_db
from web.app import create_app

# Initialize database
init_db()

# Create FastAPI app
fastapi_app = create_app()


def application(environ, start_response):
    """
    WSGI-compatible wrapper for the FastAPI (ASGI) app.
    Converts WSGI environ to ASGI scope and runs the app with asyncio.
    """
    # Build ASGI scope from WSGI environ
    headers = []
    for key, value in environ.items():
        if key.startswith("HTTP_"):
            header_name = key[5:].lower().replace("_", "-").encode("latin-1")
            headers.append((header_name, value.encode("latin-1")))
        elif key == "CONTENT_TYPE":
            headers.append((b"content-type", value.encode("latin-1")))
        elif key == "CONTENT_LENGTH" and value:
            headers.append((b"content-length", value.encode("latin-1")))

    path = environ.get("PATH_INFO", "/")
    query_string = environ.get("QUERY_STRING", "").encode("latin-1")

    scope = {
        "type": "http",
        "asgi": {"version": "3.0"},
        "http_version": environ.get("SERVER_PROTOCOL", "HTTP/1.1").split("/")[-1],
        "method": environ["REQUEST_METHOD"],
        "path": path,
        "query_string": query_string,
        "root_path": environ.get("SCRIPT_NAME", ""),
        "scheme": environ.get("wsgi.url_scheme", "http"),
        "server": (
            environ.get("SERVER_NAME", "localhost"),
            int(environ.get("SERVER_PORT", "80")),
        ),
        "headers": headers,
    }

    # Read request body
    try:
        content_length = int(environ.get("CONTENT_LENGTH", 0) or 0)
    except (ValueError, TypeError):
        content_length = 0
    body = environ["wsgi.input"].read(content_length) if content_length > 0 else b""

    # ASGI receive callable
    body_sent = False

    async def receive():
        nonlocal body_sent
        if not body_sent:
            body_sent = True
            return {"type": "http.request", "body": body, "more_body": False}
        # Wait for disconnect (won't happen in WSGI)
        await asyncio.sleep(999999)

    # ASGI send callable - collect response
    status_code = 200
    response_headers = []
    response_body = io.BytesIO()

    async def send(message):
        nonlocal status_code, response_headers
        if message["type"] == "http.response.start":
            status_code = message["status"]
            response_headers = [
                (name.decode("latin-1"), value.decode("latin-1"))
                for name, value in message.get("headers", [])
            ]
        elif message["type"] == "http.response.body":
            response_body.write(message.get("body", b""))

    # Run ASGI app synchronously
    loop = asyncio.new_event_loop()
    try:
        loop.run_until_complete(fastapi_app(scope, receive, send))
    finally:
        loop.close()

    # Send WSGI response
    status_phrases = {
        200: "OK", 201: "Created", 204: "No Content",
        301: "Moved Permanently", 302: "Found", 304: "Not Modified",
        400: "Bad Request", 403: "Forbidden", 404: "Not Found",
        405: "Method Not Allowed", 422: "Unprocessable Entity",
        500: "Internal Server Error",
    }
    status_str = f"{status_code} {status_phrases.get(status_code, 'Unknown')}"
    start_response(status_str, response_headers)
    return [response_body.getvalue()]
