"""
Image service for GoalFeed Web Portal.
Downloads and saves article images for local serving.
"""
import os
import logging
from pathlib import Path

from config import get_config
from media import download_image, process_image_with_watermark

logger = logging.getLogger(__name__)

PROJECT_ROOT = Path(__file__).parent.parent.absolute()


def save_article_image(
    image_url: str,
    slug: str,
    sport: str
) -> str | None:
    """
    Download, process and save an article image locally.

    Args:
        image_url: URL of the original image
        slug: Article slug (used as filename)
        sport: Sport type (for fallback)

    Returns:
        Filename (e.g. 'slug.jpg') or None on failure
    """
    config = get_config()

    # Build output path
    storage_path = config.web.image_storage_path
    if not os.path.isabs(storage_path):
        storage_path = os.path.join(PROJECT_ROOT, storage_path)

    os.makedirs(storage_path, exist_ok=True)

    filename = f"{slug}.jpg"
    output_path = os.path.join(storage_path, filename)

    # Skip if already exists
    if os.path.exists(output_path):
        logger.debug(f"Image already exists: {filename}")
        return filename

    try:
        # Download image
        image_data = download_image(image_url)
        if not image_data:
            logger.warning(f"Failed to download image for {slug}")
            return None

        # Process (resize to 1200px width + watermark)
        processed = process_image_with_watermark(image_data, target_width=1200)
        if not processed:
            logger.warning(f"Failed to process image for {slug}")
            return None

        # Save
        with open(output_path, 'wb') as f:
            f.write(processed)

        logger.info(f"Saved article image: {filename}")
        return filename

    except Exception as e:
        logger.error(f"Error saving article image for {slug}: {e}")
        return None
