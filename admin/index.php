<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

$cnt = fn($t) => (int) db()->query("SELECT COUNT(*) FROM $t")->fetchColumn();
$posts   = $cnt('posts');
$leads   = $cnt('leads');
$cases   = $cnt('cases');
$vac     = $cnt('vacancies');
$newLeads = (int) db()->query("SELECT COUNT(*) FROM leads")->fetchColumn();

admin_header('Обзор');
?>
<div class="top"><h2>Обзор</h2></div>
<div class="row c2">
  <div class="card"><div class="muted">Статьи и новости</div><div style="font-size:32px;font-weight:800"><?= $posts ?></div><a href="/admin/posts.php">Управлять →</a></div>
  <div class="card"><div class="muted">Заявки с сайта</div><div style="font-size:32px;font-weight:800;color:var(--act)"><?= $leads ?></div><a href="/admin/leads.php">Смотреть →</a></div>
  <div class="card"><div class="muted">Кейсы перевозок</div><div style="font-size:32px;font-weight:800"><?= $cases ?></div><a href="/admin/cases.php">Управлять →</a></div>
  <div class="card"><div class="muted">Вакансии</div><div style="font-size:32px;font-weight:800"><?= $vac ?></div><a href="/admin/vacancies.php">Управлять →</a></div>
</div>
<div class="card">
  <strong>Быстрый старт</strong>
  <p class="muted">Создайте новость или статью — она появится на главной (блок «Новости и статьи») и в разделе <a href="/blog" target="_blank">/blog</a>. Контакты, цифры, тексты согласий и SMTP-пароль — в разделе «Настройки».</p>
  <a class="btn btn-p" href="/admin/post_edit.php">+ Новая статья / новость</a>
</div>
<?php admin_footer();
