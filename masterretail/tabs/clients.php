<?php
require_once __DIR__ . '/../php/helpers/auth.php';
require_once __DIR__ . '/../php/helpers/flash.php';
require_once __DIR__ . '/../php/db.php';

checkAuth();
if (session_status() === PHP_SESSION_NONE) session_start();

$query = "SELECT * FROM clients ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<h1>😎 Клієнти</h1>

<?php display_flash_message(); ?>

<a href="php/clients/add.php">➕ Додати нового клієнта</a>

<div class="search-bar">
    <input type="text" id="clientSearchInput" placeholder="Пошук клієнтів...">
    <button id="clientSearchBtn">🔍</button>
</div>

<table class="clients-table" id="clientsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ім'я</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($client = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= $client['id']; ?></td>
                    <td><?= htmlspecialchars($client['name']); ?></td>
                    <td><?= htmlspecialchars($client['phone']); ?></td>
                    <td><?= htmlspecialchars($client['email']); ?></td>
                    <td>
                        <a href="php/clients/edit.php?id=<?= $client['id']; ?>">✏️</a>
                        <a href="php/clients/delete.php?id=<?= $client['id']; ?>" onclick="return confirm('Видалити цього клієнта?');">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Немає клієнтів.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
