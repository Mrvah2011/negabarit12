<?php
require __DIR__ . '/includes/site.php';

$type = ($_GET['type'] ?? '') === 'news' ? 'news' : (($_GET['type'] ?? '') === 'article' ? 'article' : null);
$posts = latest_posts(60, $type);

site_head([
    'title' => 'Новости и статьи — ООО «Негабарит 12»',
    'desc'  => 'Новости компании и полезные статьи о перевозке негабаритных грузов: как заказать, сколько стоит, оформление КТГ, реальные кейсы.',
    'canonical' => 'https://negabarit12.com/blog',
]);
?>
<section class="section">
  <div class="container">
    <div class="section-head" data-reveal>
      <p class="eyebrow">Блог компании</p>
      <h1 class="section-title">Новости и статьи</h1>
      <p class="lead section-intro">Как заказать перевозку негабарита, из чего складывается цена, оформление разрешений и реальные кейсы.</p>
    </div>

    <div class="cases__filter" style="margin-bottom:24px">
      <a class="<?= $type===null?'is-active':'' ?>" href="/blog">Все</a>
      <a class="<?= $type==='news'?'is-active':'' ?>" href="/blog?type=news">Новости</a>
      <a class="<?= $type==='article'?'is-active':'' ?>" href="/blog?type=article">Статьи</a>
    </div>

    <?php if (!$posts): ?>
      <div class="card muted">Материалов пока нет — скоро появятся.</div>
    <?php else: ?>
    <div class="posts-grid">
      <?php foreach ($posts as $p): ?>
      <article class="post-card">
        <a class="post-card__img" href="<?= e(post_url($p)) ?>">
          <?php if ($p['cover']): ?><img loading="lazy" src="<?= e($p['cover']) ?>" alt="<?= e($p['title']) ?>"><?php else: ?><span class="post-card__noimg">НЕГАБАРИТ 12</span><?php endif; ?>
        </a>
        <div class="post-card__body">
          <div class="post-card__meta"><span><?= e(fmt_date($p['published_at'] ?: $p['created_at'])) ?></span><?php if ($p['cat_name']): ?><span class="post-card__cat"># <?= e($p['cat_name']) ?></span><?php endif; ?></div>
          <h2 class="post-card__title"><a href="<?= e(post_url($p)) ?>"><?= e($p['title']) ?></a></h2>
          <p class="post-card__excerpt"><?= e($p['excerpt']) ?></p>
          <a class="post-card__more" href="<?= e(post_url($p)) ?>">Подробнее →</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php site_footer();
