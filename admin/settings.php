<?php
require __DIR__ . '/_boot.php';
require_once __DIR__ . '/../includes/admin_ui.php';

$fields = ['phone','phone_tel','email','telegram_url','address','hr_email','hr_phone',
           'smtp_host','smtp_port','smtp_secure','smtp_login','smtp_pass','smtp_to',
           'consent_pdn','consent_policy','consent_news'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    require_once __DIR__ . '/../includes/mailer.php';

    // --- Смена пароля через код на почту (отдельная форма) ---
    if (($_POST['pw_action'] ?? '') !== '') {
        $a = current_admin();
        $st = db()->prepare("SELECT * FROM admins WHERE id=?"); $st->execute([$a['id']]); $row = $st->fetch();
        if (post('code') !== '' && otp_data('pwchange')) {
            $d = otp_data('pwchange');
            if (check_code('pwchange', post('code'))) {
                db()->prepare("UPDATE admins SET pass_hash=? WHERE id=?")->execute([$d['new_hash'], $a['id']]);
                flash('ok', 'Пароль админки изменён');
            } else flash('err', 'Неверный или просроченный код');
        } else {
            if (!$row || !password_verify(post('cur_pass'), $row['pass_hash'])) flash('err', 'Текущий пароль неверный');
            elseif (mb_strlen(post('new_pass')) < 8) flash('err', 'Новый пароль слишком короткий (минимум 8 символов)');
            elseif (setting('smtp_pass') === '') flash('err', 'Сначала настройте почту (SMTP) — код смены пароля отправляется на почту');
            else {
                $code = issue_code('pwchange', ['new_hash' => password_hash(post('new_pass'), PASSWORD_DEFAULT)]);
                if (send_code_email(setting('smtp_to', setting('email')), $code, 'смена пароля админки')) flash('ok', 'Код отправлен на почту — введите его ниже и нажмите «Сменить пароль»');
                else { clear_code('pwchange'); flash('err', 'Не удалось отправить код на почту'); }
            }
        }
        redirect('/admin/settings.php');
    }

    // --- Обычное сохранение настроек ---
    foreach ($fields as $f) {
        if ($f === 'smtp_pass' && post('smtp_pass') === '') continue; // пустой SMTP-пароль не затираем
        set_setting($f, post($f));
    }
    flash('ok', 'Настройки сохранены');
    redirect('/admin/settings.php');
}

$v = fn($k) => e(setting($k));
admin_header('Настройки');
?>
<div class="top"><h2>Настройки сайта</h2></div>
<form method="post">
  <?= csrf_field() ?>
  <div class="card">
    <strong>Контакты</strong>
    <div class="row c2">
      <div><label>Телефон (как показывать)</label><input type="text" name="phone" value="<?= $v('phone') ?>"></div>
      <div><label>Телефон (для ссылки tel:)</label><input type="text" name="phone_tel" value="<?= $v('phone_tel') ?>"></div>
      <div><label>Email</label><input type="text" name="email" value="<?= $v('email') ?>"></div>
      <div><label>Telegram (ссылка)</label><input type="text" name="telegram_url" value="<?= $v('telegram_url') ?>"></div>
    </div>
    <label>Адрес</label><input type="text" name="address" value="<?= $v('address') ?>">
    <div class="row c2">
      <div><label>Email для резюме (вакансии)</label><input type="text" name="hr_email" value="<?= $v('hr_email') ?>"></div>
      <div><label>Телефон отдела кадров (tel:)</label><input type="text" name="hr_phone" value="<?= $v('hr_phone') ?>"></div>
    </div>
  </div>

  <div class="card">
    <strong>Почта для заявок (SMTP)</strong>
    <p class="hint">Заявки всегда сохраняются в разделе «Заявки». Если заполнить SMTP — плюсом уходят на почту. Пароль — от ящика (Beget → Почта).</p>
    <div class="row c2">
      <div><label>SMTP-хост</label><input type="text" name="smtp_host" value="<?= $v('smtp_host') ?>"></div>
      <div><label>Порт</label><input type="text" name="smtp_port" value="<?= $v('smtp_port') ?>"></div>
      <div><label>Шифрование</label><select name="smtp_secure"><option value="ssl"<?= setting('smtp_secure')==='ssl'?' selected':'' ?>>SSL (465)</option><option value="tls"<?= setting('smtp_secure')==='tls'?' selected':'' ?>>TLS (587)</option></select></div>
      <div><label>Логин (email ящика)</label><input type="text" name="smtp_login" value="<?= $v('smtp_login') ?>"></div>
      <div><label>Пароль ящика <span class="hint">— пусто = не менять</span></label><input type="password" name="smtp_pass" value="" placeholder="<?= setting('smtp_pass')?'•••••• (задан)':'не задан' ?>"></div>
      <div><label>Куда слать заявки</label><input type="text" name="smtp_to" value="<?= $v('smtp_to') ?>"></div>
    </div>
  </div>

  <div class="card">
    <strong>Тексты согласий (в формах)</strong>
    <label>Согласие на обработку ПДн (обязательное)</label><input type="text" name="consent_pdn" value="<?= $v('consent_pdn') ?>">
    <label>Согласие с политикой конфиденциальности (обязательное)</label><input type="text" name="consent_policy" value="<?= $v('consent_policy') ?>">
    <label>Согласие на рассылку (добровольное)</label><input type="text" name="consent_news" value="<?= $v('consent_news') ?>">
  </div>

  <button class="btn btn-p" type="submit">Сохранить настройки</button>
</form>

<form method="post" class="card" autocomplete="off" style="margin-top:16px">
  <?= csrf_field() ?><input type="hidden" name="pw_action" value="1">
  <strong>Смена пароля в админку</strong>
  <p class="hint">Введите текущий и новый пароль → на почту (<?= e(setting('smtp_to', setting('email'))) ?>) придёт код → введите код и нажмите «Сменить пароль».<?php if (setting('smtp_pass') === ''): ?> <span style="color:#ff9a9a">Сначала настройте почту (SMTP выше).</span><?php endif; ?></p>
  <div class="row c2">
    <div><label>Текущий пароль</label><input type="password" name="cur_pass"></div>
    <div><label>Новый пароль (мин. 8 символов)</label><input type="password" name="new_pass"></div>
  </div>
  <?php if (otp_data('pwchange')): ?>
  <label>Код из письма</label><input name="code" inputmode="numeric" maxlength="6" placeholder="6-значный код" autofocus>
  <?php endif; ?>
  <button class="btn btn-p" type="submit" style="margin-top:14px">Сменить пароль</button>
</form>
<?php admin_footer();
