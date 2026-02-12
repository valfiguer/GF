#!/usr/bin/env python3
"""
GoalFeed Web Portal - Standalone server entry point.

Usage:
    python run_web.py
"""
import sys
import logging
from pathlib import Path

# Add project root to path
PROJECT_ROOT = Path(__file__).parent.absolute()
sys.path.insert(0, str(PROJECT_ROOT))

from config import get_config
from db import init_db


def main():
    import uvicorn
    from web.app import create_app

    # Setup basic logging
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s | %(levelname)-8s | %(name)s | %(message)s',
        datefmt='%H:%M:%S'
    )
    logger = logging.getLogger('goalfeed.web')

    # Initialize database
    init_db()

    config = get_config()
    app = create_app()

    logger.info(f"Starting GoalFeed Web at {config.web.base_url}")

    uvicorn.run(
        app,
        host=config.web.host,
        port=config.web.port,
        log_level="info"
    )


if __name__ == "__main__":
    main()
