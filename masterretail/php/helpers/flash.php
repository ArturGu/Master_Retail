<?php
// Функція для відображення flash-повідомлень
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        $class = $msg['type'] === 'error' ? 'flash-error' : 'flash-success';
        $safe_text = htmlspecialchars($msg['text']);
        echo "<div class='flash-message $class'>{$safe_text}</div>";
        unset($_SESSION['flash_message']);
    }
}


// Функція для встановлення flash-повідомлення
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type
    ];
}
?>
