<?php
/* Layout админки: шапка с навигацией + подвал. Свой минимальный CSS (не грузим
   маркетинговые стили). Тёмная панель в тон бренду. */

function admin_header(string $title = 'Админка'): void {
    $a = current_admin();
    ?><!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> — Негабарит 12</title>
<style>
:root{--bg:#0E1116;--bg2:#161B22;--bg3:#1C2330;--ink:#F2F4F7;--mut:#98A2B3;--line:#2A313A;--act:#DF7038;--acth:#F08544;--ok:#1FB36B;--red:#BB2934}
*{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,Arial,sans-serif;background:var(--bg);color:var(--ink);font-size:15px}
a{color:var(--acth);text-decoration:none}a:hover{text-decoration:underline}
.wrap{display:flex;min-height:100vh}
.side{width:230px;flex:none;background:var(--bg2);border-right:1px solid var(--line);padding:16px 0;position:sticky;top:0;height:100vh;overflow:auto}
.side h1{font-size:16px;margin:0 16px 16px;font-weight:800}
.side a{display:block;padding:10px 16px;color:var(--ink)}
.side a:hover{background:var(--bg3);text-decoration:none}
.side a.on{background:var(--bg3);border-left:3px solid var(--act);font-weight:600}
.side .sep{margin:12px 16px;border-top:1px solid var(--line)}
.main{flex:1;padding:24px;max-width:1000px}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.top h2{margin:0;font-size:22px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;border:1px solid var(--line);background:var(--bg3);color:var(--ink);cursor:pointer;font:inherit}
.btn:hover{text-decoration:none;border-color:var(--act)}
.btn-p{background:var(--act);border-color:var(--act);color:#1A1207;font-weight:700}.btn-p:hover{background:var(--acth)}
.btn-d{color:#ff9a9a;border-color:#5a2a2a}
table{width:100%;border-collapse:collapse;background:var(--bg2);border:1px solid var(--line);border-radius:10px;overflow:hidden}
th,td{padding:11px 14px;text-align:left;border-bottom:1px solid var(--line);vertical-align:middle}
th{background:var(--bg3);font-size:13px;color:var(--mut)}
tr:last-child td{border-bottom:0}
.tag{display:inline-block;padding:2px 8px;border-radius:99px;font-size:12px;border:1px solid var(--line)}
.tag.pub{color:var(--ok);border-color:#1f5a42}.tag.draft{color:var(--mut)}
label{display:block;margin:14px 0 5px;font-weight:600;font-size:14px}
input[type=text],input[type=email],input[type=password],input[type=number],input[type=datetime-local],select,textarea{
  width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--line);border-radius:8px;color:var(--ink);font:inherit}
input:focus,textarea:focus,select:focus{outline:none;border-color:var(--act)}
.row{display:grid;gap:16px}@media(min-width:700px){.row.c2{grid-template-columns:1fr 1fr}}
.card{background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:20px;margin-bottom:16px}
.hint{color:var(--mut);font-size:13px;margin-top:4px}
.flash{padding:10px 14px;border-radius:8px;margin-bottom:16px;border:1px solid}
.flash.ok{background:rgba(31,179,107,.1);border-color:#1f5a42;color:#7ee0b0}
.flash.err{background:rgba(187,41,52,.12);border-color:#5a2a2a;color:#ff9a9a}
.muted{color:var(--mut)}.thumb{width:64px;height:44px;object-fit:cover;border-radius:6px;border:1px solid var(--line)}
@media(max-width:800px){.wrap{flex-direction:column}.side{width:auto;height:auto;position:static;display:flex;flex-wrap:wrap}.side h1{width:100%}.side a{flex:1 0 auto}}
/* CKEditor — тёмная тема под нашу админку (иначе текст блёклый/невидимый) */
.ck.ck-editor__main>.ck-editor__editable{background:var(--bg)!important;color:var(--ink)!important;border-color:var(--line)!important;min-height:340px}
.ck.ck-editor__editable_inline{padding:14px 18px!important}
.ck-content,.ck-content p,.ck-content li,.ck-content h2,.ck-content h3,.ck-content strong{color:var(--ink)!important}
.ck-content a{color:var(--acth)!important}
.ck-content blockquote{border-left:3px solid var(--act);color:var(--mut)!important;background:var(--bg2)}
.ck.ck-editor__editable_inline.ck-focused{border-color:var(--act)!important;box-shadow:none!important}
.ck.ck-toolbar{background:var(--bg3)!important;border-color:var(--line)!important}
.ck.ck-button,.ck.ck-toolbar .ck-button .ck-icon{color:var(--ink)!important}
.ck.ck-button:hover{background:var(--line)!important}
.ck.ck-dropdown__panel,.ck.ck-list{background:var(--bg2)!important;border-color:var(--line)!important}
.ck.ck-list__item .ck-button{color:var(--ink)!important}
.ck.ck-list__item .ck-button:hover{background:var(--bg3)!important}
</style>
</head><body>
<div class="wrap">
<nav class="side">
  <h1>НЕГАБАРИТ 12 · CMS</h1>
  <?php admin_nav(); ?>
  <div class="sep"></div>
  <a href="/" target="_blank">↗ Открыть сайт</a>
  <a href="/admin/logout.php">Выйти (<?= e($a['login'] ?? '') ?>)</a>
</nav>
<main class="main">
<?php flash_show(); }

function admin_nav(): void {
    $cur = basename($_SERVER['SCRIPT_NAME']);
    $items = [
        'index.php'    => 'Обзор',
        'posts.php'    => 'Статьи и новости',
        'categories.php'=> 'Рубрики',
        'cases.php'    => 'Кейсы перевозок',
        'vacancies.php'=> 'Вакансии',
        'leads.php'    => 'Заявки',
        'media.php'    => 'Медиа',
        'docs.php'     => 'Документы (РКН)',
        'settings.php' => 'Настройки',
    ];
    foreach ($items as $file => $label) {
        $on = ($cur === $file) ? ' class="on"' : '';
        echo '<a href="/admin/' . $file . '"' . $on . '>' . e($label) . '</a>';
    }
}

function admin_footer(): void { echo '</main></div></body></html>'; }

/* Flash-сообщения через сессию */
function flash(string $type, string $msg): void { session_boot(); $_SESSION['flash'][] = [$type, $msg]; }
function flash_show(): void {
    session_boot();
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) echo '<div class="flash ' . e($t) . '">' . e($m) . '</div>';
    $_SESSION['flash'] = [];
}
