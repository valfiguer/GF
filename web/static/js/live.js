// GoalFeed Live — Auto-refresh for /live page

(function () {
    var REFRESH_INTERVAL = 30000; // 30 seconds
    var timeEl = document.getElementById('live-update-time');

    // SVG icons for event types (matching server-side WEB_ICONS)
    var EVENT_ICONS = {
        goal: '<svg class="gf-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.1"/><path d="M8 4.5l1.3 1-.5 1.6H7.2l-.5-1.6z" stroke="currentColor" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        red_card: '<svg class="gf-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><rect x="4.5" y="2" width="7" height="12" rx="1.2" fill="currentColor"/></svg>',
        'var': '<svg class="gf-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="3.5" width="12" height="7.5" rx="1" stroke="currentColor" stroke-width="1.2"/><line x1="8" y1="11" x2="8" y2="13" stroke="currentColor" stroke-width="1.2"/><line x1="5.5" y1="13" x2="10.5" y2="13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>',
        penalty_miss: '<svg class="gf-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="4.5" y1="4.5" x2="11.5" y2="11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><line x1="11.5" y1="4.5" x2="4.5" y2="11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        default: '<svg class="gf-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="3" fill="currentColor"/></svg>'
    };

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function getEventIcon(eventType) {
        return EVENT_ICONS[eventType] || EVENT_ICONS['default'];
    }

    function refreshLive() {
        fetch('/api/live')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (timeEl) {
                    var now = new Date();
                    timeEl.textContent = 'Actualizado ' + now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                }

                var container = document.getElementById('live-matches-container');
                if (!container) return;

                if (!data.matches || data.matches.length === 0) {
                    container.innerHTML = '<div class="gf-empty"><p class="gf-empty__text">No hay partidos en vivo en este momento.</p></div>';
                    return;
                }

                var html = '<div class="gf-live-list">';
                data.matches.forEach(function (match) {
                    html += buildMatchCard(match);
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(function (err) {
                console.error('Error refreshing live data:', err);
            });
    }

    function buildMatchCard(match) {
        var statusHtml = '';
        if (['1H', '2H', 'ET', 'LIVE'].indexOf(match.match_status) !== -1) {
            statusHtml = '<span class="gf-live-dot"></span>' +
                '<span class="gf-live-status--live">' + escapeHtml(match.current_minute || '') + "'" + '</span>';
        } else if (match.match_status === 'HT') {
            statusHtml = '<span class="gf-live-status--ht">Descanso</span>';
        } else if (['FT', 'AET'].indexOf(match.match_status) !== -1) {
            statusHtml = '<span class="gf-live-status--ft">Final</span>';
        } else {
            statusHtml = '<span class="gf-live-status--ft">' + escapeHtml(match.match_status || '') + '</span>';
        }

        var eventsHtml = '';
        if (match.events && match.events.length > 0) {
            eventsHtml = '<div class="gf-live-events">';
            match.events.forEach(function (evt) {
                var iconClass = 'gf-live-event__icon--default';
                if (evt.event_type === 'goal') iconClass = 'gf-live-event__icon--goal';
                else if (evt.event_type === 'red_card') iconClass = 'gf-live-event__icon--red';
                else if (evt.event_type === 'var') iconClass = 'gf-live-event__icon--var';

                eventsHtml += '<div class="gf-live-event">' +
                    '<span class="gf-live-event__minute">' + escapeHtml(evt.event_minute || '') + "'</span>" +
                    '<span class="gf-live-event__icon ' + iconClass + '">' + getEventIcon(evt.event_type) + '</span>' +
                    '<span class="gf-live-event__player">' + escapeHtml(evt.event_player || '') + '</span>' +
                    (evt.event_detail ? '<span class="gf-live-event__detail">(' + escapeHtml(evt.event_detail) + ')</span>' : '') +
                    '</div>';
            });
            eventsHtml += '</div>';
        }

        return '<div class="gf-live-card">' +
            '<div class="gf-live-header">' +
                '<span class="gf-live-header__league">' + escapeHtml(match.league_name || '') + '</span>' +
                '<span class="gf-live-header__status">' + statusHtml + '</span>' +
            '</div>' +
            '<div class="gf-live-score">' +
                '<div class="gf-live-score__inner">' +
                    '<span class="gf-live-score__team gf-live-score__team--home">' + escapeHtml(match.home_team) + '</span>' +
                    '<span class="gf-live-score__result">' + escapeHtml(String(match.home_score)) + ' - ' + escapeHtml(String(match.away_score)) + '</span>' +
                    '<span class="gf-live-score__team gf-live-score__team--away">' + escapeHtml(match.away_team) + '</span>' +
                '</div>' +
            '</div>' +
            eventsHtml +
            '</div>';
    }

    // Start auto-refresh
    setInterval(refreshLive, REFRESH_INTERVAL);
})();
