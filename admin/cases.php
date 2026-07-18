<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

$types = ['tank'=>'Ёмкости','equip'=>'Оборудование','spec'=>'Спецтехника','long'=>'Конструкции/длинномер'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if ($_GET['del'] ?? '') { db()->prepare("DELETE FROM cases WHERE id=?")->execute([(int)$_GET['del']]); flash('ok','Удалено'); redirect('/admin/cases.php'); }
    $id=(int)post('id'); $t=post('title'); $route=post('route'); $dims=post('dims');
    $cover=post('cover'); $type=array_key_exists(post('type'),$types)?post('type'):'tank'; $sort=(int)post('sort');
    if ($t==='') flash('err','Укажите что везли');
    elseif ($id) { db()->prepare("UPDATE cases SET title=?,route=?,dims=?,cover=?,type=?,sort=? WHERE id=?")->execute([$t,$route,$dims,$cover,$type,$sort,$id]); flash('ok','Сохранено'); }
    else { db()->prepare("INSERT INTO cases (title,route,dims,cover,type,sort,status) VALUES (?,?,?,?,?,?, 'published')")->execute([$t,$route,$dims,$cover,$type,$sort]); flash('ok','Кейс добавлен'); }
    redirect('/admin/cases.php');
}

$edit = null;
if ($eid=(int)($_GET['edit']??0)) { $st=db()->prepare("SELECT * FROM cases WHERE id=?"); $st->execute([$eid]); $edit=$st->fetch(); }
$rows = db()->query("SELECT * FROM cases ORDER BY sort,id DESC")->fetchAll();
admin_header('Кейсы');
$tok=csrf_token();
?>
<div class="top"><h2>Кейсы перевозок</h2></div>
<div class="card">
  <strong><?= $edit?'Редактировать кейс':'Добавить кейс' ?></strong>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
    <input type="hidden" name="cover" id="cover" value="<?= e($edit['cover']??'') ?>">
    <div class="row c2">
      <div><label>Что везли</label><input type="text" name="title" value="<?= e($edit['title']??'') ?>" placeholder="Резервуар / цистерна"></div>
      <div><label>Тип (фильтр)</label><select name="type"><?php foreach($types as $k=>$l) echo '<option value="'.$k.'"'.(($edit['type']??'')===$k?' selected':'').'>'.e($l).'</option>'; ?></select></div>
      <div><label>Маршрут</label><input type="text" name="route" value="<?= e($edit['route']??'') ?>" placeholder="Астрахань → Пермь"></div>
      <div><label>Габариты · вес</label><input type="text" name="dims" value="<?= e($edit['dims']??'') ?>" placeholder="6,3 × 6,2 × 4,95 м · 25 т"></div>
    </div>
    <label>Порядок</label><input type="number" name="sort" value="<?= (int)($edit['sort']??0) ?>" style="max-width:120px">
    <div style="margin:12px 0;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <img id="coverPrev" class="thumb" style="width:120px;height:80px<?= ($edit['cover']??'')?'':';display:none' ?>" src="<?= e($edit['cover']??'') ?>">
      <input type="file" id="coverFile" accept="image/*"><span class="hint">Фото кейса</span>
    </div>
    <button class="btn btn-p"><?= $edit?'Сохранить':'Добавить' ?></button>
    <?php if($edit): ?> <a class="btn" href="/admin/cases.php">Отмена</a><?php endif; ?>
  </form>
</div>
<?php if ($rows): ?>
<table><tr><th></th><th>Что везли</th><th>Маршрут</th><th>Габариты</th><th>Тип</th><th></th></tr>
<?php foreach($rows as $r): ?>
<tr><td><?php if($r['cover']) echo '<img class="thumb" src="'.e($r['cover']).'">'; ?></td>
<td><a href="?edit=<?= $r['id'] ?>"><?= e($r['title']) ?></a></td><td class="muted"><?= e($r['route']) ?></td>
<td class="muted"><?= e($r['dims']) ?></td><td class="muted"><?= e($types[$r['type']]??$r['type']) ?></td>
<td><form method="post" action="?del=<?= $r['id'] ?>" onsubmit="return confirm('Удалить кейс?')" style="display:inline"><?= csrf_field() ?><button class="btn btn-d">Удалить</button></form></td></tr>
<?php endforeach; ?></table>
<?php endif; ?>
<script>
const CSRF=<?= json_encode($tok) ?>;
document.getElementById('coverFile').addEventListener('change', async e=>{
  const f=e.target.files[0]; if(!f)return; const fd=new FormData(); fd.append('upload',f);
  const r=await fetch('/admin/upload.php',{method:'POST',headers:{'X-CSRF':CSRF},body:fd}); const j=await r.json();
  if(j.url){document.getElementById('cover').value=j.url; const im=document.getElementById('coverPrev'); im.src=j.url; im.style.display='';}
  else alert('Ошибка загрузки');
});
</script>
<?php admin_footer();
