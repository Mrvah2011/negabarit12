<?php
require __DIR__ . '/includes/site.php';
$html = setting('doc_policy');
site_head([
    'title' => 'Политика конфиденциальности — ООО «Негабарит 12»',
    'desc'  => 'Политика конфиденциальности и обработки персональных данных ООО «Негабарит 12».',
    'canonical' => 'https://negabarit12.com/politika',
]);
?>
<section class="section article">
  <div class="container article__container">
    <h1 class="article__title" data-reveal>Политика конфиденциальности</h1>
    <div class="article__body" data-reveal>
      <?= $html ?: '<p class="muted">Документ готовится.</p>' ?>
    </div>
  </div>
</section>
<?php site_footer();
