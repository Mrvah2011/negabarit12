<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('act');
    // сотрудники
    if ($act === 'member_save') {
        $id=(int)post('id'); $name=post('name'); $role=post('role'); $photo=post('photo'); $sort=(int)post('sort');
        if ($name==='') flash('err','Укажите имя');
        elseif ($id) db()->prepare("UPDATE team_members SET name=?,role=?,photo=?,sort=? WHERE id=?")->execute([$name,$role,$photo,$sort,$id]);
        else db()->prepare("INSERT INTO team_members (name,role,photo,sort,status) VALUES (?,?,?,?, 'published')")->execute([$name,$role,$photo,$sort]);
        flash('ok','Сохранено');
    } elseif ($act === 'member_del') {
        db()->prepare("DELETE FROM team_members WHERE id=?")->execute([(int)post('id')]); flash('ok','Удалено');
    } elseif ($act === 'gal_add') {
        $photo=post('photo'); $cap=post('caption'); $sort=(int)post('sort');
        if ($photo) { db()->prepare("INSERT INTO team_gallery (photo,caption,sort) VALUES (?,?,?)")->execute([$photo,$cap,$sort]); flash('ok','Фото добавлено'); }
        else flash('err','Загрузите фото');
    } elseif ($act === 'gal_del') {
        db()->prepare("DELETE FROM team_gallery WHERE id=?")->execute([(int)post('id')]); flash('ok','Удалено');
    }
    redirect('/admin/team.php');
}

$editM = null;
if ($mid=(int)($_GET['edit']??0)) { $st=db()->prepare("SELECT * FROM team_members WHERE id=?"); $st->execute([$mid]); $editM=$st->fetch(); }
$members = db()->query("SELECT * FROM team_members ORDER BY sort,id")->fetchAll();
$gallery = db()->query("SELECT * FROM team_gallery ORDER BY sort,id")->fetchAll();
admin_header('Команда и галерея'); $tok=csrf_token();
?>
<div class="top"><h2>Команда и галерея (страница «Команда»)</h2></div>

<div class="card">
  <strong>Кто ведёт вашу перевозку — <?= $editM?'редактировать':'добавить' ?></strong>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="act" value="member_save"><input type="hidden" name="id" value="<?= (int)($editM['id']??0) ?>">
    <input type="hidden" name="photo" id="mphoto" value="<?= e($editM['photo']??'') ?>">
    <div class="row c2">
      <div><label>Имя</label><input type="text" name="name" value="<?= e($editM['name']??'') ?>" placeholder="Иван Иванов"></div>
      <div><label>Должность</label><input type="text" name="role" value="<?= e($editM['role']??'') ?>" placeholder="Руководитель отдела логистики"></div>
    </div>
    <label>Порядок</label><input type="number" name="sort" value="<?= (int)($editM['sort']??0) ?>" style="max-width:120px">
    <div style="margin:12px 0;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <img id="mphotoPrev" class="thumb" style="width:80px;height:80px;border-radius:50%<?= ($editM['photo']??'')?'':';display:none' ?>" src="<?= e($editM['photo']??'') ?>">
      <input type="file" id="mphotoFile" accept="image/*"><span class="hint">Фото сотрудника</span>
    </div>
    <button class="btn btn-p"><?= $editM?'Сохранить':'Добавить' ?></button><?php if($editM): ?> <a class="btn" href="/admin/team.php">Отмена</a><?php endif; ?>
  </form>
</div>
<?php if($members): ?>
<table><tr><th></th><th>Имя</th><th>Должность</th><th>Порядок</th><th></th></tr>
<?php foreach($members as $m): ?>
<tr><td><?php if($m['photo']) echo '<img class="thumb" style="border-radius:50%" src="'.e($m['photo']).'">'; ?></td>
<td><a href="?edit=<?= $m['id'] ?>"><?= e($m['name']) ?></a></td><td class="muted"><?= e($m['role']) ?></td><td><?= (int)$m['sort'] ?></td>
<td><form method="post" onsubmit="return confirm('Удалить сотрудника?')" style="display:inline"><?= csrf_field() ?><input type="hidden" name="act" value="member_del"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn btn-d">Удалить</button></form></td></tr>
<?php endforeach; ?></table>
<?php endif; ?>

<div class="card" style="margin-top:24px">
  <strong>Как мы работаем и отдыхаем — добавить фото</strong>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="act" value="gal_add"><input type="hidden" name="photo" id="gphoto" value="">
    <div class="row c2">
      <div><label>Подпись (необязательно)</label><input type="text" name="caption" placeholder="Погрузка на объекте"></div>
      <div><label>Порядок</label><input type="number" name="sort" value="0"></div>
    </div>
    <div style="margin:12px 0;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <img id="gphotoPrev" class="thumb" style="width:120px;height:80px;display:none">
      <input type="file" id="gphotoFile" accept="image/*"><span class="hint">Фото процесса/отдыха</span>
    </div>
    <button class="btn btn-p">Добавить фото</button>
  </form>
</div>
<?php if($gallery): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-top:12px">
<?php foreach($gallery as $g): ?>
  <div class="card" style="padding:8px"><img src="<?= e($g['photo']) ?>" style="width:100%;height:110px;object-fit:cover;border-radius:6px"><?php if($g['caption']) echo '<div class="hint">'.e($g['caption']).'</div>'; ?>
    <form method="post" onsubmit="return confirm('Удалить фото?')" style="margin-top:6px"><?= csrf_field() ?><input type="hidden" name="act" value="gal_del"><input type="hidden" name="id" value="<?= $g['id'] ?>"><button class="btn btn-d" style="width:100%;padding:5px">Удалить</button></form>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<script>
const CSRF=<?= json_encode($tok) ?>;
function up(fileEl, hiddenId, prevId){ fileEl.addEventListener('change', async e=>{ const f=e.target.files[0]; if(!f)return; const fd=new FormData(); fd.append('upload',f); const r=await fetch('/admin/upload.php',{method:'POST',headers:{'X-CSRF':CSRF},body:fd}); const j=await r.json(); if(j.url){document.getElementById(hiddenId).value=j.url; const im=document.getElementById(prevId); im.src=j.url; im.style.display='';} else alert('Ошибка загрузки'); }); }
up(document.getElementById('mphotoFile'),'mphoto','mphotoPrev');
up(document.getElementById('gphotoFile'),'gphoto','gphotoPrev');
</script>
<?php admin_footer();
