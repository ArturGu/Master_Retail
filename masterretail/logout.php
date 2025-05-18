<?php
require_once __DIR__ . '/php/helpers/flash.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Flash перед редіректом
session_start();
set_flash_message('Ви успішно вийшли з системи!', 'success');

header('Location: login.php');
exit;
