"""Article detail route."""
import logging
from fastapi import APIRouter, Request, HTTPException, Cookie
from fastapi.responses import HTMLResponse

from db import get_repository

logger = logging.getLogger(__name__)
router = APIRouter()


def _get_current_user(gf_user: str = None):
    """Parse mock user from cookie. Returns dict or None."""
    if not gf_user:
        return None
    parts = gf_user.split("|", 1)
    name = parts[0].strip()
    initials = parts[1].strip() if len(parts) > 1 else name[:2].upper()
    return {"name": name, "initials": initials}


@router.get("/article/{slug}", response_class=HTMLResponse)
async def article_detail(
    request: Request,
    slug: str,
    gf_user: str = Cookie(default=None),
):
    """Display a single article."""
    repo = get_repository()
    templates = request.app.state.templates

    article = repo.get_web_article_by_slug(slug)
    if not article:
        raise HTTPException(status_code=404, detail="Articulo no encontrado")

    # Increment view count
    repo.increment_view_count(slug)

    # Get related articles
    related = repo.get_related_web_articles(
        sport=article['sport'],
        exclude_slug=slug,
        limit=4
    )

    # Get comments
    comments = repo.get_comments(article['id'])

    # Mock user
    current_user = _get_current_user(gf_user)

    return templates.TemplateResponse("article_detail.html", {
        "request": request,
        "article": article,
        "related": related,
        "comments": comments,
        "current_user": current_user,
    })
