<?php
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/helpers/flash.php';
require_once __DIR__ . '/php/helpers/auth.php';

checkAuth();
checkRole('admin');

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!$username || !$email || !$password || !in_array($role, ['admin', 'manager'])) {
        set_flash_message('❌ Усі поля є обовʼязковими та роль має бути коректною', 'error');
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            set_flash_message('❌ Користувач з таким логіном або email вже існує', 'error');
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $password_hash, $role);
            mysqli_stmt_execute($stmt);

            set_flash_message('✅ Користувача успішно додано');
            header('Location: admin_panel.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Панель адміністратора</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="form-page">
    <h2>👤 Додати користувача</h2>

    <?php display_flash_message(); ?>

    <form method="POST" action="admin_panel.php">
        <div class="form-row">
            <label for="username">Логін:</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="form-row">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-row">
            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-row">
            <label for="role">Роль:</label>
            <select name="role" id="role" required>
                <option value="admin">Адміністратор</option>
                <option value="manager">Менеджер</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit">💾 Додати користувача</button>
        </div>
    </form>

    <p style="text-align: right; margin-top: 15px;"><a href="index.php">← Назад</a></p>
</div>
</body>
</html>
