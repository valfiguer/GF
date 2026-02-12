<?php
class LangController {

    public function setLang(string $lang): void {
        $lang = in_array($lang, ['es', 'en']) ? $lang : 'es';
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';

        setcookie('gf_lang', $lang, [
            'expires'  => time() + 365 * 24 * 3600,
            'path'     => '/',
            'samesite' => 'Lax',
        ]);

        header('Location: ' . $referer);
        exit;
    }
}
