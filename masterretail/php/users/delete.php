<?php
// ==== FILE: php/users/delete.php ====
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();
checkRole('admin');

if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    set_flash_message('Невірний ID користувача', 'error');
    header('Location: ../../index.php#users');
    exit;
}

// Перевірка, чи користувач існує та є менеджером
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $id"));
if (!$user || $user['role'] !== 'manager') {
    set_flash_message('Можна видаляти лише менеджерів', 'error');
    header('Location: ../../index.php#users');
    exit;
}

// Видалення користувача
$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

set_flash_message('Менеджера успішно видалено');
header('Location: ../../index.php#users');
exit;
