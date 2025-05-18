<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/auth.php';

header('Content-Type: application/json');
checkAuth(); // Захист від неавторизованого доступу

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '⛔ Невірний метод запиту']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($name === '' || $phone === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => '❌ Усі поля обовʼязкові']);
    exit;
}

// Перевірка унікальності телефону або email
$stmt = mysqli_prepare($conn, "SELECT id FROM clients WHERE phone = ? OR email = ?");
mysqli_stmt_bind_param($stmt, 'ss', $phone, $email);
mysqli_stmt_execute($stmt);
$check = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'message' => '❌ Такий телефон або email вже існує']);
    exit;
}

// Додавання клієнта
$stmt = mysqli_prepare($conn, "INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sss', $name, $phone, $email);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    $newId = mysqli_insert_id($conn);
    echo json_encode([
        'success' => true,
        'id' => $newId,
        'name' => htmlspecialchars($name)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => '❌ Помилка при додаванні клієнта']);
}
