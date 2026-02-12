"""Editorial module for GoalFeed."""
from .copywriter import (
    Copywriter,
    get_copywriter,
    generate_caption,
    generate_digest_caption
)
from .article_writer import (
    ArticleWriter,
    GeneratedArticle,
    get_article_writer,
    generate_web_article
)

__all__ = [
    'Copywriter',
    'get_copywriter',
    'generate_caption',
    'generate_digest_caption',
    'ArticleWriter',
    'GeneratedArticle',
    'get_article_writer',
    'generate_web_article'
]
