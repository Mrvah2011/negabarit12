<?php
/* submit.php — приём всех форм сайта. Заявка ВСЕГДА пишется в БД (leads),
   затем (если настроен SMTP) уходит письмом. Ответ — JSON для fetch().
   Согласия: ПДн + политика обязательны, рассылка добровольна. */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
header('Content-Type: application/json; charset=utf-8');
install_schema();

function out(bool $ok, string $err = ''): void { echo json_encode(['ok' => $ok, 'error' => $err], JSON_UNESCAPED_UNICODE); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Метод не поддерживается');

// honeypot — если заполнено, тихо «успех» (бот)
if (!empty($_POST['company_site'])) out(true);

$name  = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$cPdn    = !empty($_POST['consent_pdn']);
$cPolicy = !empty($_POST['consent_policy']);
$cNews   = !empty($_POST['consent_news']);

$digits = preg_replace('~\D~', '', $phone);
if ($name === '' || strlen($digits) < 10) out(false, 'Укажите имя и корректный телефон');
if (!$cPdn || !$cPolicy) out(false, 'Нужно согласие на обработку данных и с политикой');

// utm
$utm = [];
foreach (['utm_source','utm_medium','utm_campaign','utm_content','utm_term'] as $k) if (!empty($_POST[$k])) $utm[$k] = $_POST[$k];

$lead = [
    'form'     => preg_replace('~[^a-z_]~', '', (string)($_POST['form'] ?? 'lead_final')),
    'name'     => mb_substr($name, 0, 160),
    'phone'    => mb_substr($phone, 0, 64),
    'messenger'=> mb_substr((string)($_POST['messenger'] ?? ''), 0, 32),
    'comment'  => mb_substr((string)($_POST['comment'] ?? ''), 0, 1000),
    'cargo'    => mb_substr((string)($_POST['cargo_type'] ?? ''), 0, 64),
    'route'    => mb_substr(trim(($_POST['route_from'] ?? '') . (isset($_POST['route_to']) ? ' → ' . $_POST['route_to'] : '')), 0, 255),
    'consent_news' => $cNews ? 1 : 0,
    'page'     => mb_substr((string)($_POST['page'] ?? ($_SERVER['HTTP_REFERER'] ?? '')), 0, 255),
    'utm'      => $utm ? json_encode($utm, JSON_UNESCAPED_UNICODE) : '',
];

// 1) всегда сохраняем в БД
try {
    db()->prepare("INSERT INTO leads (form,name,phone,messenger,comment,cargo,route,consent_pdn,consent_policy,consent_news,page,utm,created_at)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
       ->execute([$lead['form'],$lead['name'],$lead['phone'],$lead['messenger'],$lead['comment'],$lead['cargo'],$lead['route'],
                  $cPdn?1:0,$cPolicy?1:0,$lead['consent_news'],$lead['page'],$lead['utm'],now()]);
} catch (\Throwable $e) {
    error_log('[lead-db] ' . $e->getMessage());
    // даже при сбое БД не теряем контакт — пробуем письмом
}

// 2) письмо (если настроен SMTP)
send_lead_email($lead);

out(true);
