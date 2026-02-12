"""Authentication routes: login, register, logout, Google OAuth, profile."""
import html
import logging

from fastapi import APIRouter, Request, Form
from fastapi.responses import HTMLResponse, RedirectResponse

from db import get_repository
from web.auth import (
    hash_password, verify_password,
    create_session, get_current_user, destroy_session,
    _make_initials,
)
from web.i18n import t, get_lang

logger = logging.getLogger(__name__)
router = APIRouter(prefix="/auth")


# ── Login ──

@router.get("/login", response_class=HTMLResponse)
async def login_page(request: Request, error: str = None):
    templates = request.app.state.templates
    lang = get_lang(request)
    user = get_current_user(request)
    if user:
        return RedirectResponse("/", status_code=302)
    return templates.TemplateResponse("auth/login.html", {
        "request": request,
        "error": error,
        "lang": lang,
    })


@router.post("/login")
async def login_submit(
    request: Request,
    email: str = Form(...),
    password: str = Form(...),
):
    lang = get_lang(request)
    repo = get_repository()
    user = repo.get_user_by_email(email.strip().lower())

    if not user or not user.get("password_hash"):
        return RedirectResponse(
            f"/auth/login?error={t('auth.error_invalid', lang)}",
            status_code=302,
        )

    if not verify_password(password, user["password_hash"]):
        return RedirectResponse(
            f"/auth/login?error={t('auth.error_invalid', lang)}",
            status_code=302,
        )

    response = RedirectResponse("/", status_code=302)
    create_session(user["id"], response)
    return response


# ── Register ──

@router.get("/register", response_class=HTMLResponse)
async def register_page(request: Request, error: str = None):
    templates = request.app.state.templates
    lang = get_lang(request)
    user = get_current_user(request)
    if user:
        return RedirectResponse("/", status_code=302)
    return templates.TemplateResponse("auth/register.html", {
        "request": request,
        "error": error,
        "lang": lang,
    })


@router.post("/register")
async def register_submit(
    request: Request,
    display_name: str = Form(...),
    email: str = Form(...),
    password: str = Form(...),
    password_confirm: str = Form(...),
):
    lang = get_lang(request)
    name = display_name.strip()
    email_clean = email.strip().lower()

    # Validate
    if not name or not email_clean or not password:
        return RedirectResponse(
            f"/auth/register?error={t('auth.error_fields', lang)}",
            status_code=302,
        )
    if len(password) < 8:
        return RedirectResponse(
            f"/auth/register?error={t('auth.error_password_short', lang)}",
            status_code=302,
        )
    if password != password_confirm:
        return RedirectResponse(
            f"/auth/register?error={t('auth.error_password_mismatch', lang)}",
            status_code=302,
        )

    repo = get_repository()
    if repo.get_user_by_email(email_clean):
        return RedirectResponse(
            f"/auth/register?error={t('auth.error_email_exists', lang)}",
            status_code=302,
        )

    # Create user
    initials = _make_initials(name)
    pw_hash = hash_password(password)
    user_id = repo.create_user(
        email=email_clean,
        display_name=html.escape(name),
        initials=html.escape(initials),
        password_hash=pw_hash,
        auth_provider="local",
    )

    response = RedirectResponse("/", status_code=302)
    create_session(user_id, response)
    return response


# ── Logout ──

@router.get("/logout")
async def logout(request: Request):
    response = RedirectResponse("/", status_code=302)
    destroy_session(request, response)
    return response


# ── Google OAuth ──

@router.get("/google")
async def google_login(request: Request):
    """Redirect user to Google's OAuth consent screen."""
    config = request.app.state.config
    client_id = config.web.google_client_id
    redirect_uri = config.web.google_redirect_uri

    if not client_id or not redirect_uri:
        return RedirectResponse("/auth/login?error=Google+OAuth+not+configured", status_code=302)

    google_auth_url = (
        "https://accounts.google.com/o/oauth2/v2/auth"
        f"?client_id={client_id}"
        f"&redirect_uri={redirect_uri}"
        "&response_type=code"
        "&scope=openid+email+profile"
        "&access_type=offline"
        "&prompt=select_account"
    )
    return RedirectResponse(google_auth_url, status_code=302)


@router.get("/google/callback")
async def google_callback(request: Request, code: str = None, error: str = None):
    """Handle the OAuth callback from Google."""
    if error or not code:
        return RedirectResponse("/auth/login?error=Google+login+cancelled", status_code=302)

    config = request.app.state.config
    client_id = config.web.google_client_id
    client_secret = config.web.google_client_secret
    redirect_uri = config.web.google_redirect_uri

    try:
        import httpx

        # Exchange code for tokens
        async with httpx.AsyncClient() as client:
            token_resp = await client.post(
                "https://oauth2.googleapis.com/token",
                data={
                    "code": code,
                    "client_id": client_id,
                    "client_secret": client_secret,
                    "redirect_uri": redirect_uri,
                    "grant_type": "authorization_code",
                },
            )
            token_data = token_resp.json()

        access_token = token_data.get("access_token")
        if not access_token:
            logger.error(f"Google token exchange failed: {token_data}")
            return RedirectResponse("/auth/login?error=Google+login+failed", status_code=302)

        # Get user info
        async with httpx.AsyncClient() as client:
            userinfo_resp = await client.get(
                "https://www.googleapis.com/oauth2/v2/userinfo",
                headers={"Authorization": f"Bearer {access_token}"},
            )
            userinfo = userinfo_resp.json()

        google_id = userinfo.get("id")
        email = userinfo.get("email", "").lower()
        name = userinfo.get("name", email.split("@")[0])
        avatar = userinfo.get("picture")

        if not google_id or not email:
            return RedirectResponse("/auth/login?error=Google+login+failed", status_code=302)

        repo = get_repository()

        # Check if user exists by google_id
        user = repo.get_user_by_google_id(google_id)
        if not user:
            # Check by email (might have registered with email/password first)
            user = repo.get_user_by_email(email)
            if user:
                # Link Google ID to existing account
                repo.db.execute(
                    "UPDATE users SET google_id = %s, avatar_url = %s, auth_provider = 'google' WHERE id = %s",
                    (google_id, avatar, user["id"])
                )
            else:
                # Create new user
                initials = _make_initials(name)
                user_id = repo.create_user(
                    email=email,
                    display_name=html.escape(name),
                    initials=html.escape(initials),
                    auth_provider="google",
                    google_id=google_id,
                    avatar_url=avatar,
                )
                user = repo.get_user_by_id(user_id)

        response = RedirectResponse("/", status_code=302)
        create_session(user["id"], response)
        return response

    except Exception as e:
        logger.error(f"Google OAuth error: {e}")
        return RedirectResponse("/auth/login?error=Google+login+failed", status_code=302)


# ── Profile ──

@router.get("/profile", response_class=HTMLResponse)
async def profile_page(request: Request):
    templates = request.app.state.templates
    lang = get_lang(request)
    user = get_current_user(request)
    if not user:
        return RedirectResponse("/auth/login", status_code=302)
    return templates.TemplateResponse("auth/profile.html", {
        "request": request,
        "user": user,
        "lang": lang,
    })
