<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Перевірка, чи користувач авторизований.
 * Якщо ні — перенаправляє на сторінку входу.
 */
function checkAuth() {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Перевірка, чи користувач має задану роль.
 * Якщо ні — перенаправляє на головну сторінку.
 */
function checkRole(string $requiredRole) {
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== $requiredRole) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Повертає true, якщо користувач — адміністратор.
 */
function is_admin(): bool {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Повертає true, якщо користувач — менеджер.
 */
function is_manager(): bool {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'manager';
}

/**
 * Завершення сесії та вихід із системи.
 */
function logout() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}
