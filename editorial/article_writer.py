"""
Article Writer module for GoalFeed Web Portal.
Uses Claude API to generate full web articles from RSS summaries.
"""
import json
import re
import logging
from typing import Optional
from dataclasses import dataclass

from config import get_config

logger = logging.getLogger(__name__)


@dataclass
class GeneratedArticle:
    """Result of AI article generation."""
    headline: str
    subtitle: str
    body_html: str
    body_text: str
    meta_description: str
    meta_keywords: str
    slug: str


class ArticleWriter:
    """Generates web articles using Claude API."""

    def __init__(self, api_key: Optional[str] = None, model: Optional[str] = None):
        config = get_config()
        self.api_key = api_key or config.web.claude_api_key
        self.model = model or config.web.claude_model

        if not self.api_key:
            raise ValueError("Claude API key is required (set CLAUDE_API_KEY env var)")

        import anthropic
        self.client = anthropic.Anthropic(api_key=self.api_key)

    def generate_article(
        self,
        title: str,
        summary: str,
        sport: str,
        category: Optional[str] = None,
        status: Optional[str] = None,
        source_name: Optional[str] = None
    ) -> Optional[GeneratedArticle]:
        """
        Generate a full web article from an RSS item.

        Args:
            title: Original article title
            summary: Original article summary/excerpt
            sport: Sport classification
            category: Article category
            status: Article status (CONFIRMADO, RUMOR, etc.)
            source_name: Name of the original source

        Returns:
            GeneratedArticle or None on failure
        """
        sport_names = {
            "football_eu": "fútbol",
            "nba": "NBA / baloncesto",
            "tennis": "tenis"
        }
        sport_display = sport_names.get(sport, "deportes")

        status_instruction = ""
        if status == "RUMOR":
            status_instruction = "Usa lenguaje condicional (podría, se rumorea, según fuentes). Deja claro que no está confirmado."
        elif status == "CONFIRMADO":
            status_instruction = "Escribe con tono afirmativo, es información confirmada."
        elif status == "EN_DESARROLLO":
            status_instruction = "Indica que la noticia está en desarrollo y podría haber actualizaciones."

        prompt = f"""Eres un periodista deportivo experto que escribe para un portal web de noticias deportivas en español llamado GoalFeed.

Genera un artículo completo basado en la siguiente información:

**Título original:** {title}
**Resumen:** {summary or 'No disponible'}
**Deporte:** {sport_display}
**Categoría:** {category or 'general'}
**Estado:** {status or 'RUMOR'}
**Fuente:** {source_name or 'Medios deportivos'}

INSTRUCCIONES:
- Escribe en español, con tono periodístico profesional pero accesible
- El artículo debe tener 3-5 párrafos
- NO inventes datos, cifras, declaraciones ni nombres que no estén en la información proporcionada
- Si faltan detalles, mantén el artículo enfocado en lo que se sabe
- {status_instruction}
- El body_html debe usar etiquetas <p> para párrafos
- El slug debe ser URL-friendly: solo minúsculas, números y guiones, sin acentos ni caracteres especiales
- meta_keywords: 5-8 palabras clave separadas por comas

Responde ÚNICAMENTE con un JSON válido (sin markdown, sin ```):
{{
    "headline": "Titular llamativo y conciso",
    "subtitle": "Subtítulo que amplía el titular con contexto adicional",
    "body_html": "<p>Párrafo 1...</p><p>Párrafo 2...</p><p>Párrafo 3...</p>",
    "meta_description": "Descripción SEO de máximo 160 caracteres",
    "meta_keywords": "palabra1, palabra2, palabra3",
    "slug": "slug-url-friendly-del-articulo"
}}"""

        try:
            message = self.client.messages.create(
                model=self.model,
                max_tokens=2000,
                messages=[{"role": "user", "content": prompt}]
            )

            response_text = message.content[0].text.strip()

            # Try to extract JSON from response
            data = self._parse_json_response(response_text)
            if not data:
                logger.error(f"Failed to parse article JSON: {response_text[:200]}")
                return None

            # Clean slug
            slug = self._clean_slug(data.get("slug", ""))
            if not slug:
                slug = self._generate_slug(data.get("headline", title))

            # Extract plain text from HTML
            body_html = data.get("body_html", "")
            body_text = re.sub(r'<[^>]+>', '', body_html).strip()

            return GeneratedArticle(
                headline=data.get("headline", title),
                subtitle=data.get("subtitle", ""),
                body_html=body_html,
                body_text=body_text,
                meta_description=data.get("meta_description", "")[:160],
                meta_keywords=data.get("meta_keywords", ""),
                slug=slug
            )

        except Exception as e:
            logger.error(f"Error generating article with Claude: {e}")
            return None

    def _parse_json_response(self, text: str) -> Optional[dict]:
        """Parse JSON from Claude response, handling edge cases."""
        # Try direct parse
        try:
            return json.loads(text)
        except json.JSONDecodeError:
            pass

        # Try to extract JSON block from markdown code fences
        match = re.search(r'```(?:json)?\s*(\{.*?\})\s*```', text, re.DOTALL)
        if match:
            try:
                return json.loads(match.group(1))
            except json.JSONDecodeError:
                pass

        # Try to find JSON object in text
        match = re.search(r'\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}', text, re.DOTALL)
        if match:
            try:
                return json.loads(match.group(0))
            except json.JSONDecodeError:
                pass

        return None

    def _clean_slug(self, slug: str) -> str:
        """Clean and validate a slug."""
        slug = slug.lower().strip()
        # Remove accents manually
        replacements = {
            'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u',
            'ñ': 'n', 'ü': 'u'
        }
        for char, replacement in replacements.items():
            slug = slug.replace(char, replacement)
        # Only allow alphanumeric and hyphens
        slug = re.sub(r'[^a-z0-9-]', '-', slug)
        slug = re.sub(r'-+', '-', slug)
        slug = slug.strip('-')
        return slug[:120]

    def _generate_slug(self, title: str) -> str:
        """Generate a slug from a title."""
        import time
        slug = self._clean_slug(title)
        if not slug:
            slug = f"articulo-{int(time.time())}"
        return slug


# Module-level convenience function
_writer_instance: Optional[ArticleWriter] = None


def get_article_writer() -> Optional[ArticleWriter]:
    """Get or create the ArticleWriter instance. Returns None if API key not configured."""
    global _writer_instance
    if _writer_instance is None:
        config = get_config()
        if not config.web.claude_api_key:
            return None
        try:
            _writer_instance = ArticleWriter()
        except Exception as e:
            logger.error(f"Failed to initialize ArticleWriter: {e}")
            return None
    return _writer_instance


def generate_web_article(
    title: str,
    summary: str,
    sport: str,
    category: Optional[str] = None,
    status: Optional[str] = None,
    source_name: Optional[str] = None
) -> Optional[GeneratedArticle]:
    """Convenience function to generate a web article."""
    writer = get_article_writer()
    if not writer:
        logger.warning("ArticleWriter not available (no API key?)")
        return None
    return writer.generate_article(title, summary, sport, category, status, source_name)
