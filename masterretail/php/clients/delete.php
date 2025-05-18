<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    set_flash_message('❌ Невірний ID клієнта', 'error');
    header('Location: ../../index.php#clients');
    exit;
}

// Видалення через prepared statement
$stmt = mysqli_prepare($conn, "DELETE FROM clients WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

set_flash_message('✅ Клієнта видалено успішно');
header('Location: ../../index.php#clients');
exit;
