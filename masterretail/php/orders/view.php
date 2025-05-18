<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
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
$stmt = mysqli_prepare($conn, "
    SELECT o.*, c.name AS client_name
    FROM orders o
    JOIN clients c ON o.client_id = c.id
    WHERE o.id = ?
");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);

if (!$order) {
    set_flash_message('❌ Замовлення не знайдено', 'error');
    header('Location: ../../index.php#orders');
    exit;
}

// Отримуємо товари замовлення
$stmt = mysqli_prepare($conn, "
    SELECT oi.*, p.name AS product_name, p.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Перегляд замовлення</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

<h2>📦 Деталі замовлення №<?= $order['id'] ?></h2>

<div class="order-details">
    <p><strong>Клієнт:</strong> <?= htmlspecialchars($order['client_name']) ?></p>
    <p><strong>Статус:</strong> <?= htmlspecialchars($order['status']) ?></p>
    <p><strong>Створено:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
</div>

<h3>🛒 Товари:</h3>
<table class="clients-table">
    <thead>
        <tr>
            <th>Назва товару</th>
            <th>Кількість</th>
            <th>Ціна за одиницю (₴)</th>
            <th>Сума (₴)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        while ($item = mysqli_fetch_assoc($items)) :
            $sum = $item['price'] * $item['quantity'];
            $total += $sum;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['product_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price'], 2, '.', ' ') ?></td>
            <td><?= number_format($sum, 2, '.', ' ') ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h3>💰 Загальна сума: <?= number_format($total, 2, '.', ' ') ?> ₴</h3>

<p style="margin-top: 20px;">
    <a href="../../index.php#orders">← Назад до замовлень</a>
</p>

</body>
</html>
