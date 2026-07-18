<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

// экспорт CSV
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=leads.csv');
    echo "\xEF\xBB\xBF"; // BOM для Excel
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Дата','Форма','Имя','Телефон','Мессенджер','Комментарий','Груз','Маршрут','Рассылка','Страница']);
    foreach (db()->query("SELECT * FROM leads ORDER BY id DESC") as $r) {
        fputcsv($out, [$r['created_at'],$r['form'],$r['name'],$r['phone'],$r['messenger'],$r['comment'],$r['cargo'],$r['route'],$r['consent_news']?'да':'нет',$r['page']]);
    }
    exit;
}

$rows = db()->query("SELECT * FROM leads ORDER BY id DESC LIMIT 500")->fetchAll();
admin_header('Заявки');
?>
<div class="top"><h2>Заявки с сайта</h2><?php if($rows): ?><a class="btn" href="?export=csv">Выгрузить CSV</a><?php endif; ?></div>
<?php if (!$rows): ?>
  <div class="card muted">Заявок пока нет. Они появятся здесь автоматически при отправке форм — даже если почта ещё не настроена.</div>
<?php else: ?>
<table>
  <tr><th>Дата</th><th>Имя</th><th>Телефон</th><th>Форма</th><th>Груз / маршрут</th><th>Комментарий</th><th>Рассылка</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td class="muted" style="white-space:nowrap"><?= e(substr((string)$r['created_at'],0,16)) ?></td>
    <td><?= e($r['name']) ?></td>
    <td><a href="tel:<?= e(preg_replace('~\D~','',$r['phone'])) ?>"><?= e($r['phone']) ?></a><?php if($r['messenger']) echo '<div class="muted" style="font-size:12px">'.e($r['messenger']).'</div>'; ?></td>
    <td class="muted"><?= e($r['form']) ?></td>
    <td><?= e(trim(($r['cargo']?:'').' '.($r['route']?:''))) ?: '<span class="muted">—</span>' ?></td>
    <td class="muted"><?= e($r['comment']) ?></td>
    <td><?= $r['consent_news'] ? '<span class="tag pub">да</span>' : '<span class="muted">нет</span>' ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<p class="hint">Показаны последние 500. Полная выгрузка — кнопка «CSV».</p>
<?php endif; admin_footer();
