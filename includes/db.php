<?php
/* ============================================================================
   db.php — подключение к БД (PDO) + инициализация схемы.
   Работает и на SQLite (локально), и на MySQL (Beget) — одна кодовая база.
   ============================================================================ */

function cfg(string $key = null) {
    static $c = null;
    if ($c === null) {
        $path = __DIR__ . '/config.php';
        $c = is_file($path) ? require $path : require __DIR__ . '/config.sample.php';
    }
    return $key === null ? $c : ($c[$key] ?? null);
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $driver = cfg('DB_DRIVER');
    if ($driver === 'mysql') {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', cfg('DB_HOST'), cfg('DB_NAME'));
        $pdo = new PDO($dsn, cfg('DB_USER'), cfg('DB_PASS'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } else {
        // SQLite (локальная разработка)
        $file = cfg('DB_SQLITE');
        @mkdir(dirname($file), 0775, true);
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

function db_is_mysql(): bool { return cfg('DB_DRIVER') === 'mysql'; }

/** Автоинкрементный первичный ключ — синтаксис под драйвер. */
function pk(): string {
    return db_is_mysql()
        ? 'INT AUTO_INCREMENT PRIMARY KEY'
        : 'INTEGER PRIMARY KEY AUTOINCREMENT';
}

/** Создаёт таблицы, если их нет. Идемпотентно — можно звать при каждом запросе. */
function install_schema(): void {
    $pdo = db();
    $eng = db_is_mysql() ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
    $pk  = pk();
    $q = [];

    $q[] = "CREATE TABLE IF NOT EXISTS admins (
        id $pk, login VARCHAR(64) UNIQUE, pass_hash VARCHAR(255),
        created_at VARCHAR(32)) $eng";

    $q[] = "CREATE TABLE IF NOT EXISTS categories (
        id $pk, name VARCHAR(128), slug VARCHAR(160) UNIQUE, sort INT DEFAULT 0) $eng";

    $q[] = "CREATE TABLE IF NOT EXISTS posts (
        id $pk, type VARCHAR(16) DEFAULT 'article', title VARCHAR(255), slug VARCHAR(220) UNIQUE,
        excerpt TEXT, body " . (db_is_mysql() ? 'LONGTEXT' : 'TEXT') . ", cover VARCHAR(255),
        category_id INT, seo_title VARCHAR(255), seo_desc VARCHAR(500),
        status VARCHAR(16) DEFAULT 'draft', published_at VARCHAR(32),
        created_at VARCHAR(32), updated_at VARCHAR(32)) $eng";

    $q[] = "CREATE TABLE IF NOT EXISTS vacancies (
        id $pk, title VARCHAR(255), body " . (db_is_mysql() ? 'LONGTEXT' : 'TEXT') . ",
        status VARCHAR(16) DEFAULT 'published', sort INT DEFAULT 0, updated_at VARCHAR(32)) $eng";

    $q[] = "CREATE TABLE IF NOT EXISTS cases (
        id $pk, title VARCHAR(255), route VARCHAR(255), dims VARCHAR(160),
        cover VARCHAR(255), type VARCHAR(32) DEFAULT 'tank', sort INT DEFAULT 0,
        status VARCHAR(16) DEFAULT 'published') $eng";

    $q[] = "CREATE TABLE IF NOT EXISTS leads (
        id $pk, form VARCHAR(32), name VARCHAR(160), phone VARCHAR(64), messenger VARCHAR(32),
        comment TEXT, cargo VARCHAR(64), route VARCHAR(255),
        consent_pdn INT DEFAULT 0, consent_policy INT DEFAULT 0, consent_news INT DEFAULT 0,
        page VARCHAR(255), utm VARCHAR(500), created_at VARCHAR(32)) $eng";

    $q[] = "CREATE TABLE IF NOT EXISTS settings (
        k VARCHAR(64) PRIMARY KEY, v " . (db_is_mysql() ? 'LONGTEXT' : 'TEXT') . ") $eng";

    $q[] = "CREATE TABLE IF NOT EXISTS media (
        id $pk, path VARCHAR(255), created_at VARCHAR(32)) $eng";

    foreach ($q as $sql) $pdo->exec($sql);
}
