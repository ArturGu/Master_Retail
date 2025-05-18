<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/log.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

$clients = mysqli_query($conn, "SELECT id, name FROM clients ORDER BY name");
$products = mysqli_query($conn, "SELECT id, name, stock FROM products ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id']);
    $status = trim($_POST['status']);
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    if (!$client_id || !$status || empty($product_ids)) {
        set_flash_message("❌ Усі поля мають бути заповнені", 'error');
    } else {
        // Перевірка доступності товару
        foreach ($product_ids as $i => $product_id) {
            $qty = intval($quantities[$i]);
            $stmt = mysqli_prepare($conn, "SELECT stock FROM products WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $product_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res);

            if (!$row || $row['stock'] < $qty) {
                set_flash_message("❌ Недостатньо товару для продукту ID $product_id. Доступно: {$row['stock']}", 'error');
                header('Location: add.php');
                exit;
            }
        }

        // Додаємо замовлення
        $stmt = mysqli_prepare($conn, "INSERT INTO orders (client_id, status) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'is', $client_id, $status);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        foreach ($product_ids as $i => $product_id) {
            $qty = intval($quantities[$i]);
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity) VALUES ($order_id, $product_id, $qty)");
            mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $product_id");
            log_event($conn, 'списання', 'товар', $product_id, "Списано $qty шт. у замовленні ID $order_id");
        }

        log_event($conn, 'створення', 'замовлення', $order_id, "Замовлення клієнта ID $client_id з {$status}, ".count($product_ids)." товарів");
        set_flash_message("✅ Замовлення створено");
        header("Location: ../../index.php#orders");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Нове замовлення</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .form-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 15px;
            gap: 20px;
        }
        .form-row label {
            min-width: 140px;
            text-align: right;
            font-weight: bold;
        }
        .form-row select, .form-row input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-actions {
            text-align: right;
            margin-top: 20px;
        }
        .product-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        .product-row select,
        .product-row input[type="number"] {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        .product-row button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .product-row button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<div class="reports-wrapper">
    <div class="reports-section">
        <h2>📝 Створити нове замовлення</h2>

        <?php display_flash_message(); ?>

        <form method="POST">
            <div class="form-row">
                <label for="client_id">Клієнт:</label>
                <select name="client_id" id="client_id" required>
                    <option value="">Оберіть клієнта</option>
                    <?php while ($client = mysqli_fetch_assoc($clients)) : ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-row">
                <label for="status">Статус:</label>
                <select name="status" id="status" required>
                    <option value="нове">Нове</option>
                    <option value="в обробці">В обробці</option>
                    <option value="завершено">Завершено</option>
                    <option value="скасовано">Скасовано</option>
                </select>
            </div>

            <h4>🛒 Товари:</h4>
            <div id="product-list">
                <div class="product-row">
                    <select name="product_id[]" required>
                        <option value="">Оберіть товар</option>
                        <?php mysqli_data_seek($products, 0); while ($p = mysqli_fetch_assoc($products)) : ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['stock'] ?> шт.)</option>
                        <?php endwhile; ?>
                    </select>
                    <input type="number" name="quantity[]" value="1" min="1" required>
                    <button type="button" onclick="removeProductRow(this)">➖</button>
                </div>
            </div>
            <button type="button" onclick="addProductRow()">➕ Додати товар</button>

            <div class="form-actions">
                <button type="submit">💾 Створити замовлення</button>
            </div>
        </form>

        <p style="text-align: right; margin-top: 15px;"><a href="../../index.php#orders">← Назад до замовлень</a></p>
    </div>
</div>

<script>
function addProductRow() {
    const row = document.querySelector('.product-row').cloneNode(true);
    row.querySelector('select').value = '';
    row.querySelector('input').value = 1;
    document.getElementById('product-list').appendChild(row);
}

function removeProductRow(btn) {
    const rows = document.querySelectorAll('.product-row');
    if (rows.length > 1) btn.parentElement.remove();
}
</script>
</body>
</html>
