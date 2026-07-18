<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if ($_GET['del'] ?? '') { db()->prepare("DELETE FROM vacancies WHERE id=?")->execute([(int)$_GET['del']]); flash('ok','Удалено'); redirect('/admin/vacancies.php'); }
    $id=(int)post('id'); $t=post('title'); $body=(string)($_POST['body']??''); $sort=(int)post('sort');
    $status = post('status')==='published'?'published':'draft';
    if ($t==='') flash('err','Укажите название вакансии');
    elseif ($id){ db()->prepare("UPDATE vacancies SET title=?,body=?,sort=?,status=?,updated_at=? WHERE id=?")->execute([$t,$body,$sort,$status,now(),$id]); flash('ok','Сохранено'); }
    else { db()->prepare("INSERT INTO vacancies (title,body,sort,status,updated_at) VALUES (?,?,?,?,?)")->execute([$t,$body,$sort,$status,now()]); flash('ok','Вакансия добавлена'); }
    redirect('/admin/vacancies.php');
}
$edit=null; if($eid=(int)($_GET['edit']??0)){ $st=db()->prepare("SELECT * FROM vacancies WHERE id=?"); $st->execute([$eid]); $edit=$st->fetch(); }
$rows=db()->query("SELECT * FROM vacancies ORDER BY sort,id DESC")->fetchAll();
admin_header('Вакансии'); $tok=csrf_token();
?>
<div class="top"><h2>Вакансии</h2></div>
<div class="card">
  <strong><?= $edit?'Редактировать вакансию':'Добавить вакансию' ?></strong>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
    <label>Название</label><input type="text" name="title" value="<?= e($edit['title']??'') ?>" placeholder="Водитель категории СЕ">
    <label>Описание</label><textarea name="body" id="editor"><?= e($edit['body']??'') ?></textarea>
    <div class="row c2">
      <div><label>Статус</label><select name="status"><option value="published"<?= (($edit['status']??'published')==='published')?' selected':'' ?>>Опубликовано</option><option value="draft"<?= (($edit['status']??'')==='draft')?' selected':'' ?>>Черновик</option></select></div>
      <div><label>Порядок</label><input type="number" name="sort" value="<?= (int)($edit['sort']??0) ?>"></div>
    </div>
    <div style="margin-top:14px"><button class="btn btn-p"><?= $edit?'Сохранить':'Добавить' ?></button><?php if($edit): ?> <a class="btn" href="/admin/vacancies.php">Отмена</a><?php endif; ?></div>
  </form>
</div>
<?php if($rows): ?>
<table><tr><th>Название</th><th>Статус</th><th>Порядок</th><th></th></tr>
<?php foreach($rows as $r): ?>
<tr><td><a href="?edit=<?= $r['id'] ?>"><?= e($r['title']) ?></a></td>
<td><span class="tag <?= $r['status']==='published'?'pub':'draft' ?>"><?= $r['status']==='published'?'Опубл.':'Черновик' ?></span></td>
<td><?= (int)$r['sort'] ?></td>
<td><form method="post" action="?del=<?= $r['id'] ?>" onsubmit="return confirm('Удалить?')" style="display:inline"><?= csrf_field() ?><button class="btn btn-d">Удалить</button></form></td></tr>
<?php endforeach; ?></table>
<?php endif; ?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>ClassicEditor.create(document.querySelector('#editor'),{simpleUpload:{uploadUrl:'/admin/upload.php',headers:{'X-CSRF':<?= json_encode($tok) ?>}}}).catch(console.error);</script>
<?php admin_footer();
