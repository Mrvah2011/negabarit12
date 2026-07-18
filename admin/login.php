<?php
$PUBLIC = true;
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

if (current_admin()) redirect('/admin/');

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (attempt_login(post('login'), post('password'))) redirect('/admin/');
    $err = 'Неверный логин или пароль';
    usleep(400000); // лёгкая задержка от перебора
}
?><!doctype html><html lang="ru"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>Вход — Негабарит 12 CMS</title>
<style>
body{margin:0;height:100vh;display:grid;place-items:center;background:#0E1116;color:#F2F4F7;font-family:Inter,system-ui,Arial,sans-serif}
form{background:#161B22;border:1px solid #2A313A;border-radius:14px;padding:32px;width:min(360px,90vw)}
h1{font-size:18px;margin:0 0 20px}label{display:block;margin:12px 0 5px;font-size:14px;font-weight:600}
input{width:100%;padding:11px 12px;background:#0E1116;border:1px solid #2A313A;border-radius:8px;color:#F2F4F7;font:inherit;box-sizing:border-box}
input:focus{outline:none;border-color:#DF7038}
button{width:100%;margin-top:20px;padding:12px;background:#DF7038;color:#1A1207;border:0;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer}
.err{color:#ff9a9a;font-size:14px;margin-top:12px}
</style></head><body>
<form method="post" autocomplete="off">
  <h1>Вход в админку</h1>
  <?= csrf_field() ?>
  <label>Логин</label><input name="login" autofocus>
  <label>Пароль</label><input type="password" name="password">
  <button type="submit">Войти</button>
  <?php if ($err) echo '<div class="err">' . e($err) . '</div>'; ?>
</form></body></html>
