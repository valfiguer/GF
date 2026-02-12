"""Home page route."""
import logging
from fastapi import APIRouter, Request
from fastapi.responses import HTMLResponse

from db import get_repository

logger = logging.getLogger(__name__)
router = APIRouter()


@router.get("/", response_class=HTMLResponse)
async def home(request: Request, page: int = 1):
    """Homepage with featured articles and paginated grid."""
    repo = get_repository()
    templates = request.app.state.templates
    config = request.app.state.config
    per_page = config.web.articles_per_page

    featured = repo.get_featured_web_articles(limit=4)

    # If no featured articles, use latest high-score ones
    if not featured:
        latest = repo.get_latest_web_articles(limit=4)
        featured = latest

    # Load enough articles for the carousel (min 12) + grid below
    effective_per_page = max(per_page, 18)
    articles = repo.get_web_articles_paginated(page=page, per_page=effective_per_page)
    total = repo.get_web_article_count_by_sport()
    total_pages = max(1, (total + effective_per_page - 1) // effective_per_page)

    return templates.TemplateResponse("home.html", {
        "request": request,
        "featured": featured,
        "articles": articles,
        "page": page,
        "total_pages": total_pages,
        "total": total,
    })
