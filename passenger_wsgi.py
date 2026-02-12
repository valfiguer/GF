"""
WSGI entry point for cPanel Passenger (BanaHost / shared hosting).
cPanel's "Setup Python App" looks for this file.
"""
import sys
import os

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

# Passenger expects a callable named 'application'
# FastAPI is ASGI, but cPanel Passenger supports WSGI.
# We use a2wsgi to bridge ASGI -> WSGI.
try:
    from a2wsgi import ASGIMiddleware
    application = ASGIMiddleware(fastapi_app)
except ImportError:
    # Fallback: try uvicorn as ASGI server if a2wsgi not available
    # This path is used when running directly, not via Passenger
    application = fastapi_app
