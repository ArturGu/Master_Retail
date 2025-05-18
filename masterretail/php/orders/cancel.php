<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/log.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) {
    set_flash_message('❌ Невірний ID замовлення', 'error');
    header('Location: ../../index.php#orders');
    exit;
}

// Отримуємо замовлення
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);

if (!$order) {
    set_flash_message('❌ Замовлення не знайдено', 'error');
    header('Location: ../../index.php#orders');
    exit;
}

if ($order['status'] === 'скасовано') {
    set_flash_message('ℹ️ Замовлення вже скасоване', 'error');
    header('Location: ../../index.php#orders');
    exit;
}

// Повертаємо товари на склад
$stmt = mysqli_prepare($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

while ($item = mysqli_fetch_assoc($res)) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    mysqli_query($conn, "UPDATE products SET stock = stock + $quantity WHERE id = $product_id");
    log_event($conn, 'повернення', 'товар', $product_id, "Повернено $quantity шт. через скасування замовлення ID $order_id");
}

// Оновлюємо статус замовлення
$stmt = mysqli_prepare($conn, "UPDATE orders SET status = 'скасовано' WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);

log_event($conn, 'скасування', 'замовлення', $order_id, "Замовлення скасовано, товари повернені на склад");

set_flash_message('✅ Замовлення скасовано, товари повернені на склад');
header('Location: ../../index.php#orders');
exit;
