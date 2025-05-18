<?php
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/helpers/flash.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Якщо користувач вже увійшов — перенаправляємо
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $role = 'manager'; // За замовчуванням

    // Перевірка на пусті поля
    if (!$username || !$email || !$password) {
        set_flash_message('❌ Усі поля обовʼязкові', 'error');
    } else {
        // Перевірка на унікальність логіна або email
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

            set_flash_message('✅ Реєстрація успішна! Увійдіть у систему');
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Реєстрація</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <h2>🧾 Реєстрація</h2>

        <?php display_flash_message(); ?>

        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Логін" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Зареєструватися</button>
        </form>
    </div>
</body>
</html>
