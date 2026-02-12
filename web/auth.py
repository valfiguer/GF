"""
GoalFeed authentication helpers.
Password hashing, session management, and current-user resolution.
"""
import secrets
import logging
from datetime import datetime, timedelta
from typing import Optional, Dict

import bcrypt
from fastapi import Request
from fastapi.responses import Response

from db import get_repository
from utils.timeutils import utc_now, datetime_to_iso

logger = logging.getLogger(__name__)

SESSION_COOKIE = "gf_session"
SESSION_MAX_AGE_DAYS = 30


# ── Password hashing ──

def hash_password(password: str) -> str:
    """Hash a plaintext password with bcrypt."""
    return bcrypt.hashpw(password.encode("utf-8"), bcrypt.gensalt()).decode("utf-8")


def verify_password(password: str, hashed: str) -> bool:
    """Verify a plaintext password against a bcrypt hash."""
    try:
        return bcrypt.checkpw(password.encode("utf-8"), hashed.encode("utf-8"))
    except Exception:
        return False


# ── Session management ──

def create_session(user_id: int, response: Response) -> str:
    """
    Create a new session for *user_id*, store it in the DB,
    and set the session cookie on *response*.
    Returns the session token.
    """
    token = secrets.token_hex(32)  # 64-char hex string
    expires_at = utc_now() + timedelta(days=SESSION_MAX_AGE_DAYS)

    repo = get_repository()
    repo.create_session(token, user_id, datetime_to_iso(expires_at))
    repo.update_user_last_login(user_id)

    response.set_cookie(
        SESSION_COOKIE,
        token,
        max_age=SESSION_MAX_AGE_DAYS * 86400,
        path="/",
        httponly=True,
        samesite="lax",
    )
    return token


def get_current_user(request: Request) -> Optional[Dict]:
    """
    Read the session cookie, look up the session in the DB,
    and return the user dict (or None if not authenticated).
    """
    token = request.cookies.get(SESSION_COOKIE)
    if not token:
        return None

    repo = get_repository()
    user = repo.get_user_by_session(token)
    return user


def destroy_session(request: Request, response: Response):
    """Delete the session from DB and clear the cookie."""
    token = request.cookies.get(SESSION_COOKIE)
    if token:
        repo = get_repository()
        repo.delete_session(token)

    response.delete_cookie(SESSION_COOKIE, path="/")


def _make_initials(name: str) -> str:
    """Generate 2-letter initials from a display name."""
    parts = name.strip().split()
    if len(parts) >= 2:
        return (parts[0][0] + parts[-1][0]).upper()
    if parts:
        return parts[0][:2].upper()
    return "??"
