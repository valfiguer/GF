<?php $pageTitle = t('auth.profile_title', $lang); ?>
<div class="gf-container gf-auth">
    <div class="gf-auth__card">
        <div class="gf-auth__profile-header">
            <div class="gf-auth__profile-avatar"><?= e($user['initials']) ?></div>
            <div class="gf-auth__profile-info">
                <div class="gf-auth__profile-name"><?= e($user['display_name']) ?></div>
                <div class="gf-auth__profile-email"><?= e($user['email']) ?></div>
            </div>
        </div>

        <div class="gf-auth__profile-row">
            <span class="gf-auth__profile-label"><?= e(t('auth.member_since', $lang)) ?></span>
            <span class="gf-auth__profile-value"><?= e(!empty($user['created_at']) ? substr($user['created_at'], 0, 10) : '—') ?></span>
        </div>

        <div class="gf-auth__profile-row">
            <span class="gf-auth__profile-label"><?= e(t('auth.provider', $lang)) ?></span>
            <span class="gf-auth__profile-value">
                <?= ($user['auth_provider'] ?? '') === 'google' ? 'Google' : 'Email' ?>
            </span>
        </div>

        <div style="margin-top: 28px; text-align: center;">
            <a href="/auth/logout" class="gf-btn gf-btn--outline gf-btn--sm"><?= e(t('nav.logout', $lang)) ?></a>
        </div>
    </div>
</div>
