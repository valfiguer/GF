<?php
$leagueData = LEAGUES[$currentLeague] ?? null;
if (!$leagueData) return;
$teams = $leagueData['teams'] ?? [];
?>
<nav class="gf-team-nav" aria-label="<?= e($lang === 'en' ? $leagueData['name_en'] : $leagueData['name_es']) ?>">
    <div class="gf-container">
        <button class="gf-team-nav__arrow gf-team-nav__arrow--left hidden" aria-label="Scroll left">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="gf-team-nav__scroll">
            <?php foreach ($teams as $tSlug => $team): ?>
            <a href="/league/<?= e($currentLeague) ?>/<?= e($tSlug) ?>" data-ajax-page
               class="gf-team-nav__item<?= ($currentTeam ?? '') === $tSlug ? ' gf-team-nav__item--active' : '' ?>">
                <img src="<?= e($team['logo']) ?>" alt="<?= e($lang === 'en' ? $team['name_en'] : $team['name_es']) ?>" class="gf-team-nav__logo" width="24" height="24">
                <span class="gf-team-nav__name"><?= e($lang === 'en' ? $team['name_en'] : $team['name_es']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <button class="gf-team-nav__arrow gf-team-nav__arrow--right" aria-label="Scroll right">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
</nav>
