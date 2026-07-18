<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

$docs = [
    'doc_policy'     => ['Политика конфиденциальности', '/politika'],
    'doc_consent'    => ['Согласие на обработку персональных данных', '/soglasie'],
    'doc_newsletter' => ['Согласие на получение рассылки', null],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($docs as $key => $_) set_setting($key, (string)($_POST[$key] ?? ''));
    flash('ok', 'Документы сохранены');
    redirect('/admin/docs.php');
}

admin_header('Документы (РКН)');
$tok = csrf_token();
?>
<div class="top"><h2>Юридические документы</h2></div>
<div class="card hint">Тексты для соответствия 152-ФЗ. Публикуются на страницах сайта, галочки согласий в формах ссылаются на них. Отредактируйте под вашу компанию (или передайте юристу).</div>
<form method="post">
  <?= csrf_field() ?>
  <?php foreach ($docs as $key => [$label, $url]): ?>
  <div class="card">
    <strong><?= e($label) ?></strong>
    <?php if ($url): ?> <a class="hint" href="<?= e($url) ?>" target="_blank">— страница <?= e($url) ?> ↗</a><?php endif; ?>
    <textarea name="<?= e($key) ?>" class="doc-editor" style="margin-top:8px"><?= e(setting($key)) ?></textarea>
  </div>
  <?php endforeach; ?>
  <button class="btn btn-p" type="submit">Сохранить документы</button>
</form>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
document.querySelectorAll('.doc-editor').forEach(el => {
  ClassicEditor.create(el, { simpleUpload: { uploadUrl: '/admin/upload.php', headers: { 'X-CSRF': <?= json_encode($tok) ?> } } }).catch(console.error);
});
</script>
<?php admin_footer();
