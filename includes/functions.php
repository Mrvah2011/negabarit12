<?php
/* ============================================================================
   functions.php — общие хелперы: экранирование, slug, настройки, авторизация,
   CSRF, работа с заявками. Подключается на всех страницах (фронт и админка).
   ============================================================================ */

require_once __DIR__ . '/db.php';

/* --- Вывод/экранирование (защита от XSS) --- */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function now(): string { return date('Y-m-d H:i:s'); }

/* --- Транслит RU→LAT для slug (ЧПУ-адреса статей) --- */
function slugify(string $s): string {
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z',
        'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
        'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
    ];
    $s = mb_strtolower(trim($s), 'UTF-8');
    $s = strtr($s, $map);
    $s = preg_replace('~[^a-z0-9]+~u', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'post-' . time();
}

/** Уникальный slug в таблице posts (добавляет -2, -3 при коллизии). */
function unique_slug(string $base, int $exceptId = 0): string {
    $slug = slugify($base); $i = 1; $try = $slug;
    while (true) {
        $st = db()->prepare("SELECT id FROM posts WHERE slug=? AND id<>?");
        $st->execute([$try, $exceptId]);
        if (!$st->fetch()) return $try;
        $try = $slug . '-' . (++$i);
    }
}

/* --- Анонс из HTML-тела (обрезка по символам, по умолчанию 170) --- */
function make_excerpt(string $html, int $limit = 170): string {
    $text = trim(preg_replace('~\s+~u', ' ', strip_tags($html)));
    if (mb_strlen($text, 'UTF-8') <= $limit) return $text;
    $cut = mb_substr($text, 0, $limit, 'UTF-8');
    $sp = mb_strrpos($cut, ' ', 0, 'UTF-8');
    if ($sp > $limit * 0.6) $cut = mb_substr($cut, 0, $sp, 'UTF-8');
    return $cut . '…';
}

/* --- Настройки (key-value) с дефолтами --- */
function settings_defaults(): array {
    return [
        'phone'        => '8 800 250 14 44',
        'phone_tel'    => '+78002501444',
        'email'        => 'info@negabarit12.com',
        'telegram_url' => 'https://t.me/negabarit_makarova',
        'address'      => 'г. Йошкар-Ола, ул. Дружбы, д. 100, оф. 213',
        'hr_email'     => 'k.salimgareev@negabarit12.com',
        'hr_phone'     => '+79194149999',
        // SMTP (пароль вписывается в админке; пусто — письма не шлём, заявки всё равно в БД)
        'smtp_host'    => 'smtp.beget.com',
        'smtp_port'    => '465',
        'smtp_secure'  => 'ssl',
        'smtp_login'   => 'info@negabarit12.com',
        'smtp_pass'    => '',
        'smtp_to'      => 'info@negabarit12.com',
        // Тексты согласий
        'consent_pdn'    => 'Согласен на обработку персональных данных',
        'consent_policy' => 'Согласен с политикой конфиденциальности',
        'consent_news'   => 'Согласен на получение рассылки',
    ];
}
function setting(string $k, $default = null) {
    static $cache = null;
    if ($cache === null) {
        $cache = settings_defaults();
        foreach (db()->query("SELECT k,v FROM settings") as $r) $cache[$r['k']] = $r['v'];
    }
    return $cache[$k] ?? $default;
}
function set_setting(string $k, string $v): void {
    if (db_is_mysql()) {
        $st = db()->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)");
    } else {
        $st = db()->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON CONFLICT(k) DO UPDATE SET v=excluded.v");
    }
    $st->execute([$k, $v]);
}

/* --- Авторизация в админку --- */
function session_boot(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}
function current_admin(): ?array { session_boot(); return $_SESSION['admin'] ?? null; }
function require_login(): void {
    if (!current_admin()) { header('Location: /admin/login.php'); exit; }
}
function attempt_login(string $login, string $pass): bool {
    $st = db()->prepare("SELECT * FROM admins WHERE login=?");
    $st->execute([$login]);
    $a = $st->fetch();
    if ($a && password_verify($pass, $a['pass_hash'])) {
        session_boot();
        session_regenerate_id(true);
        $_SESSION['admin'] = ['id' => $a['id'], 'login' => $a['login']];
        return true;
    }
    return false;
}
function logout(): void { session_boot(); $_SESSION = []; session_destroy(); }

/* --- CSRF --- */
function csrf_token(): string {
    session_boot();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    session_boot();
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '_')) { http_response_code(419); exit('CSRF'); }
}

/* --- Прочее --- */
function redirect(string $to): void { header('Location: ' . $to); exit; }
function post(string $k, $d = ''): string { return trim((string)($_POST[$k] ?? $d)); }
