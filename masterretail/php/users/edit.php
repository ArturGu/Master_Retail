<?php
// ==== FILE: php/users/edit.php ====
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/auth.php';

checkAuth();
checkRole('admin');

if (session_status() === PHP_SESSION_NONE) session_start();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash_message('Невірний ID користувача', 'error');
    header('Location: ../../index.php#users');
    exit;
}

// Отримуємо поточного користувача
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $id AND role = 'manager'"));
if (!$user) {
    set_flash_message('Користувача не знайдено або роль не "manager"', 'error');
    header('Location: ../../index.php#users');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email) {
        set_flash_message('Імʼя користувача та email обовʼязкові', 'error');
    } else {
        // Перевірка унікальності
        $check = mysqli_query($conn, "SELECT id FROM users WHERE (username = '" . mysqli_real_escape_string($conn, $username) . "' OR email = '" . mysqli_real_escape_string($conn, $email) . "') AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            set_flash_message('Такий логін або email вже існує', 'error');
        } else {
            if ($password) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'sssi', $username, $email, $password_hash, $id);
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'ssi', $username, $email, $id);
            }
            mysqli_stmt_execute($stmt);

            set_flash_message('Менеджера оновлено успішно');
            header('Location: ../../index.php#users');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагування менеджера</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="reports-wrapper">
    <div class="reports-section">
        <h2>✏️ Редагувати менеджера</h2>

        <?php display_flash_message(); ?>

        <form method="POST">
            <div class="form-row">
                <label for="username">Логін:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="form-row">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-row">
                <label for="password">Новий пароль (необов'язково):</label>
                <input type="password" name="password" id="password">
            </div>

            <div class="form-actions">
                <button type="submit">🔖 Зберегти зміни</button>
            </div>
        </form>

        <p style="text-align:right; margin-top:20px">
            <a href="../../index.php#users">← Назад до користувачів</a>
        </p>
    </div>
</div>
</body>
</html>
