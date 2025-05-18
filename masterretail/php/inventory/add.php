<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/flash.php';

checkAuth();

if (!is_admin()) {
    set_flash_message("⛔ У вас недостатньо прав для додавання товарів", 'error');
    header("Location: ../../inventory.php");
    exit;
}

$name = $category = $sku = $description = '';
$price = $stock = 0;

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
        // Перевірка на унікальність SKU
        $check = mysqli_prepare($conn, "SELECT COUNT(*) FROM products WHERE sku = ?");
        mysqli_stmt_bind_param($check, 's', $sku);
        mysqli_stmt_execute($check);
        mysqli_stmt_bind_result($check, $count);
        mysqli_stmt_fetch($check);
        mysqli_stmt_close($check);

        if ($count > 0) {
            set_flash_message("❌ Товар з таким артикулом вже існує", 'error');
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (name, category, sku, description, price, stock) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssssdi', $name, $category, $sku, $description, $price, $stock);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            set_flash_message("✅ Товар успішно додано");
            header("Location: ../../index.php#inventory");
            exit;

        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати товар</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="reports-wrapper">
    <div class="reports-section">
        <h2>📦 Додати новий товар</h2>

        <?php display_flash_message(); ?>

        <form method="POST">
            <div class="form-row">
                <label for="name">Назва:</label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($name) ?>">
            </div>

            <div class="form-row">
                <label for="category">Категорія:</label>
                <input type="text" name="category" id="category" required value="<?= htmlspecialchars($category) ?>">
            </div>

            <div class="form-row">
                <label for="sku">Артикул (SKU):</label>
                <input type="text" name="sku" id="sku" required value="<?= htmlspecialchars($sku) ?>">
            </div>

            <div class="form-row">
                <label for="description">Опис:</label>
                <input type="text" name="description" id="description" value="<?= htmlspecialchars($description) ?>">
            </div>

            <div class="form-row">
                <label for="price">Ціна (₴):</label>
                <input type="number" name="price" id="price" step="0.01" min="0" required value="<?= htmlspecialchars($price) ?>">
            </div>

            <div class="form-row">
                <label for="stock">Кількість на складі:</label>
                <input type="number" name="stock" id="stock" min="0" required value="<?= htmlspecialchars($stock) ?>">
            </div>

            <div class="form-actions">
                <button type="submit">💾 Додати товар</button>
            </div>
        </form>

        <p style="margin-top: 20px; text-align: right;">
            <a href="../../index.php#inventory">← Назад до складу</a>
        </p>
    </div>
</div>
</body>
</html>
