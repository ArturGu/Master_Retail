<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/auth.php';

header('Content-Type: application/json');
checkAuth(); // Захищаємо від неавторизованих запитів

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '⛔ Невірний метод запиту']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($id <= 0 || $name === '' || $phone === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => '❌ Усі поля є обовʼязковими']);
    exit;
}

// Перевірка унікальності (крім самого себе)
$stmt = mysqli_prepare($conn, "SELECT id FROM clients WHERE (phone = ? OR email = ?) AND id != ?");
mysqli_stmt_bind_param($stmt, 'ssi', $phone, $email, $id);
mysqli_stmt_execute($stmt);
$check = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'message' => '❌ Інший клієнт вже має цей телефон або email']);
    exit;
}

// Оновлення даних
$stmt = mysqli_prepare($conn, "UPDATE clients SET name = ?, phone = ?, email = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'sssi', $name, $phone, $email, $id);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true, 'message' => '✅ Дані клієнта оновлено']);
} else {
    echo json_encode(['success' => false, 'message' => '❌ Помилка під час оновлення']);
}
