<?php
require __DIR__ . '/includes/site.php';

$slug = $_GET['slug'] ?? '';
$p = $slug ? post_by_slug($slug) : null;

if (!$p) {
    http_response_code(404);
    site_head(['title' => 'Не найдено — Негабарит 12']);
    echo '<section class="section"><div class="container"><h1 class="section-title">Статья не найдена</h1><p class="lead">Возможно, она снята с публикации. <a class="link" href="/blog">Все статьи</a></p></div></section>';
    site_footer(); exit;
}

$date = $p['published_at'] ?: $p['created_at'];
$img  = $p['cover'] ?: '/assets/img/hero-poster.jpg';
$jsonld = json_encode([
    '@context' => 'https://schema.org', '@type' => 'Article',
    'headline' => $p['title'],
    'image'    => 'https://negabarit12.com' . $img,
    'datePublished' => date('c', strtotime($date)),
    'dateModified'  => date('c', strtotime($p['updated_at'] ?: $date)),
    'author'    => ['@type' => 'Organization', 'name' => 'ООО «Негабарит 12»'],
    'publisher' => ['@type' => 'Organization', 'name' => 'ООО «Негабарит 12»',
                    'logo' => ['@type' => 'ImageObject', 'url' => 'https://negabarit12.com/assets/logo/logo-orange.png']],
    'description' => $p['excerpt'],
    'mainEntityOfPage' => 'https://negabarit12.com' . post_url($p),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

site_head([
    'title' => ($p['seo_title'] ?: $p['title']) . ' — Негабарит 12',
    'desc'  => $p['seo_desc'] ?: $p['excerpt'],
    'canonical' => 'https://negabarit12.com' . post_url($p),
    'ogimage' => $img, 'ogtype' => 'article', 'jsonld' => $jsonld,
]);
?>
<article class="section article">
  <div class="container article__container">
    <div class="article__meta" data-reveal>
      <a href="/blog" class="link">← Новости и статьи</a>
      <span class="muted"><?= e(fmt_date($date)) ?><?php if ($p['cat_name']) echo ' · # ' . e($p['cat_name']); ?></span>
    </div>
    <h1 class="article__title" data-reveal><?= e($p['title']) ?></h1>
    <?php if ($p['cover']): ?><figure class="article__cover" data-reveal><img src="<?= e($p['cover']) ?>" alt="<?= e($p['title']) ?>"></figure><?php endif; ?>
    <div class="article__body" data-reveal><?= $p['body'] /* HTML из редактора (автор — админ) */ ?></div>

    <div class="article__cta card" data-reveal>
      <div><h3>Нужно перевезти негабарит?</h3><p class="muted">Рассчитаем честную стоимость со страхованием и КТГ — цена в договоре.</p></div>
      <a class="btn btn--primary" href="/#quiz">Рассчитать стоимость</a>
    </div>
  </div>
</article>
<?php site_footer();
