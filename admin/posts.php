<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

// удаление
if (($_GET['del'] ?? '') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    db()->prepare("DELETE FROM posts WHERE id=?")->execute([(int)$_GET['del']]);
    flash('ok', 'Удалено'); redirect('/admin/posts.php');
}

$rows = db()->query("SELECT p.*, c.name AS cat FROM posts p LEFT JOIN categories c ON c.id=p.category_id ORDER BY COALESCE(p.published_at,p.created_at) DESC")->fetchAll();

admin_header('Статьи и новости');
?>
<div class="top"><h2>Статьи и новости</h2><a class="btn btn-p" href="/admin/post_edit.php">+ Создать</a></div>
<?php if (!$rows): ?>
  <div class="card muted">Пока пусто. <a href="/admin/post_edit.php">Создайте первую статью</a>.</div>
<?php else: ?>
<table>
  <tr><th></th><th>Заголовок</th><th>Тип</th><th>Рубрика</th><th>Статус</th><th>Дата</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?php if ($r['cover']) echo '<img class="thumb" src="' . e($r['cover']) . '" alt="">'; ?></td>
    <td><a href="/admin/post_edit.php?id=<?= $r['id'] ?>"><?= e($r['title'] ?: '(без названия)') ?></a></td>
    <td><?= $r['type'] === 'news' ? 'Новость' : 'Статья' ?></td>
    <td class="muted"><?= e($r['cat'] ?? '—') ?></td>
    <td><span class="tag <?= $r['status']==='published'?'pub':'draft' ?>"><?= $r['status']==='published'?'Опубликовано':'Черновик' ?></span></td>
    <td class="muted"><?= e(substr((string)($r['published_at'] ?: $r['created_at']),0,10)) ?></td>
    <td>
      <form method="post" action="?del=<?= $r['id'] ?>" onsubmit="return confirm('Удалить статью?')" style="display:inline">
        <?= csrf_field() ?><button class="btn btn-d" type="submit">Удалить</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; admin_footer();
