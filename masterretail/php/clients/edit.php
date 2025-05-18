<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    set_flash_message('❌ Невірний ID клієнта', 'error');
    header('Location: ../../index.php#clients');
    exit;
}

// Отримуємо дані клієнта
$stmt = mysqli_prepare($conn, "SELECT * FROM clients WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$client = mysqli_fetch_assoc($result);

if (!$client) {
    set_flash_message('❌ Клієнта не знайдено', 'error');
    header('Location: ../../index.php#clients');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name && $phone && $email) {
        // Перевірка на унікальність телефону та email, крім цього клієнта
        $stmt = mysqli_prepare($conn, "SELECT id FROM clients WHERE (phone = ? OR email = ?) AND id != ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $phone, $email, $id);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($exists) > 0) {
            set_flash_message('❌ Інший клієнт вже має цей телефон або email', 'error');
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE clients SET name = ?, phone = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssi', $name, $phone, $email, $id);
            mysqli_stmt_execute($stmt);

            set_flash_message("✅ Клієнта оновлено успішно");
            header('Location: ../../index.php#clients');
            exit;
        }
    } else {
        set_flash_message("❌ Усі поля обов'язкові", 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати клієнта</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="form-page">
    <h2>✏️ Редагувати клієнта</h2>

    <?php display_flash_message(); ?>

    <form method="POST">
        <div class="form-row">
            <label for="name">Ім'я:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($client['name']) ?>" required>
        </div>

        <div class="form-row">
            <label for="phone">Телефон:</label>
            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($client['phone']) ?>" required>
        </div>

        <div class="form-row">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($client['email']) ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit">💾 Оновити</button>
        </div>
    </form>

    <p style="text-align: right; margin-top: 15px;">
        <a href="../../index.php#clients">← Назад до клієнтів</a>
    </p>
</div>
</body>
</html>
