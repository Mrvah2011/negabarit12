<?php
/* mailer.php — отправка письма-уведомления о заявке через SMTP (настройки из
   админки). Если SMTP-пароль пуст — не шлём (заявка всё равно уже в БД). */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

/** @return bool отправлено ли письмо */
function send_lead_email(array $lead): bool {
    if (setting('smtp_pass') === '') return false; // почта не настроена — тихо выходим

    $labels = ['lead_calc'=>'калькулятор','lead_callback'=>'заказ звонка','lead_magnet'=>'лид-магнит','lead_final'=>'финальная форма','vacancy'=>'отклик на вакансию'];
    $src = $labels[$lead['form']] ?? $lead['form'];

    $rows = [
        'Имя'        => $lead['name'],
        'Телефон'    => $lead['phone'],
        'Мессенджер' => $lead['messenger'] ?? '',
        'Груз'       => $lead['cargo'] ?? '',
        'Маршрут'    => $lead['route'] ?? '',
        'Комментарий'=> $lead['comment'] ?? '',
        'Форма'      => $src,
        'Страница'   => $lead['page'] ?? '',
        'Рассылка'   => !empty($lead['consent_news']) ? 'да' : 'нет',
    ];
    $html = '<h2>Новая заявка с сайта</h2><table cellpadding="6" style="border-collapse:collapse">';
    foreach ($rows as $k => $v) { if ($v === '') continue; $html .= '<tr><td style="color:#888">' . e($k) . '</td><td><b>' . e($v) . '</b></td></tr>'; }
    $html .= '</table>';

    try {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host       = setting('smtp_host', 'smtp.beget.com');
        $m->SMTPAuth   = true;
        $m->Username   = setting('smtp_login');
        $m->Password   = setting('smtp_pass');
        $m->SMTPSecure = setting('smtp_secure', 'ssl') === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $m->Port       = (int) setting('smtp_port', 465);
        $m->CharSet    = 'UTF-8';
        $m->setFrom(setting('smtp_login'), 'Сайт Негабарит 12');
        $m->addAddress(setting('smtp_to', setting('email')));
        if (!empty($lead['phone'])) $m->addReplyTo(setting('smtp_login'), $lead['name'] ?? '');
        $m->isHTML(true);
        $m->Subject = 'Заявка с сайта (' . $src . ') — Негабарит 12';
        $m->Body    = $html;
        $m->AltBody = strip_tags(str_replace(['<tr>','</td><td>'], ["\n",': '], $html));
        $m->send();
        return true;
    } catch (\Throwable $e) {
        error_log('[mailer] ' . $e->getMessage());
        return false;
    }
}
