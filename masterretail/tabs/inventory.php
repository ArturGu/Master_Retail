<?php
require_once __DIR__ . '/../php/helpers/auth.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/helpers/flash.php';

checkAuth();  // Перевірка авторизації та запуск сесії

// Визначаємо, чи поточний користувач — адміністратор
$isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

// Отримуємо тільки ті товари, які не позначені як видалені
$result = mysqli_query($conn, "SELECT * FROM products WHERE is_deleted = 0 ORDER BY id DESC");
?>

<h1>📦 Склад</h1>

<?php
// Відображаємо flash-повідомлення (якщо було встановлено)
display_flash_message();
?>

<?php if ($isAdmin): ?>
    <a href="php/inventory/add.php" class="button">➕ Додати новий товар</a>
<?php endif; ?>

<div class="search-bar">
    <input type="text" id="inventorySearchInput" placeholder="Пошук товарів...">
    <button id="inventorySearchBtn">🔍</button>
</div>

<table class="inventory-table" id="inventoryTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Назва</th>
            <th>Категорія</th>
            <th>SKU</th>
            <th>Опис</th>
            <th>Ціна (₴)</th>
            <th>Кількість</th>
            <?php if ($isAdmin): ?>
                <th>Дії</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php while ($product = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $product['id']; ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td><?= htmlspecialchars($product['sku']) ?></td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td><?= number_format($product['price'], 2, '.', ' ') ?></td>
                <td><?= $product['stock'] ?></td>
                <?php if ($isAdmin): ?>
                    <td>
                        <a href="php/inventory/edit.php?id=<?= $product['id']; ?>">✏️</a>
                        <a href="php/inventory/delete.php?id=<?= $product['id']; ?>"
                           onclick="return confirm('Видалити товар?');">🗑️</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
