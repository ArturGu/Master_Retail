<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'masterretail';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Помилка підключення до бази даних: ' . mysqli_connect_error());
}
?>
