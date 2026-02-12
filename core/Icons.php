<?php
/**
 * SVG icon system — replaces emojis for professional look.
 * All icons use currentColor, 16x16 viewBox, inline-ready.
 */

$_S  = 'class="gf-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"';
$_SL = 'stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"';

define('WEB_ICONS', [
    // ── Sport icons ──
    'football_eu' =>
        '<svg ' . $_S . '>'
        . '<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2"/>'
        . '<path d="M8 3.8l2 1.5-.8 2.4H6.8L6 5.3z" stroke="currentColor" stroke-width="1" ' . $_SL . '/>'
        . '<path d="M8 3.8V1.5M10 5.3l3-.8M9.2 7.7l2.3 2M6.8 7.7l-2.3 2M6 5.3l-3-.8" stroke="currentColor" stroke-width="0.9" ' . $_SL . '/>'
        . '</svg>',

    'nba' =>
        '<svg ' . $_S . '>'
        . '<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2"/>'
        . '<line x1="1.5" y1="8" x2="14.5" y2="8" stroke="currentColor" stroke-width="1"/>'
        . '<path d="M8 1.5c2.5 2.5 2.5 10.5 0 13" stroke="currentColor" stroke-width="1"/>'
        . '<path d="M8 1.5c-2.5 2.5-2.5 10.5 0 13" stroke="currentColor" stroke-width="1"/>'
        . '</svg>',

    'tennis' =>
        '<svg ' . $_S . '>'
        . '<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2"/>'
        . '<path d="M2.8 3.2c3.5 1.5 3.5 8.1 0 9.6" stroke="currentColor" stroke-width="1" ' . $_SL . '/>'
        . '<path d="M13.2 3.2c-3.5 1.5-3.5 8.1 0 9.6" stroke="currentColor" stroke-width="1" ' . $_SL . '/>'
        . '</svg>',

    // ── Status icons ──
    'CONFIRMADO' =>
        '<svg ' . $_S . '>'
        . '<circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/>'
        . '<path d="M5.5 8l2 2.5L11 6" stroke="currentColor" stroke-width="1.4" ' . $_SL . '/>'
        . '</svg>',

    'RUMOR' =>
        '<svg ' . $_S . '>'
        . '<path d="M2.5 2.5h11v8H7l-2.5 2v-2h-2z" stroke="currentColor" stroke-width="1.2" ' . $_SL . '/>'
        . '<circle cx="5.8" cy="6.5" r="0.7" fill="currentColor"/>'
        . '<circle cx="8" cy="6.5" r="0.7" fill="currentColor"/>'
        . '<circle cx="10.2" cy="6.5" r="0.7" fill="currentColor"/>'
        . '</svg>',

    'EN_DESARROLLO' =>
        '<svg ' . $_S . '>'
        . '<path d="M13.5 8A5.5 5.5 0 1 1 12 4.5" stroke="currentColor" stroke-width="1.3" ' . $_SL . '/>'
        . '<path d="M13.5 2.5v3h-3" stroke="currentColor" stroke-width="1.3" ' . $_SL . '/>'
        . '</svg>',

    // ── Live event icons ──
    'event_goal' =>
        '<svg ' . $_S . '>'
        . '<circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.1"/>'
        . '<path d="M8 4.5l1.3 1-.5 1.6H7.2l-.5-1.6z" stroke="currentColor" stroke-width="0.8" ' . $_SL . '/>'
        . '</svg>',

    'event_red_card' =>
        '<svg class="gf-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">'
        . '<rect x="4.5" y="2" width="7" height="12" rx="1.2" fill="currentColor"/>'
        . '</svg>',

    'event_var' =>
        '<svg ' . $_S . '>'
        . '<rect x="2" y="3.5" width="12" height="7.5" rx="1" stroke="currentColor" stroke-width="1.2"/>'
        . '<line x1="8" y1="11" x2="8" y2="13" stroke="currentColor" stroke-width="1.2"/>'
        . '<line x1="5.5" y1="13" x2="10.5" y2="13" stroke="currentColor" stroke-width="1.2" ' . $_SL . '/>'
        . '</svg>',

    'event_penalty_miss' =>
        '<svg ' . $_S . '>'
        . '<line x1="4.5" y1="4.5" x2="11.5" y2="11.5" stroke="currentColor" stroke-width="1.5" ' . $_SL . '/>'
        . '<line x1="11.5" y1="4.5" x2="4.5" y2="11.5" stroke="currentColor" stroke-width="1.5" ' . $_SL . '/>'
        . '</svg>',

    'event_default' =>
        '<svg class="gf-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">'
        . '<circle cx="8" cy="8" r="3" fill="currentColor"/>'
        . '</svg>',
]);

/** Get an icon SVG string by key. */
function icon(string $key): string {
    return WEB_ICONS[$key] ?? '';
}
