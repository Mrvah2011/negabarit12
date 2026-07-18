<?php
/* Бутстрап админки: подключение ядра, сессия, схема, требование входа.
   Страница login.php подключает этот файл с $PUBLIC=true (вход не требуется). */
require_once __DIR__ . '/../includes/functions.php';
install_schema();
session_boot();
if (empty($PUBLIC)) require_login();
