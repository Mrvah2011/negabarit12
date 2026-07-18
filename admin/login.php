<?php
$PUBLIC = true;
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/mailer.php';

if (current_admin()) redirect('/admin/');

$err = '';
$stage = otp_data('login') ? 'code' : 'password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (($_POST['stage'] ?? '') === 'code' && otp_data('login')) {
        // шаг 2 — проверка кода
        $d = otp_data('login');
        if (check_code('login', post('code'))) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => $d['aid'], 'login' => $d['alogin']];
            redirect('/admin/');
        }
        $err = 'Неверный или просроченный код';
        $stage = 'code';
    } else {
        // шаг 1 — логин + пароль
        $st = db()->prepare("SELECT * FROM admins WHERE login=?");
        $st->execute([post('login')]);
        $a = $st->fetch();
        if ($a && password_verify(post('password'), $a['pass_hash'])) {
            $to = setting('smtp_to', setting('email'));
            if (setting('smtp_pass') !== '') {
                // 2FA: шлём код на почту
                $code = issue_code('login', ['aid' => $a['id'], 'alogin' => $a['login']]);
                if (send_code_email($to, $code, 'вход в админку')) {
                    $stage = 'code';
                } else {
                    clear_code('login');
                    $err = 'Не удалось отправить код на почту. Проверьте настройки SMTP.';
                }
            } else {
                // почта не настроена — входим без кода (чтобы не заблокировать доступ)
                session_regenerate_id(true);
                $_SESSION['admin'] = ['id' => $a['id'], 'login' => $a['login']];
                redirect('/admin/');
            }
        } else {
            $err = 'Неверный логин или пароль';
            usleep(400000);
        }
    }
}
$mask = preg_replace('~(.).*(@.*)~', '$1***$2', setting('smtp_to', setting('email')));
?><!doctype html><html lang="ru"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>Вход — Негабарит 12 CMS</title>
<style>
body{margin:0;height:100vh;display:grid;place-items:center;background:#0E1116;color:#F2F4F7;font-family:Inter,system-ui,Arial,sans-serif}
form{background:#161B22;border:1px solid #2A313A;border-radius:14px;padding:32px;width:min(360px,90vw)}
h1{font-size:18px;margin:0 0 6px}p.sub{color:#98A2B3;font-size:13px;margin:0 0 16px}
label{display:block;margin:12px 0 5px;font-size:14px;font-weight:600}
input{width:100%;padding:11px 12px;background:#0E1116;border:1px solid #2A313A;border-radius:8px;color:#F2F4F7;font:inherit;box-sizing:border-box}
input:focus{outline:none;border-color:#DF7038}
button{width:100%;margin-top:20px;padding:12px;background:#DF7038;color:#1A1207;border:0;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer}
.err{color:#ff9a9a;font-size:14px;margin-top:12px}.code-inp{letter-spacing:8px;text-align:center;font-size:22px}
a{color:#F08544;font-size:13px}
</style></head><body>
<?php if ($stage === 'code'): ?>
<form method="post" autocomplete="off">
  <h1>Введите код из письма</h1>
  <p class="sub">Код отправлен на <?= e($mask) ?>. Действует 10 минут.</p>
  <?= csrf_field() ?><input type="hidden" name="stage" value="code">
  <label>Код подтверждения</label><input class="code-inp" name="code" inputmode="numeric" maxlength="6" autofocus>
  <button type="submit">Войти</button>
  <?php if ($err) echo '<div class="err">' . e($err) . '</div>'; ?>
  <p style="margin-top:14px"><a href="/admin/login.php">← Начать заново</a></p>
</form>
<?php else: ?>
<form method="post" autocomplete="off">
  <h1>Вход в админку</h1>
  <p class="sub">После пароля на почту придёт код подтверждения.</p>
  <?= csrf_field() ?>
  <label>Логин</label><input name="login" autofocus>
  <label>Пароль</label><input type="password" name="password">
  <button type="submit">Продолжить</button>
  <?php if ($err) echo '<div class="err">' . e($err) . '</div>'; ?>
</form>
<?php endif; ?>
</body></html>
