"""
FastAPI application factory for GoalFeed Web Portal.
"""
import os
import logging
from pathlib import Path

from fastapi import FastAPI, Request
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.gzip import GZipMiddleware
from fastapi.responses import HTMLResponse, RedirectResponse

from config import get_config, SPORT_DISPLAY, STATUS_CONFIG
from web.i18n import t, get_lang, get_js_translations
from web.auth import get_current_user

logger = logging.getLogger(__name__)

# Paths
WEB_DIR = Path(__file__).parent
TEMPLATES_DIR = WEB_DIR / "templates"
STATIC_DIR = WEB_DIR / "static"

# SVG icon system — replaces emojis for professional look
# All icons use currentColor, 16x16 viewBox, inline-ready
_S = 'class="gf-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"'
_SL = 'stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"'

WEB_ICONS = {
    # ── Sport icons ──
    "football_eu": (
        f'<svg {_S}>'
        f'<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2"/>'
        f'<path d="M8 3.8l2 1.5-.8 2.4H6.8L6 5.3z" stroke="currentColor" stroke-width="1" {_SL}/>'
        f'<path d="M8 3.8V1.5M10 5.3l3-.8M9.2 7.7l2.3 2M6.8 7.7l-2.3 2M6 5.3l-3-.8" stroke="currentColor" stroke-width="0.9" {_SL}/>'
        '</svg>'
    ),
    "nba": (
        f'<svg {_S}>'
        f'<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2"/>'
        f'<line x1="1.5" y1="8" x2="14.5" y2="8" stroke="currentColor" stroke-width="1"/>'
        f'<path d="M8 1.5c2.5 2.5 2.5 10.5 0 13" stroke="currentColor" stroke-width="1"/>'
        f'<path d="M8 1.5c-2.5 2.5-2.5 10.5 0 13" stroke="currentColor" stroke-width="1"/>'
        '</svg>'
    ),
    "tennis": (
        f'<svg {_S}>'
        f'<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2"/>'
        f'<path d="M2.8 3.2c3.5 1.5 3.5 8.1 0 9.6" stroke="currentColor" stroke-width="1" {_SL}/>'
        f'<path d="M13.2 3.2c-3.5 1.5-3.5 8.1 0 9.6" stroke="currentColor" stroke-width="1" {_SL}/>'
        '</svg>'
    ),

    # ── Status icons ──
    "CONFIRMADO": (
        f'<svg {_S}>'
        f'<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/>'
        f'<path d="M5.5 8l2 2.5L11 6" stroke="currentColor" stroke-width="1.4" {_SL}/>'
        '</svg>'
    ),
    "RUMOR": (
        f'<svg {_S}>'
        f'<path d="M2.5 2.5h11v8H7l-2.5 2v-2h-2z" stroke="currentColor" stroke-width="1.2" {_SL}/>'
        f'<circle cx="5.8" cy="6.5" r="0.7" fill="currentColor"/>'
        f'<circle cx="8" cy="6.5" r="0.7" fill="currentColor"/>'
        f'<circle cx="10.2" cy="6.5" r="0.7" fill="currentColor"/>'
        '</svg>'
    ),
    "EN_DESARROLLO": (
        f'<svg {_S}>'
        f'<path d="M13.5 8A5.5 5.5 0 1 1 12 4.5" stroke="currentColor" stroke-width="1.3" {_SL}/>'
        f'<path d="M13.5 2.5v3h-3" stroke="currentColor" stroke-width="1.3" {_SL}/>'
        '</svg>'
    ),

    # ── Live event icons ──
    "event_goal": (
        f'<svg {_S}>'
        f'<circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.1"/>'
        f'<path d="M8 4.5l1.3 1-.5 1.6H7.2l-.5-1.6z" stroke="currentColor" stroke-width="0.8" {_SL}/>'
        '</svg>'
    ),
    "event_red_card": (
        '<svg class="gf-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">'
        '<rect x="4.5" y="2" width="7" height="12" rx="1.2" fill="currentColor"/>'
        '</svg>'
    ),
    "event_var": (
        f'<svg {_S}>'
        f'<rect x="2" y="3.5" width="12" height="7.5" rx="1" stroke="currentColor" stroke-width="1.2"/>'
        f'<line x1="8" y1="11" x2="8" y2="13" stroke="currentColor" stroke-width="1.2"/>'
        f'<line x1="5.5" y1="13" x2="10.5" y2="13" stroke="currentColor" stroke-width="1.2" {_SL}/>'
        '</svg>'
    ),
    "event_penalty_miss": (
        f'<svg {_S}>'
        f'<line x1="4.5" y1="4.5" x2="11.5" y2="11.5" stroke="currentColor" stroke-width="1.5" {_SL}/>'
        f'<line x1="11.5" y1="4.5" x2="4.5" y2="11.5" stroke="currentColor" stroke-width="1.5" {_SL}/>'
        '</svg>'
    ),
    "event_default": (
        '<svg class="gf-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">'
        '<circle cx="8" cy="8" r="3" fill="currentColor"/>'
        '</svg>'
    ),
}

# Clean up helper vars
del _S, _SL


def create_app() -> FastAPI:
    """Create and configure the FastAPI application."""
    config = get_config()

    app = FastAPI(
        title="GoalFeed",
        description="Portal de noticias deportivas",
        docs_url=None,
        redoc_url=None,
    )

    # Middleware
    app.add_middleware(GZipMiddleware, minimum_size=500)

    # Static files
    STATIC_DIR.mkdir(parents=True, exist_ok=True)
    app.mount("/static", StaticFiles(directory=str(STATIC_DIR)), name="static")

    # Ensure article images directory exists
    images_dir = STATIC_DIR / "images" / "articles"
    images_dir.mkdir(parents=True, exist_ok=True)

    # Templates
    templates = Jinja2Templates(directory=str(TEMPLATES_DIR))

    # Store templates and config in app state
    app.state.templates = templates
    app.state.config = config

    # Add template globals
    templates.env.globals.update({
        "SPORT_DISPLAY": SPORT_DISPLAY,
        "STATUS_CONFIG": STATUS_CONFIG,
        "ICONS": WEB_ICONS,
        "site_name": "GoalFeed",
        "base_url": config.web.base_url,
        "t": t,
        "get_lang": get_lang,
        "get_js_translations": get_js_translations,
        "get_current_user": get_current_user,
    })

    # Register routes
    from web.routes.home import router as home_router
    from web.routes.article import router as article_router
    from web.routes.category import router as category_router
    from web.routes.live import router as live_router
    from web.routes.api import router as api_router
    from web.routes.sitemap import router as sitemap_router
    from web.routes.auth import router as auth_router

    app.include_router(home_router)
    app.include_router(article_router)
    app.include_router(category_router)
    app.include_router(live_router)
    app.include_router(api_router)
    app.include_router(sitemap_router)
    app.include_router(auth_router)

    # Language switcher route
    @app.get("/set-lang/{lang}")
    async def set_lang(request: Request, lang: str):
        lang = lang if lang in ("es", "en") else "es"
        referer = request.headers.get("referer", "/")
        response = RedirectResponse(url=referer, status_code=302)
        response.set_cookie("gf_lang", lang, max_age=365 * 24 * 3600, path="/", samesite="lax")
        return response

    logger.info("GoalFeed web app created")
    return app
