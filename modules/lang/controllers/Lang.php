<?php

// ─── Global translation function ─────────────────────────────────────────────
// Loaded via require_once from template layout files.
// Available in every view across public, users, and judges areas.

if (!function_exists('t')) {

    function _lang_detect(): string {
        static $detected = null;
        if ($detected !== null) return $detected;

        $allowed = ['en', 'ru', 'de', 'fr', 'sv', 'fi', 'es', 'pt'];

        // 1. Session (set by Lang::set_lang)
        if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], $allowed, true)) {
            $detected = $_SESSION['lang'];
            return $detected;
        }
        // 2. Cookie (persists across visits)
        if (!empty($_COOKIE['lang']) && in_array($_COOKIE['lang'], $allowed, true)) {
            $_SESSION['lang'] = $_COOKIE['lang'];
            $detected = $_COOKIE['lang'];
            return $detected;
        }
        // 3. Browser Accept-Language header
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browser = substr(strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])), 0, 2);
            if (in_array($browser, $allowed, true)) {
                $_SESSION['lang'] = $browser;
                $detected = $browser;
                return $detected;
            }
        }
        // 4. Default: English
        $_SESSION['lang'] = 'en';
        $detected = 'en';
        return $detected;
    }

    function t(string $key, array $vars = []): string {
        static $translations  = null;
        static $en_fallback   = null;

        if ($translations === null) {
            $lang      = _lang_detect();
            $base_path = APPPATH . 'modules/lang/assets/';

            $lang_file = $base_path . $lang . '.php';
            $translations = (file_exists($lang_file)) ? require $lang_file : [];

            if ($lang !== 'en') {
                $en_file     = $base_path . 'en.php';
                $en_fallback = (file_exists($en_file)) ? require $en_file : [];
            } else {
                $en_fallback = $translations;
            }
        }

        $text = $translations[$key] ?? $en_fallback[$key] ?? $key;

        // Variable substitution: {name} → value
        if (!empty($vars)) {
            foreach ($vars as $k => $v) {
                $text = str_replace('{' . $k . '}', (string)$v, $text);
            }
        }

        return $text;
    }

    function current_lang(): string {
        return _lang_detect();
    }

}

// ─── Lang controller ─────────────────────────────────────────────────────────

class Lang extends Trongate {

    private const ALLOWED = ['en', 'ru', 'de', 'fr', 'sv', 'fi', 'es', 'pt'];

    /**
     * Set language via GET: lang/set_lang?lang=de
     * Stores in session + 30-day cookie, then redirects back.
     */
    public function set_lang(): void {
        $lang = trim($_GET['lang'] ?? '');

        if (in_array($lang, self::ALLOWED, true)) {
            $_SESSION['lang'] = $lang;
            setcookie('lang', $lang, time() + 60 * 60 * 24 * 30, '/');
        }

        $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        redirect($back);
    }
}
