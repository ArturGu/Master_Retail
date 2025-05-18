<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/helpers/auth.php';

checkAuth(); // Захищаємо доступ

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Отримуємо всі замовлення разом з іменем клієнта
$query = "
    SELECT o.id, o.status, o.created_at, c.name AS client_name
    FROM orders o
    JOIN clients c ON o.client_id = c.id
    ORDER BY o.id DESC
";
$result = mysqli_query($conn, $query);
?>

<h1>📋 Замовлення</h1>
<a href="php/orders/add.php">➕ Додати нове замовлення</a>

<div class="search-bar">
    <input type="text" id="orderSearchInput" placeholder="Пошук замовлень...">
    <button id="orderSearchBtn">🔍</button>
</div>

<table class="clients-table" id="ordersMainTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Клієнт</th>
            <th>Статус</th>
            <th>Дата</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= $order['id']; ?></td>
                    <td><?= htmlspecialchars($order['client_name']); ?></td>
                    <td><?= htmlspecialchars($order['status']); ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="php/orders/view.php?id=<?= $order['id']; ?>">🔍</a>
                        <a href="php/orders/edit.php?id=<?= $order['id']; ?>">✏️</a>
                        <a href="php/orders/delete.php?id=<?= $order['id']; ?>" onclick="return confirm('Видалити це замовлення?');">🗑️</a>
                        <a href="php/orders/cancel.php?id=<?= $order['id']; ?>" onclick="return confirm('Справді скасувати це замовлення?');">🚫</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Немає замовлень.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
