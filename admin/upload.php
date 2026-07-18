<?php
/* Загрузка изображений из редактора/обложек. Формат ответа — под CKEditor
   SimpleUploadAdapter: {url:"..."} или {error:{message:"..."}}.
   Защита: только вошедший админ + CSRF-заголовок + строгая валидация картинки. */
require __DIR__ . '/_boot.php';
header('Content-Type: application/json; charset=utf-8');

function fail(string $m){ echo json_encode(['error'=>['message'=>$m]]); exit; }

if (($_SERVER['HTTP_X_CSRF'] ?? '') !== csrf_token()) fail('Сессия истекла, обновите страницу');
if (empty($_FILES['upload'])) fail('Файл не передан');
$f = $_FILES['upload'];
if ($f['error'] !== UPLOAD_ERR_OK) fail('Ошибка загрузки');
if ($f['size'] > 12 * 1024 * 1024) fail('Файл больше 12 МБ');

$info = @getimagesize($f['tmp_name']);
if (!$info) fail('Это не изображение');
[$w, $h] = $info;
$type = $info[2];

// читаем в GD
switch ($type) {
    case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($f['tmp_name']); break;
    case IMAGETYPE_PNG:  $img = imagecreatefrompng($f['tmp_name']);  break;
    case IMAGETYPE_WEBP: $img = imagecreatefromwebp($f['tmp_name']); break;
    case IMAGETYPE_GIF:  $img = imagecreatefromgif($f['tmp_name']);  break;
    default: fail('Формат не поддерживается (только JPG/PNG/WEBP/GIF)');
}
if (!$img) fail('Не удалось прочитать изображение');

// уменьшаем до 1600px по ширине
$maxW = 1600;
if ($w > $maxW) {
    $nh = (int) round($h * $maxW / $w);
    $dst = imagecreatetruecolor($maxW, $nh);
    imagealphablending($dst, false); imagesavealpha($dst, true);
    imagecopyresampled($dst, $img, 0, 0, 0, 0, $maxW, $nh, $w, $h);
    imagedestroy($img); $img = $dst;
}

$dir = __DIR__ . '/../assets/uploads';
@mkdir($dir, 0775, true);
$name = date('Y/m/') . bin2hex(random_bytes(8)) . '.webp';
@mkdir(dirname("$dir/$name"), 0775, true);
if (!imagewebp($img, "$dir/$name", 82)) fail('Не удалось сохранить');
imagedestroy($img);

$url = '/assets/uploads/' . $name;
db()->prepare("INSERT INTO media (path,created_at) VALUES (?,?)")->execute([$url, now()]);
echo json_encode(['url' => $url]);
