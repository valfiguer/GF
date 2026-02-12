// GoalFeed Main JS — Theme, User Menu, Mobile Menu, Carousel, Comments

(function () {
    // --- Theme Management ---
    var THEME_KEY = 'gf-theme';
    var META_THEME = document.querySelector('meta[name="theme-color"]');

    function getPreferredTheme() {
        var stored = localStorage.getItem(THEME_KEY);
        if (stored === 'dark' || stored === 'light') return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        if (META_THEME) {
            META_THEME.content = theme === 'dark' ? '#1d1d1f' : '#f5f5f7';
        }
    }

    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-theme') || 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem(THEME_KEY, next);
        applyTheme(next);
    }

    // Apply on load
    applyTheme(getPreferredTheme());

    // System preference change listener
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
        if (!localStorage.getItem(THEME_KEY)) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });

    // --- Mock User Cookie ---
    // Set mock user cookie for "Carlos M." so comments work out of the box
    function ensureMockUser() {
        if (!document.cookie.match(/gf_user=/)) {
            document.cookie = 'gf_user=Carlos M.|CM; path=/; SameSite=Lax; max-age=31536000';
        }
    }
    ensureMockUser();

    // --- DOM Ready ---
    document.addEventListener('DOMContentLoaded', function () {
        // Theme toggle button
        var themeBtn = document.getElementById('theme-toggle');
        if (themeBtn) {
            themeBtn.addEventListener('click', toggleTheme);
        }

        // User dropdown
        var userMenu = document.getElementById('user-menu');
        if (userMenu) {
            var trigger = userMenu.querySelector('.gf-user-menu__trigger');
            if (trigger) {
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('open');
                });
            }
            document.addEventListener('click', function () {
                userMenu.classList.remove('open');
            });
        }

        // Mobile menu
        var mobileBtn = document.getElementById('mobile-menu-btn');
        var mobileMenu = document.getElementById('mobile-menu');
        if (mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', function () {
                mobileMenu.classList.toggle('open');
            });
        }

        // Lazy images
        document.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
            if (img.complete) {
                img.classList.add('loaded');
            } else {
                img.addEventListener('load', function () {
                    img.classList.add('loaded');
                });
                img.addEventListener('error', function () {
                    img.classList.add('loaded');
                });
            }
        });

        // --- Carousel ---
        initCarousel();

        // --- Comments ---
        initComments();
    });

    // --- Showcase Carousel (animated) ---
    function initCarousel() {
        var viewport = document.getElementById('showcase-viewport');
        var track = document.getElementById('showcase-track');
        var prevBtn = document.getElementById('showcase-prev');
        var nextBtn = document.getElementById('showcase-next');
        var dotsContainer = document.getElementById('showcase-dots');
        var progressBar = document.getElementById('showcase-progress');

        if (!track || !viewport) return;

        var items = track.querySelectorAll('.gf-showcase__item');
        if (items.length === 0) return;

        var AUTOPLAY_MS = 4000; // 4 seconds per page
        var currentPage = 0;
        var totalPages = 1;
        var autoTimer = null;
        var progressTimer = null;
        var progressStart = 0;
        var paused = false;

        function getVisibleCount() {
            var w = viewport.offsetWidth;
            if (w >= 1024) return 3;
            if (w >= 640) return 2;
            return 1;
        }

        function getGap() {
            return 16;
        }

        function calcTotalPages() {
            var visible = getVisibleCount();
            totalPages = Math.max(1, Math.ceil(items.length / visible));
            if (currentPage >= totalPages) currentPage = totalPages - 1;
        }

        function getOffset(page) {
            var visible = getVisibleCount();
            var gap = getGap();
            var itemW = (viewport.offsetWidth - gap * (visible - 1)) / visible;
            var idx = page * visible;
            return idx * (itemW + gap);
        }

        function goTo(page, resetAuto) {
            if (page < 0) page = totalPages - 1;
            if (page >= totalPages) page = 0;
            currentPage = page;

            var offset = getOffset(page);
            track.style.transform = 'translateX(-' + offset + 'px)';

            updateDots();
            if (resetAuto !== false) restartAutoplay();
        }

        function next() { goTo(currentPage + 1); }
        function prev() { goTo(currentPage - 1); }

        // Dots
        function buildDots() {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            for (var i = 0; i < totalPages; i++) {
                var dot = document.createElement('button');
                dot.className = 'gf-showcase__dot';
                dot.setAttribute('aria-label', 'Pagina ' + (i + 1));
                (function (idx) {
                    dot.addEventListener('click', function () { goTo(idx); });
                })(i);
                dotsContainer.appendChild(dot);
            }
            updateDots();
        }

        function updateDots() {
            if (!dotsContainer) return;
            var dots = dotsContainer.querySelectorAll('.gf-showcase__dot');
            dots.forEach(function (d, i) {
                d.classList.toggle('gf-showcase__dot--active', i === currentPage);
            });
        }

        // Progress bar animation
        function animateProgress() {
            if (!progressBar) return;
            progressStart = Date.now();

            cancelAnimationFrame(progressTimer);

            function tick() {
                if (paused) { progressTimer = requestAnimationFrame(tick); return; }
                var elapsed = Date.now() - progressStart;
                var pct = Math.min((elapsed / AUTOPLAY_MS) * 100, 100);
                progressBar.style.width = pct + '%';
                if (pct < 100) {
                    progressTimer = requestAnimationFrame(tick);
                }
            }
            progressTimer = requestAnimationFrame(tick);
        }

        // Autoplay
        function startAutoplay() {
            stopAutoplay();
            animateProgress();
            autoTimer = setTimeout(function () {
                next();
            }, AUTOPLAY_MS);
        }

        function stopAutoplay() {
            clearTimeout(autoTimer);
            cancelAnimationFrame(progressTimer);
            if (progressBar) progressBar.style.width = '0%';
        }

        function restartAutoplay() {
            stopAutoplay();
            startAutoplay();
        }

        // Pause on hover
        viewport.addEventListener('mouseenter', function () {
            paused = true;
            clearTimeout(autoTimer);
        });

        viewport.addEventListener('mouseleave', function () {
            paused = false;
            // Resume with remaining time
            var elapsed = Date.now() - progressStart;
            var remaining = Math.max(500, AUTOPLAY_MS - elapsed);
            autoTimer = setTimeout(function () {
                next();
            }, remaining);
        });

        // Touch/swipe support
        var touchStartX = 0;
        var touchDelta = 0;

        viewport.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
            touchDelta = 0;
            paused = true;
            clearTimeout(autoTimer);
        }, { passive: true });

        viewport.addEventListener('touchmove', function (e) {
            touchDelta = e.touches[0].clientX - touchStartX;
        }, { passive: true });

        viewport.addEventListener('touchend', function () {
            paused = false;
            if (Math.abs(touchDelta) > 50) {
                if (touchDelta < 0) next(); else prev();
            } else {
                restartAutoplay();
            }
        });

        // Arrow buttons
        if (prevBtn) prevBtn.addEventListener('click', prev);
        if (nextBtn) nextBtn.addEventListener('click', next);

        // Recalc on resize
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                calcTotalPages();
                buildDots();
                goTo(currentPage, false);
            }, 200);
        });

        // Init
        calcTotalPages();
        buildDots();
        goTo(0);
    }

    // --- Comments Logic ---
    function initComments() {
        var section = document.getElementById('comments-section');
        if (!section) return;

        var articleId = section.getAttribute('data-article-id');
        var list = document.getElementById('comments-list');
        var form = document.getElementById('comment-form');
        var textarea = document.getElementById('comment-text');
        var charCount = document.getElementById('comment-char-count');

        // Load comments
        loadComments(articleId, list);

        // Character counter
        if (textarea && charCount) {
            textarea.addEventListener('input', function () {
                charCount.textContent = textarea.value.length + ' / 2000';
            });
        }

        // Submit comment
        if (form && textarea) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var text = textarea.value.trim();
                if (!text) return;

                var submitBtn = form.querySelector('.gf-comments__submit');
                if (submitBtn) submitBtn.disabled = true;

                fetch('/api/comments/' + articleId, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ comment_text: text })
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    textarea.value = '';
                    if (charCount) charCount.textContent = '0 / 2000';
                    // Reload comments
                    loadComments(articleId, list);
                })
                .catch(function (err) {
                    console.error('Error posting comment:', err);
                })
                .finally(function () {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function formatTime(dateStr) {
        if (!dateStr || dateStr === 'ahora') return 'ahora';
        try {
            var d = new Date(dateStr);
            var now = new Date();
            var diff = (now - d) / 1000;
            if (diff < 60) return 'ahora';
            if (diff < 3600) return Math.floor(diff / 60) + ' min';
            if (diff < 86400) return Math.floor(diff / 3600) + ' h';
            return d.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        } catch (e) {
            return dateStr;
        }
    }

    function loadComments(articleId, container) {
        if (!container) return;

        fetch('/api/comments/' + articleId)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.comments || data.comments.length === 0) {
                    container.innerHTML = '<div class="gf-comments__empty">Se el primero en comentar</div>';
                    return;
                }

                var html = '';
                data.comments.forEach(function (c) {
                    html += '<div class="gf-comment">' +
                        '<div class="gf-comment__avatar">' + escapeHtml(c.user_initials) + '</div>' +
                        '<div class="gf-comment__body">' +
                            '<div class="gf-comment__header">' +
                                '<span class="gf-comment__name">' + escapeHtml(c.user_name) + '</span>' +
                                '<span class="gf-comment__time">' + formatTime(c.created_at) + '</span>' +
                            '</div>' +
                            '<p class="gf-comment__text">' + escapeHtml(c.comment_text) + '</p>' +
                        '</div>' +
                    '</div>';
                });
                container.innerHTML = html;
            })
            .catch(function (err) {
                console.error('Error loading comments:', err);
                container.innerHTML = '<div class="gf-comments__empty">Error cargando comentarios</div>';
            });
    }
})();
