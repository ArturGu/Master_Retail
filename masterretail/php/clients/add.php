<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if ($name === '' || $phone === '' || $email === '') {
        set_flash_message("❌ Усі поля є обов'язковими", 'error');
    } else {
        // Перевірка унікальності телефону або email
        $stmt = mysqli_prepare($conn, "SELECT id FROM clients WHERE phone = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $phone, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            set_flash_message("❌ Клієнт з таким телефоном або email вже існує", 'error');
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $name, $phone, $email);
            mysqli_stmt_execute($stmt);

            set_flash_message("✅ Клієнта додано успішно");
            header("Location: ../../index.php#clients");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати клієнта</title>
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

        .form-row input {
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
    </style>
</head>
<body>
<div class="reports-wrapper">
    <div class="reports-section">
        <h2>➕ Додати нового клієнта</h2>

        <?php display_flash_message(); ?>

        <form method="POST">
            <div class="form-row">
                <label for="name">Ім’я:</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div class="form-row">
                <label for="phone">Телефон:</label>
                <input type="text" name="phone" id="phone" required>
            </div>

            <div class="form-row">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-actions">
                <button type="submit">💾 Додати</button>
            </div>
        </form>

        <p style="text-align: right; margin-top: 15px;"><a href="../../index.php#clients">← Назад до клієнтів</a></p>
    </div>
</div>
</body>
</html>
