<?php
/* ============================================================================
   config.sample.php — ШАБЛОН конфига. Скопировать в config.php и заполнить.
   config.php в .gitignore (секреты не коммитятся).

   Локальная разработка: DB_DRIVER='sqlite' → файл data/app.db (ничего не надо).
   Боевой Beget:          DB_DRIVER='mysql'  + реквизиты базы MySQL.

   SMTP-пароль тут НЕ храним для боевого — он редактируется в админке
   (Настройки → Почта) и лежит в таблице settings. Здесь только дефолты.
   ============================================================================ */

return [
    // --- База данных ---
    'DB_DRIVER' => 'sqlite',            // 'sqlite' (локально) | 'mysql' (Beget)
    'DB_HOST'   => 'localhost',
    'DB_NAME'   => '',                  // имя базы MySQL (Beget)
    'DB_USER'   => '',
    'DB_PASS'   => '',
    'DB_SQLITE' => __DIR__ . '/../data/app.db', // путь к SQLite-файлу (локально)

    // --- Безопасность ---
    'SECRET'    => 'CHANGE_ME_random_long_string', // соль для сессий/токенов

    // --- Базовый URL сайта (для canonical/sitemap/писем) ---
    'SITE_URL'  => 'https://negabarit12.com',
];
