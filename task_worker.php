<?php
declare(strict_types=1);

function deleteTask(int $taskId, int $userId): void {
    global $conn;

    $stmt = $conn->prepare(
        "DELETE FROM tasks WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('ii', $taskId, $userId);
    $stmt->execute();
    $stmt->close();
}

function editTask(int $taskId, int $userId, string $title, string $description, string $dueDate, string $priority, string $status): void {
    global $conn;

    // Determine is_done based on status
    $is_done = ($status === 'completed') ? 1 : 0;

    $stmt = $conn->prepare(
        "UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ?, status = ?, is_done = ? WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('sssssiis', $title, $description, $dueDate, $priority, $status, $is_done, $taskId, $userId);
    $stmt->execute();
    $stmt->close();
}
function getTask(int $taskId, int $userId): ?array {
    global $conn;

    $stmt = $conn->prepare(
        "SELECT * FROM tasks WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('ii', $taskId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    $stmt->close();

    return $task ?: null;
}
?>