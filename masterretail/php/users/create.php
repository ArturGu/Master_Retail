<?php
// ==== FILE: php/users/create.php ====
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();
checkRole('admin');

if (session_status() === PHP_SESSION_NONE) session_start();

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$email || !$password) {
    set_flash_message("❌ Усі поля обов'язкові", 'error');
    header('Location: ../../index.php#users');
    exit;
}

$check = mysqli_query($conn, "SELECT id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "' OR email = '" . mysqli_real_escape_string($conn, $email) . "'");
if (mysqli_num_rows($check) > 0) {
    set_flash_message("❌ Такий логін або email вже існує", 'error');
    header('Location: ../../index.php#users');
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'manager';

$stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $password_hash, $role);
mysqli_stmt_execute($stmt);

set_flash_message("✅ Менеджера створено успішно");
header('Location: ../../index.php#users');
exit;
