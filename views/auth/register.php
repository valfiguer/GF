<?php $pageTitle = t('auth.register_title', $lang); ?>
<div class="gf-container gf-auth">
    <div class="gf-auth__card">
        <h1 class="gf-auth__title"><?= e(t('auth.register_title', $lang)) ?></h1>

        <?php if (!empty($error)): ?>
        <div class="gf-auth__error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/auth/register">
            <div class="gf-auth__field">
                <label class="gf-auth__label" for="display_name"><?= e(t('auth.display_name', $lang)) ?></label>
                <input class="gf-auth__input" type="text" id="display_name" name="display_name" required autocomplete="name">
            </div>
            <div class="gf-auth__field">
                <label class="gf-auth__label" for="email"><?= e(t('auth.email', $lang)) ?></label>
                <input class="gf-auth__input" type="email" id="email" name="email" required autocomplete="email">
            </div>
            <div class="gf-auth__field">
                <label class="gf-auth__label" for="password"><?= e(t('auth.password', $lang)) ?></label>
                <input class="gf-auth__input" type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
            </div>
            <div class="gf-auth__field">
                <label class="gf-auth__label" for="password_confirm"><?= e(t('auth.confirm_password', $lang)) ?></label>
                <input class="gf-auth__input" type="password" id="password_confirm" name="password_confirm" required minlength="8" autocomplete="new-password">
            </div>
            <button type="submit" class="gf-auth__submit"><?= e(t('auth.register_btn', $lang)) ?></button>
        </form>

        <div class="gf-auth__separator"><?= e(t('auth.or_separator', $lang)) ?></div>

        <a href="/auth/google" class="gf-auth__google">
            <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
            <?= e(t('auth.google_btn', $lang)) ?>
        </a>

        <div class="gf-auth__footer">
            <?= e(t('auth.has_account', $lang)) ?>
            <a href="/auth/login"><?= e(t('auth.login_btn', $lang)) ?></a>
        </div>
    </div>
</div>
