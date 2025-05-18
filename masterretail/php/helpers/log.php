<?php
function log_event(mysqli $conn, string $type, string $entity, int $entity_id, string $message): void {
    $stmt = mysqli_prepare($conn, "INSERT INTO log (type, entity, entity_id, message) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssis', $type, $entity, $entity_id, $message);
    mysqli_stmt_execute($stmt);
}
