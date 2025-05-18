<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/flash.php';

checkAuth();

if (!is_admin()) {
    set_flash_message("⛔ У вас недостатньо прав для видалення товарів", 'error');
    header("Location: ../../index.php#inventory");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    if ($product_id <= 0) {
        set_flash_message("❌ Невірний ідентифікатор товару", 'error');
        header("Location: ../../index.php#inventory");
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE products SET is_deleted = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        set_flash_message("✅ Товар успішно видалено");
    } else {
        set_flash_message("❌ Помилка при видаленні товару", 'error');
    }

    header("Location: ../../index.php#inventory");
    exit;
} else {
    set_flash_message("❌ Некоректний запит", 'error');
    header("Location: ../../index.php#inventory");
    exit;
}
?>
