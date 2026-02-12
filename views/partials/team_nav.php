<?php
$leagueData = LEAGUES[$currentLeague] ?? null;
if (!$leagueData) return;
$teams = $leagueData['teams'] ?? [];
?>
<nav class="gf-team-nav" aria-label="<?= e($lang === 'en' ? $leagueData['name_en'] : $leagueData['name_es']) ?>">
    <div class="gf-container">
        <div class="gf-team-nav__scroll">
            <?php foreach ($teams as $tSlug => $team): ?>
            <a href="/league/<?= e($currentLeague) ?>/<?= e($tSlug) ?>"
               class="gf-team-nav__item<?= ($currentTeam ?? '') === $tSlug ? ' gf-team-nav__item--active' : '' ?>">
                <img src="<?= e($team['logo']) ?>" alt="" class="gf-team-nav__logo" width="24" height="24">
                <span class="gf-team-nav__name"><?= e($lang === 'en' ? $team['name_en'] : $team['name_es']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
