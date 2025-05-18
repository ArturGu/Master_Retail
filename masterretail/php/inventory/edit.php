<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/flash.php';

checkAuth();

if (!is_admin()) {
    set_flash_message("⛔ У вас недостатньо прав для редагування товарів", 'error');
    header("Location: ../../inventory.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash_message('❌ Невірний ID товару', 'error');
    header('Location: ../../index.php#inventory');
    exit;
}

// Отримання товару
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$product) {
    set_flash_message('❌ Товар не знайдено', 'error');
    header('Location: ../../index.php#inventory');
    exit;
}

// Значення за замовчуванням для форми
$name = $product['name'];
$category = $product['category'];
$sku = $product['sku'];
$description = $product['description'];
$price = $product['price'];
$stock = $product['stock'];

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if (!$name || !$category || !$sku || $price < 0 || $stock < 0) {
        set_flash_message("❌ Усі поля мають бути заповнені коректно", 'error');
    } else {
        $stmt = mysqli_prepare($conn, "
            UPDATE products SET name = ?, category = ?, sku = ?, description = ?, price = ?, stock = ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, 'ssssdii', $name, $category, $sku, $description, $price, $stock, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        set_flash_message("✅ Товар оновлено");
        header("Location: ../../index.php#inventory");
        exit;

    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати товар</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="reports-wrapper">
    <div class="reports-section">
        <h2>✏️ Редагувати товар</h2>

        <?php display_flash_message(); ?>

        <form method="POST">
            <div class="form-row">
                <label for="name">Назва:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="form-row">
                <label for="category">Категорія:</label>
                <input type="text" name="category" id="category" value="<?= htmlspecialchars($category) ?>" required>
            </div>

            <div class="form-row">
                <label for="sku">Артикул (SKU):</label>
                <input type="text" name="sku" id="sku" value="<?= htmlspecialchars($sku) ?>" required>
            </div>

            <div class="form-row">
                <label for="description">Опис:</label>
                <input type="text" name="description" id="description" value="<?= htmlspecialchars($description) ?>">
            </div>

            <div class="form-row">
                <label for="price">Ціна (₴):</label>
                <input type="number" name="price" id="price" step="0.01" min="0" value="<?= htmlspecialchars($price) ?>" required>
            </div>

            <div class="form-row">
                <label for="stock">Кількість:</label>
                <input type="number" name="stock" id="stock" min="0" value="<?= htmlspecialchars($stock) ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit">💾 Зберегти зміни</button>
            </div>
        </form>

        <p style="margin-top: 20px; text-align: right;">
            <a href="../../index.php#inventory">← Назад до складу</a>
        </p>
    </div>
</div>
</body>
</html>
