<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_GET['del']??'')) {
    csrf_check();
    $st=db()->prepare("SELECT path FROM media WHERE id=?"); $st->execute([(int)$_GET['del']]);
    if($m=$st->fetch()){ $p=__DIR__.'/..'.$m['path']; if(is_file($p)) @unlink($p); db()->prepare("DELETE FROM media WHERE id=?")->execute([(int)$_GET['del']]); }
    flash('ok','Удалено'); redirect('/admin/media.php');
}
$rows=db()->query("SELECT * FROM media ORDER BY id DESC LIMIT 300")->fetchAll();
admin_header('Медиа');
?>
<div class="top"><h2>Медиа-библиотека</h2></div>
<div class="card hint">Все загруженные картинки. Клик по картинке — скопировать ссылку (для вставки в статью вручную). Загрузка — прямо в редакторе статьи.</div>
<?php if(!$rows): ?><div class="card muted">Пусто. Картинки появятся здесь по мере загрузки в статьях/кейсах.</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px">
<?php foreach($rows as $r): ?>
  <div class="card" style="padding:8px">
    <img src="<?= e($r['path']) ?>" style="width:100%;height:100px;object-fit:cover;border-radius:6px;cursor:pointer" onclick="navigator.clipboard.writeText(location.origin+'<?= e($r['path']) ?>');this.style.outline='2px solid #1FB36B'" title="Скопировать ссылку">
    <form method="post" action="?del=<?= $r['id'] ?>" onsubmit="return confirm('Удалить файл?')" style="margin-top:6px"><?= csrf_field() ?><button class="btn btn-d" style="width:100%;padding:5px">Удалить</button></form>
  </div>
<?php endforeach; ?>
</div>
<?php endif; admin_footer();
