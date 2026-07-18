<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

$id = (int)($_GET['id'] ?? 0);
$cats = db()->query("SELECT * FROM categories ORDER BY sort,name")->fetchAll();

// значения по умолчанию / из БД
$p = ['id'=>0,'type'=>'article','title'=>'','slug'=>'','excerpt'=>'','body'=>'','cover'=>'',
      'category_id'=>'','seo_title'=>'','seo_desc'=>'','status'=>'draft','published_at'=>''];
if ($id) {
    $st = db()->prepare("SELECT * FROM posts WHERE id=?"); $st->execute([$id]);
    $p = $st->fetch() ?: $p;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title = post('title');
    $body  = (string)($_POST['body'] ?? '');
    $type  = post('type') === 'news' ? 'news' : 'article';
    $slug  = post('slug') ?: $title;
    $slug  = unique_slug($slug, $id);
    $excerpt = post('excerpt') ?: make_excerpt($body, 170);
    $status  = post('status') === 'published' ? 'published' : 'draft';
    $catId   = (int)post('category_id') ?: null;
    $cover   = post('cover');
    $seoT    = post('seo_title');
    $seoD    = post('seo_desc');
    $pub     = post('published_at') ?: now();
    $pub     = str_replace('T', ' ', $pub);

    if ($title === '') { flash('err','Укажите заголовок'); }
    else {
        if ($id) {
            db()->prepare("UPDATE posts SET type=?,title=?,slug=?,excerpt=?,body=?,cover=?,category_id=?,seo_title=?,seo_desc=?,status=?,published_at=?,updated_at=? WHERE id=?")
               ->execute([$type,$title,$slug,$excerpt,$body,$cover,$catId,$seoT,$seoD,$status,$pub,now(),$id]);
        } else {
            db()->prepare("INSERT INTO posts (type,title,slug,excerpt,body,cover,category_id,seo_title,seo_desc,status,published_at,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$type,$title,$slug,$excerpt,$body,$cover,$catId,$seoT,$seoD,$status,$pub,now(),now()]);
            $id = (int) db()->lastInsertId();
        }
        flash('ok','Сохранено');
        redirect('/admin/post_edit.php?id=' . $id);
    }
}

admin_header($id ? 'Редактирование' : 'Новая статья');
$tok = csrf_token();
?>
<div class="top"><h2><?= $id ? 'Редактирование' : 'Новая статья / новость' ?></h2>
  <?php if ($id && $p['status']==='published'): ?><a class="btn" target="_blank" href="/blog/<?= e($p['slug']) ?>">Открыть на сайте ↗</a><?php endif; ?>
</div>
<form method="post">
  <?= csrf_field() ?>
  <div class="card">
    <div class="row c2">
      <div><label>Тип</label>
        <select name="type"><option value="article"<?= $p['type']==='article'?' selected':'' ?>>Статья</option><option value="news"<?= $p['type']==='news'?' selected':'' ?>>Новость</option></select></div>
      <div><label>Рубрика</label>
        <select name="category_id"><option value="">— без рубрики —</option>
          <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"<?= (int)$p['category_id']===$c['id']?' selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select></div>
    </div>
    <label>Заголовок</label><input type="text" name="title" value="<?= e($p['title']) ?>" required>
    <label>Адрес (slug) <span class="hint">— пусто = сгенерируется из заголовка</span></label>
    <input type="text" name="slug" value="<?= e($p['slug']) ?>" placeholder="напр. kak-zakazat-perevozku">
  </div>

  <div class="card">
    <label>Обложка</label>
    <input type="hidden" name="cover" id="cover" value="<?= e($p['cover']) ?>">
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <img id="coverPrev" class="thumb" style="width:160px;height:100px<?= $p['cover']?'':';display:none' ?>" src="<?= e($p['cover']) ?>" alt="">
      <input type="file" id="coverFile" accept="image/*">
      <button type="button" class="btn" onclick="document.getElementById('cover').value='';document.getElementById('coverPrev').style.display='none'">Убрать</button>
    </div>
    <div class="hint">Загружается автоматически, сжимается в webp.</div>
  </div>

  <div class="card">
    <label>Текст статьи</label>
    <textarea name="body" id="editor"><?= e($p['body']) ?></textarea>
    <div class="hint">Жирный, курсив, цитаты, списки, ссылки, картинки внутрь — панель редактора.</div>
    <label>Анонс (для главной и списка) <span class="hint">— пусто = возьмём начало текста (~170 символов)</span></label>
    <textarea name="excerpt" rows="2"><?= e($p['excerpt']) ?></textarea>
  </div>

  <div class="card">
    <strong>SEO / GEO</strong>
    <label>SEO-заголовок (title) <span class="hint">— пусто = заголовок статьи</span></label>
    <input type="text" name="seo_title" value="<?= e($p['seo_title']) ?>">
    <label>SEO-описание (description)</label>
    <textarea name="seo_desc" rows="2" maxlength="500"><?= e($p['seo_desc']) ?></textarea>
  </div>

  <div class="card">
    <div class="row c2">
      <div><label>Статус</label>
        <select name="status"><option value="draft"<?= $p['status']==='draft'?' selected':'' ?>>Черновик</option><option value="published"<?= $p['status']==='published'?' selected':'' ?>>Опубликовано</option></select></div>
      <div><label>Дата публикации</label>
        <input type="datetime-local" name="published_at" value="<?= e($p['published_at'] ? date('Y-m-d\TH:i', strtotime($p['published_at'])) : date('Y-m-d\TH:i')) ?>"></div>
    </div>
    <div style="margin-top:16px;display:flex;gap:10px">
      <button class="btn btn-p" type="submit">Сохранить</button>
      <a class="btn" href="/admin/posts.php">Назад</a>
    </div>
  </div>
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
const CSRF = <?= json_encode($tok) ?>;
ClassicEditor.create(document.querySelector('#editor'), {
  simpleUpload: { uploadUrl: '/admin/upload.php', headers: { 'X-CSRF': CSRF } }
}).catch(console.error);

// загрузка обложки
document.getElementById('coverFile').addEventListener('change', async e => {
  const f = e.target.files[0]; if (!f) return;
  const fd = new FormData(); fd.append('upload', f);
  const r = await fetch('/admin/upload.php', { method:'POST', headers:{'X-CSRF':CSRF}, body:fd });
  const j = await r.json();
  if (j.url) { document.getElementById('cover').value = j.url;
    const im = document.getElementById('coverPrev'); im.src = j.url; im.style.display=''; }
  else alert(j.error && j.error.message || 'Ошибка загрузки');
});
</script>
<?php admin_footer();
