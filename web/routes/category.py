"""Category/sport filter route."""
import logging
from fastapi import APIRouter, Request, HTTPException
from fastapi.responses import HTMLResponse

from config import SPORT_DISPLAY
from db import get_repository

logger = logging.getLogger(__name__)
router = APIRouter()


@router.get("/category/{sport}", response_class=HTMLResponse)
async def category(request: Request, sport: str, page: int = 1):
    """Articles filtered by sport."""
    if sport not in SPORT_DISPLAY:
        raise HTTPException(status_code=404, detail="Categoría no encontrada")

    repo = get_repository()
    templates = request.app.state.templates
    config = request.app.state.config
    per_page = config.web.articles_per_page

    articles = repo.get_web_articles_paginated(page=page, per_page=per_page, sport=sport)
    total = repo.get_web_article_count_by_sport(sport=sport)
    total_pages = max(1, (total + per_page - 1) // per_page)

    sport_info = SPORT_DISPLAY[sport]

    return templates.TemplateResponse("category.html", {
        "request": request,
        "articles": articles,
        "sport": sport,
        "sport_info": sport_info,
        "page": page,
        "total_pages": total_pages,
        "total": total,
    })
