<?php
/* ============================================================================
   site.php — общий бутстрап и layout ПУБЛИЧНОГО сайта (шапка/подвал в едином
   стиле, из наших токенов). Контакты берутся из настроек (админка).
   ============================================================================ */
require_once __DIR__ . '/functions.php';
install_schema();

/** Последние опубликованные посты для главной/блога. */
function latest_posts(int $limit = 7, string $type = null): array {
    $sql = "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
            FROM posts p LEFT JOIN categories c ON c.id=p.category_id
            WHERE p.status='published' AND p.published_at <= ?";
    $args = [now()];
    if ($type) { $sql .= " AND p.type=?"; $args[] = $type; }
    $sql .= " ORDER BY p.published_at DESC LIMIT " . (int)$limit;
    $st = db()->prepare($sql); $st->execute($args);
    return $st->fetchAll();
}
function post_by_slug(string $slug): ?array {
    $st = db()->prepare("SELECT p.*, c.name AS cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id
                         WHERE p.slug=? AND p.status='published'");
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}
function post_url(array $p): string { return '/blog/' . rawurlencode($p['slug']); }
function fmt_date(string $d): string {
    $m = ['','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    $t = strtotime($d); return $t ? date('j', $t) . ' ' . $m[(int)date('n', $t)] . ' ' . date('Y', $t) : '';
}

/* --- Layout публичных страниц (блог, статья, юр-страницы) --- */
function site_head(array $o = []): void {
    $title = $o['title'] ?? 'ООО «Негабарит 12»';
    $desc  = $o['desc'] ?? 'Перевозка негабаритных грузов по России, СНГ и Китаю.';
    $canon = $o['canonical'] ?? (setting('_site_url', 'https://negabarit12.com') . ($_SERVER['REQUEST_URI'] ?? '/'));
    $ogimg = $o['ogimage'] ?? '/assets/img/hero-poster.jpg';
    ?><!doctype html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta name="robots" content="index, follow">
<meta property="og:type" content="<?= e($o['ogtype'] ?? 'website') ?>">
<meta property="og:title" content="<?= e($title) ?>"><meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e($ogimg) ?>"><meta property="og:locale" content="ru_RU">
<meta name="theme-color" content="#0E1116"><link rel="icon" href="/assets/logo/logo-orange.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/src/styles/tokens.css"><link rel="stylesheet" href="/src/styles/base.css"><link rel="stylesheet" href="/src/styles/sections.css">
<?php if (!empty($o['jsonld'])) echo '<script type="application/ld+json">' . $o['jsonld'] . "</script>\n"; ?>
</head><body>
<header class="header is-visible header--static">
  <div class="container header__row">
    <a href="/" class="header__logo" aria-label="Негабарит 12 — на главную"><img src="/assets/logo/logo-orange.png" alt="ООО «Негабарит 12»" height="36"><span>НЕГАБАРИТ&nbsp;12</span></a>
    <nav class="header__actions">
      <a class="header__nav-link" href="/blog">Новости и статьи</a>
      <a class="header__nav-link" href="/team">Наша команда</a>
      <a class="header__phone" href="tel:<?= e(setting('phone_tel')) ?>"><?= e(setting('phone')) ?></a>
      <a class="btn btn--primary header__cta" href="/#quiz">Рассчитать стоимость</a>
    </nav>
  </div>
</header>
<main><?php
}
function site_footer(): void { ?>
</main>
<footer class="footer"><div class="container footer__row">
  <span class="muted">© ООО «Негабарит 12», г. Йошкар-Ола · перевозка негабаритных грузов по РФ, СНГ, Китаю</span>
  <a href="/" class="muted">← На главную</a>
</div></footer>
<!-- Раскрываем reveal-контент сразу (без зависимостей), контент никогда не скрыт -->
<script>(function(){function r(){document.querySelectorAll('[data-reveal],[data-reveal-stagger]').forEach(function(e,i){e.style.transitionDelay=(i*40)+'ms';e.classList.add('is-in');});}
document.readyState==='loading'?document.addEventListener('DOMContentLoaded',r):r();})();</script>
</body></html><?php
}
