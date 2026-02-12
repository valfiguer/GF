"""JSON API routes for AJAX."""
import logging
import html
from fastapi import APIRouter, Request
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field

from db import get_repository
from web.auth import get_current_user

logger = logging.getLogger(__name__)
router = APIRouter(prefix="/api")


@router.get("/articles")
async def api_articles(request: Request, page: int = 1, sport: str = None):
    """JSON API for articles."""
    repo = get_repository()
    config = request.app.state.config
    per_page = config.web.articles_per_page

    articles = repo.get_web_articles_paginated(page=page, per_page=per_page, sport=sport)
    total = repo.get_web_article_count_by_sport(sport=sport)

    return JSONResponse({
        "articles": articles,
        "page": page,
        "total": total,
        "per_page": per_page,
    })


@router.get("/live")
async def api_live(request: Request):
    """JSON API for live matches."""
    repo = get_repository()

    matches = repo.get_active_live_matches()
    enriched = []
    for match in matches:
        events = repo.get_match_events(match['match_id'])
        enriched.append({**match, "events": events})

    return JSONResponse({
        "matches": enriched,
    })


# --- Comments API ---

@router.get("/comments/{web_article_id}")
async def api_get_comments(web_article_id: int):
    """Get comments for an article."""
    repo = get_repository()
    comments = repo.get_comments(web_article_id)
    return JSONResponse({"comments": comments})


class CommentCreate(BaseModel):
    comment_text: str = Field(..., min_length=1, max_length=2000)


@router.post("/comments/{web_article_id}")
async def api_post_comment(
    request: Request,
    web_article_id: int,
    body: CommentCreate,
):
    """Post a comment on an article. Requires authenticated session."""
    user = get_current_user(request)
    if not user:
        return JSONResponse({"error": "No autenticado"}, status_code=401)

    user_name = user["display_name"]
    user_initials = user["initials"]

    # Sanitize
    clean_text = html.escape(body.comment_text.strip())
    if not clean_text:
        return JSONResponse({"error": "Comentario vacio"}, status_code=400)

    repo = get_repository()
    comment_id = repo.add_comment(
        web_article_id=web_article_id,
        user_name=html.escape(user_name),
        user_initials=html.escape(user_initials),
        comment_text=clean_text,
        user_id=user["id"],
    )

    return JSONResponse({
        "id": comment_id,
        "user_name": user_name,
        "user_initials": user_initials,
        "comment_text": clean_text,
        "created_at": "now",
    }, status_code=201)
