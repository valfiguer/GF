<?php
class LegalController {

    public function privacy(): void {
        $lang = getLang();
        View::render('legal/privacy', compact('lang'));
    }

    public function terms(): void {
        $lang = getLang();
        View::render('legal/terms', compact('lang'));
    }
}
