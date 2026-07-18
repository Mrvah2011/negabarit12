<?php require_once __DIR__ . '/includes/site.php'; ?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>Наша команда — ООО «Негабарит 12», перевозка негабарита, Йошкар-Ола</title>
  <meta name="description" content="Команда ООО «Негабарит 12»: логисты, водители, тендерный отдел. Как мы работаем и отдыхаем. Вакансии в компании по перевозке негабаритных грузов." />
  <meta name="theme-color" content="#0E1116" />
  <link rel="icon" href="assets/logo/logo-orange.png" />
  <link rel="canonical" href="https://negabarit12.vercel.app/team" />
  <meta name="robots" content="index, follow" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Наша команда — ООО «Негабарит 12»" />
  <meta property="og:description" content="Люди, которые везут ваш груз. Процессы, атмосфера, вакансии." />
  <meta property="og:image" content="assets/img/hero-poster.jpg" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="src/styles/tokens.css" />
  <link rel="stylesheet" href="src/styles/base.css" />
  <link rel="stylesheet" href="src/styles/sections.css" />
</head>
<body>

<!-- Шапка (на этой странице — статичная, всегда видна) -->
<header class="header is-visible header--static">
  <div class="container header__row">
    <a href="/" class="header__logo" aria-label="Негабарит 12 — на главную">
      <img src="assets/logo/logo-orange.png" alt="ООО «Негабарит 12»" height="36" />
      <span>НЕГАБАРИТ&nbsp;12</span>
    </a>
    <nav class="header__actions">
      <a class="header__nav-link" href="/blog">Новости и статьи</a>
      <a class="header__nav-link" href="/">← На главную</a>
      <a class="header__phone" href="tel:+78002501444">8&nbsp;800&nbsp;250&nbsp;14&nbsp;44</a>
      <a class="btn btn--primary header__cta" href="/#quiz">Рассчитать стоимость</a>
    </nav>
  </div>
</header>

