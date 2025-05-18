<?php
require_once __DIR__ . '/../php/helpers/auth.php';
require_once __DIR__ . '/../php/helpers/flash.php';
require_once __DIR__ . '/../php/db.php';

checkAuth();
checkRole('admin');
?>

<h1>👥 Створення менеджера</h1>

<?php display_flash_message(); ?>

<form method="POST" action="php/users/create.php">
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

    <div class="form-actions">
        <button type="submit">📂 Створити менеджера</button>
    </div>
</form>

<hr style="margin: 40px 0;">
<h2>📋 Менеджери</h2>

<table class="clients-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Логін</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $users = mysqli_query($conn, "SELECT * FROM users WHERE role = 'manager' ORDER BY id DESC");
        while ($u = mysqli_fetch_assoc($users)) :
        ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['role'] ?></td>
            <td>
                <a href="php/users/edit.php?id=<?= $u['id'] ?>">✏️</a>
                <a href="php/users/delete.php?id=<?= $u['id'] ?>" onclick="return confirm('Справді видалити?')">🗑️</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
