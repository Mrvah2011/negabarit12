<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (post('add')) {
        $name = post('name');
        if ($name !== '') {
            db()->prepare("INSERT INTO categories (name,slug,sort) VALUES (?,?,?)")
               ->execute([$name, slugify($name), (int)post('sort')]);
            flash('ok', 'Рубрика добавлена');
        }
    } elseif ($_GET['del'] ?? '') {
        db()->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_GET['del']]);
        flash('ok', 'Удалено');
    }
    redirect('/admin/categories.php');
}
$rows = db()->query("SELECT * FROM categories ORDER BY sort,name")->fetchAll();
admin_header('Рубрики');
?>
<div class="top"><h2>Рубрики блога</h2></div>
<div class="card">
  <form method="post" class="row c2">
    <?= csrf_field() ?><input type="hidden" name="add" value="1">
    <div><label>Название рубрики</label><input type="text" name="name" placeholder="напр. Кейсы"></div>
    <div><label>Порядок</label><input type="number" name="sort" value="0"><div style="margin-top:10px"><button class="btn btn-p">Добавить</button></div></div>
  </form>
</div>
<?php if ($rows): ?>
<table><tr><th>Название</th><th>Slug</th><th>Порядок</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['name']) ?></td><td class="muted"><?= e($r['slug']) ?></td><td><?= (int)$r['sort'] ?></td>
<td><form method="post" action="?del=<?= $r['id'] ?>" onsubmit="return confirm('Удалить рубрику?')" style="display:inline"><?= csrf_field() ?><button class="btn btn-d">Удалить</button></form></td></tr>
<?php endforeach; ?></table>
<?php endif; admin_footer();