<main class="team-page">

  <!-- Интро -->
  <section class="section team-hero">
    <div class="container" data-reveal>
      <p class="eyebrow">Наша команда</p>
      <h1 class="team-hero__h1">Люди, которые везут ваш груз</h1>
      <p class="lead section-intro">ООО «Негабарит 12» — одна из ведущих компаний в сфере негабаритных перевозок. Активно участвуем в крупных проектах на локальных и международных маршрутах и зарекомендовали себя как надёжный партнёр по логистике. За фиксированной ценой и сохранным грузом стоят конкретные люди: логисты на связи 24/7, опытные водители тягачей и тендерный отдел.</p>
    </div>
  </section>

  <!-- Команда (карточки-плейсхолдеры) -->
  <section class="section section--tight">
    <div class="container">
      <div class="section-head" data-reveal><h2 class="section-title">Кто ведёт вашу перевозку</h2></div>
      <?php $members = db()->query("SELECT * FROM team_members WHERE status='published' ORDER BY sort, id")->fetchAll(); ?>
      <?php if ($members): ?>
      <div class="grid md:cols-2 lg:cols-4 team-grid" data-reveal-stagger>
        <?php foreach ($members as $m): ?>
        <figure class="team-member">
          <?php if ($m['photo']): ?><div class="team-member__photo"><img loading="lazy" src="<?= e($m['photo']) ?>" alt="<?= e($m['name']) ?>"></div>
          <?php else: ?><div class="team-member__photo artefact__placeholder"><span>Фото</span></div><?php endif; ?>
          <figcaption><strong><?= e($m['name']) ?></strong><span class="muted"><?= e($m['role']) ?></span></figcaption>
        </figure>
        <?php endforeach; ?>
      </div>
      <?php else: ?><p class="muted">Раздел наполняется.</p><?php endif; ?>
    </div>
  </section>

  <div class="zebra-divider" aria-hidden="true"></div>

  <!-- Процессы и атмосфера -->
  <section class="section">
    <div class="container">
      <div class="section-head" data-reveal>
        <p class="eyebrow">Изнутри</p>
        <h2 class="section-title">Как мы работаем и отдыхаем</h2>
        <p class="lead section-intro">Реальные кадры с погрузок, рейсов и из жизни команды.</p>
      </div>
      <?php $gallery = db()->query("SELECT * FROM team_gallery ORDER BY sort, id")->fetchAll(); ?>
      <?php if ($gallery): ?>
      <div class="grid md:cols-3 team-gallery" data-reveal-stagger>
        <?php foreach ($gallery as $g): ?>
        <figure class="team-gallery__item"><img loading="lazy" src="<?= e($g['photo']) ?>" alt="<?= e($g['caption'] ?: 'Фото перевозки') ?>"><?php if ($g['caption']): ?><figcaption class="muted"><?= e($g['caption']) ?></figcaption><?php endif; ?></figure>
        <?php endforeach; ?>
      </div>
      <?php else: ?><p class="muted">Галерея наполняется.</p><?php endif; ?>
    </div>
  </section>

  <!-- Вакансии -->
  <section class="section vacancies">
    <div class="container">
      <div class="section-head" data-reveal>
        <p class="eyebrow">Карьера</p>
        <h2 class="section-title">Вакансии</h2>
        <p class="lead section-intro">Мы ищем амбициозных сотрудников, готовых работать на результат и развиваться в логистике. Предлагаем работу, где можно выйти на стабильный и высокий доход — благодаря вашему желанию и компетенциям и нашим возможностям.</p>
      </div>
      <div class="grid md:cols-2 lg:cols-3" data-reveal-stagger>
        <?php foreach (db()->query("SELECT * FROM vacancies WHERE status='published' ORDER BY sort, id") as $v): ?>
        <article class="card vacancy"><h3><?= e($v['title']) ?></h3><div class="muted"><?= $v['body'] ?></div></article>
        <?php endforeach; ?>
      </div>
      <div class="vacancies__cta card" data-reveal>
        <div>
          <h3>Готовы рассмотреть ваше резюме</h3>
          <p class="muted">Направьте резюме на почту или позвоните — обсудим.</p>
        </div>
        <div class="vacancies__contacts">
          <a class="btn btn--primary" href="mailto:<?= e(setting('hr_email')) ?>">Резюме на почту</a>
          <a class="btn btn--ghost" href="tel:<?= e(setting('hr_phone')) ?>"><?= e(setting('hr_phone')) ?></a>
        </div>
      </div>
    </div>
  </section>

  <!-- Финальный CTA -->
  <section class="section center">
    <div class="container" data-reveal>
      <h2 class="section-title" style="margin-inline:auto">Нужно перевезти негабарит?</h2>
      <p class="lead section-intro" style="margin-inline:auto">Рассчитаем честную стоимость со страхованием и КТГ — цена в договоре.</p>
      <p style="margin-top:var(--space-5)"><a class="btn btn--primary btn--lg" href="/#quiz">Рассчитать стоимость</a></p>
    </div>
  </section>

</main>

<footer class="footer">
  <div class="container footer__row">
    <span class="muted">© ООО «Негабарит 12», г. Йошкар-Ола · перевозка негабаритных грузов по РФ, СНГ, Китаю</span>
    <a href="/" class="muted">← На главную</a>
  </div>
</footer>

<!-- Вторичная страница: раскрываем контент сразу (без зависимостей, грузится мгновенно).
     Лёгкий one-time fade-in через классы reveal; контент никогда не остаётся скрытым. -->
<script>
(function () {
  function reveal() {
    document.querySelectorAll('[data-reveal], [data-reveal-stagger]').forEach(function (e, i) {
      e.style.transitionDelay = (i * 50) + 'ms';
      e.classList.add('is-in');
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', reveal);
  else reveal();
})();
</script>
</body>
</html>
