"""Article detail route."""
import logging
from fastapi import APIRouter, Request, HTTPException
from fastapi.responses import HTMLResponse

from db import get_repository
from web.auth import get_current_user

logger = logging.getLogger(__name__)
router = APIRouter()


@router.get("/article/{slug}", response_class=HTMLResponse)
async def article_detail(request: Request, slug: str):
    """Display a single article."""
    repo = get_repository()
    templates = request.app.state.templates

    article = repo.get_web_article_by_slug(slug)
    if not article:
        raise HTTPException(status_code=404, detail="Article not found")

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

    # Real user from session
    current_user = get_current_user(request)

    return templates.TemplateResponse("article_detail.html", {
        "request": request,
        "article": article,
        "related": related,
        "comments": comments,
        "current_user": current_user,
    })
