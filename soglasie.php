<?php
require __DIR__ . '/includes/site.php';
$html = setting('doc_consent');
site_head([
    'title' => 'Согласие на обработку персональных данных — ООО «Негабарит 12»',
    'desc'  => 'Согласие на обработку персональных данных ООО «Негабарит 12».',
    'canonical' => 'https://negabarit12.com/soglasie',
]);
?>
<section class="section article">
  <div class="container article__container">
    <h1 class="article__title" data-reveal>Согласие на обработку персональных данных</h1>
    <div class="article__body" data-reveal>
      <?= $html ?: '<p class="muted">Документ готовится.</p>' ?>
    </div>
  </div>
</section>
<?php site_footer();
