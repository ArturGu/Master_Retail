<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/helpers/auth.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Загальна кількість замовлень
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"))['total'];

// Замовлення за статусами
$statusStats = mysqli_query($conn, "
    SELECT status, COUNT(*) AS count
    FROM orders
    GROUP BY status
");

// Топ-5 товарів за кількістю у замовленнях
$topProducts = mysqli_query($conn, "
    SELECT p.name, SUM(oi.quantity) AS total_qty
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_qty DESC
    LIMIT 5
");

// Останні 20 подій
$logResult = mysqli_query($conn, "SELECT * FROM log ORDER BY created_at DESC LIMIT 20");
?>

<h1>📊 Звітність</h1>

<div class="reports-wrapper">

    <div class="reports-section">
        <h3>🔢 Загальна статистика</h3>
        <ul>
            <li>Усього замовлень: <strong><?= $totalOrders ?></strong></li>
        </ul>
    </div>

    <div class="reports-section">
        <h3>📦 Замовлення за статусами</h3>
        <ul>
            <?php while ($row = mysqli_fetch_assoc($statusStats)) : ?>
                <li><?= htmlspecialchars($row['status']) ?>: <strong><?= $row['count'] ?></strong></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="reports-section">
        <h3>🔥 Топ-5 товарів</h3>
        <ol>
            <?php while ($product = mysqli_fetch_assoc($topProducts)) : ?>
                <li><?= htmlspecialchars($product['name']) ?> — <?= $product['total_qty'] ?> шт.</li>
            <?php endwhile; ?>
        </ol>
    </div>

    <div class="reports-section">
        <h3>🕒 Останні події</h3>

        <div class="search-bar">
            <input type="text" id="ordersSearchInput" placeholder="Пошук у звітах...">
            <button id="ordersSearchBtn">🔍</button>
        </div>

        <table id="ordersTable">
            <thead>
                <tr>
                    <th>Час</th>
                    <th>Тип</th>
                    <th>Об'єкт</th>
                    <th>Подія</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($logResult)) : ?>
                    <tr>
                        <td><?= date('d.m.Y H:i', strtotime($row['created_at'])); ?></td>
                        <td><?= htmlspecialchars($row['type']); ?></td>
                        <td><?= htmlspecialchars($row['entity']) ?> #<?= $row['entity_id'] ?></td>
                        <td><?= htmlspecialchars($row['message']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
