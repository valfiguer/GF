"""Sitemap and robots.txt routes."""
import logging
from fastapi import APIRouter, Request
from fastapi.responses import Response

from config import get_config
from db import get_repository

logger = logging.getLogger(__name__)
router = APIRouter()


@router.get("/sitemap.xml")
async def sitemap(request: Request):
    """Dynamic sitemap."""
    config = get_config()
    base_url = config.web.base_url
    repo = get_repository()

    articles = repo.get_latest_web_articles(limit=500)

    xml = '<?xml version="1.0" encoding="UTF-8"?>\n'
    xml += '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'

    # Homepage
    xml += f'  <url><loc>{base_url}/</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>\n'

    # Category pages
    for sport in ["football_eu", "nba", "tennis"]:
        xml += f'  <url><loc>{base_url}/category/{sport}</loc><changefreq>hourly</changefreq><priority>0.8</priority></url>\n'

    # Live page
    xml += f'  <url><loc>{base_url}/live</loc><changefreq>always</changefreq><priority>0.9</priority></url>\n'

    # Articles
    for article in articles:
        lastmod = article.get('updated_at', article.get('created_at', ''))
        if lastmod:
            lastmod_tag = f'<lastmod>{lastmod[:10]}</lastmod>'
        else:
            lastmod_tag = ''
        xml += f'  <url><loc>{base_url}/article/{article["slug"]}</loc>{lastmod_tag}<priority>0.7</priority></url>\n'

    xml += '</urlset>'

    return Response(content=xml, media_type="application/xml")


@router.get("/robots.txt")
async def robots(request: Request):
    """Robots.txt."""
    config = get_config()
    base_url = config.web.base_url

    content = f"""User-agent: *
Allow: /

Sitemap: {base_url}/sitemap.xml
"""
    return Response(content=content, media_type="text/plain")
