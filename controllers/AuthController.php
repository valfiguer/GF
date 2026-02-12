<?php
class AuthController {

    // ── Login page ──
    public function loginPage(): void {
        $user = Session::getCurrentUser();
        if ($user) { header('Location: /'); exit; }

        $lang  = getLang();
        $error = $_GET['error'] ?? null;
        View::render('auth/login', compact('lang', 'error'));
    }

    // ── Login submit ──
    public function loginSubmit(): void {
        $lang  = getLang();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass  = $_POST['password'] ?? '';

        $user = UserRepository::getByEmail($email);
        if (!$user || empty($user['password_hash'])) {
            header('Location: /auth/login?error=' . urlencode(t('auth.error_invalid', $lang)));
            exit;
        }

        if (!Auth::verifyPassword($pass, $user['password_hash'])) {
            header('Location: /auth/login?error=' . urlencode(t('auth.error_invalid', $lang)));
            exit;
        }

        Session::create((int)$user['id']);
        header('Location: /');
        exit;
    }

    // ── Register page ──
    public function registerPage(): void {
        $user = Session::getCurrentUser();
        if ($user) { header('Location: /'); exit; }

        $lang  = getLang();
        $error = $_GET['error'] ?? null;
        View::render('auth/register', compact('lang', 'error'));
    }

    // ── Register submit ──
    public function registerSubmit(): void {
        $lang           = getLang();
        $name           = trim($_POST['display_name'] ?? '');
        $email          = strtolower(trim($_POST['email'] ?? ''));
        $pass           = $_POST['password'] ?? '';
        $passConfirm    = $_POST['password_confirm'] ?? '';

        if (!$name || !$email || !$pass) {
            header('Location: /auth/register?error=' . urlencode(t('auth.error_fields', $lang)));
            exit;
        }
        if (mb_strlen($pass) < 8) {
            header('Location: /auth/register?error=' . urlencode(t('auth.error_password_short', $lang)));
            exit;
        }
        if ($pass !== $passConfirm) {
            header('Location: /auth/register?error=' . urlencode(t('auth.error_password_mismatch', $lang)));
            exit;
        }
        if (UserRepository::getByEmail($email)) {
            header('Location: /auth/register?error=' . urlencode(t('auth.error_email_exists', $lang)));
            exit;
        }

        $initials = Auth::makeInitials($name);
        $pwHash   = Auth::hashPassword($pass);
        $userId   = UserRepository::create(
            $email,
            e($name),
            e($initials),
            $pwHash,
            'local'
        );

        Session::create($userId);
        header('Location: /');
        exit;
    }

    // ── Logout ──
    public function logout(): void {
        Session::destroy();
        header('Location: /');
        exit;
    }

    // ── Google OAuth redirect ──
    public function googleLogin(): void {
        $clientId    = GOOGLE_CLIENT_ID;
        $redirectUri = GOOGLE_REDIRECT_URI;

        if (!$clientId || !$redirectUri) {
            header('Location: /auth/login?error=' . urlencode('Google OAuth not configured'));
            exit;
        }

        $url = 'https://accounts.google.com/o/oauth2/v2/auth'
             . '?client_id='    . urlencode($clientId)
             . '&redirect_uri=' . urlencode($redirectUri)
             . '&response_type=code'
             . '&scope='        . urlencode('openid email profile')
             . '&access_type=offline'
             . '&prompt=select_account';
        header('Location: ' . $url);
        exit;
    }

    // ── Google OAuth callback ──
    public function googleCallback(): void {
        $code  = $_GET['code'] ?? null;
        $error = $_GET['error'] ?? null;

        if ($error || !$code) {
            header('Location: /auth/login?error=' . urlencode('Google login cancelled'));
            exit;
        }

        try {
            // Exchange code for tokens
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS     => http_build_query([
                    'code'          => $code,
                    'client_id'     => GOOGLE_CLIENT_ID,
                    'client_secret' => GOOGLE_CLIENT_SECRET,
                    'redirect_uri'  => GOOGLE_REDIRECT_URI,
                    'grant_type'    => 'authorization_code',
                ]),
            ]);
            $tokenData = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                header('Location: /auth/login?error=' . urlencode('Google login failed'));
                exit;
            }

            // Get user info
            $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            ]);
            $userinfo = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $googleId = $userinfo['id']      ?? null;
            $email    = strtolower($userinfo['email'] ?? '');
            $name     = $userinfo['name']    ?? explode('@', $email)[0];
            $avatar   = $userinfo['picture'] ?? null;

            if (!$googleId || !$email) {
                header('Location: /auth/login?error=' . urlencode('Google login failed'));
                exit;
            }

            // Find or create user
            $user = UserRepository::getByGoogleId($googleId);
            if (!$user) {
                $user = UserRepository::getByEmail($email);
                if ($user) {
                    UserRepository::linkGoogle((int)$user['id'], $googleId, $avatar);
                } else {
                    $initials = Auth::makeInitials($name);
                    $userId   = UserRepository::create(
                        $email, e($name), e($initials),
                        null, 'google', $googleId, $avatar
                    );
                    $user = UserRepository::getById($userId);
                }
            }

            Session::create((int)$user['id']);
            header('Location: /');
            exit;
        } catch (\Exception $ex) {
            header('Location: /auth/login?error=' . urlencode('Google login failed'));
            exit;
        }
    }

    // ── Profile ──
    public function profile(): void {
        $user = Session::getCurrentUser();
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }
        $lang = getLang();
        View::render('auth/profile', compact('user', 'lang'));
    }
}
