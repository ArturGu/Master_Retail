<?php
require_once __DIR__ . '/php/helpers/auth.php';
require_once __DIR__ . '/php/helpers/flash.php';

checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();
display_flash_message();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>MasterRetail CRM</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="layout">
        <aside>
            <h2>MasterRetail</h2>
            <nav>
                <ul>
                    <li><a href="#clients" class="menu__item">Клієнти</a></li>
                    <li><a href="#orders" class="menu__item">Замовлення</a></li>
                    <li><a href="#inventory" class="menu__item">Склад</a></li>
                    <li><a href="#reports" class="menu__item">Звіти</a></li>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="#users" class="menu__item">Адмін панель</a></li>
                    <?php endif; ?>
                    <li><a href="#logout" class="menu__item">Вийти</a></li>
                </ul>
            </nav>
            <div style="margin-top:auto; font-size: 14px; padding-top: 20px; border-top: 1px solid #555; color: #ccc;">
                <?php
                    $username = htmlspecialchars($_SESSION['user']['username']);
                    $role = $_SESSION['user']['role'] === 'admin' ? 'Адміністратор' : 'Менеджер';
                    echo "$role: $username";
                ?>
            </div>
        </aside>

        <main>
            <div id="tabContent">Завантаження вкладок...</div>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
