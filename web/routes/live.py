"""Live matches route."""
import logging
from fastapi import APIRouter, Request
from fastapi.responses import HTMLResponse

from db import get_repository

logger = logging.getLogger(__name__)
router = APIRouter()


@router.get("/live", response_class=HTMLResponse)
async def live(request: Request):
    """Live matches page with auto-refresh."""
    repo = get_repository()
    templates = request.app.state.templates

    matches = repo.get_active_live_matches()

    # Enrich matches with their events
    enriched = []
    for match in matches:
        events = repo.get_match_events(match['match_id'])
        enriched.append({**match, "events": events})

    return templates.TemplateResponse("live.html", {
        "request": request,
        "matches": enriched,
    })
