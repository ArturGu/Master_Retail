<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/log.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    set_flash_message('❌ Невірний ID замовлення', 'error');
    header('Location: ../../index.php#orders');
    exit;
}

// Повернення товарів на склад
$stmt = mysqli_prepare($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($res)) {
    $product_id = $row['product_id'];
    $quantity = $row['quantity'];
    mysqli_query($conn, "UPDATE products SET stock = stock + $quantity WHERE id = $product_id");
    log_event($conn, 'повернення', 'товар', $product_id, "Повернено $quantity шт. після видалення замовлення ID $id");
}

// Видаляємо з order_items
$stmt = mysqli_prepare($conn, "DELETE FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

// Видаляємо саме замовлення
$stmt = mysqli_prepare($conn, "DELETE FROM orders WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

log_event($conn, 'видалення', 'замовлення', $id, "Замовлення видалено, товари повернуто на склад");

set_flash_message('✅ Замовлення видалено успішно');
header('Location: ../../index.php#orders');
exit;
