<?php
declare(strict_types=1);

// Functions for working with tasks in the database.
// Used by delete_task.php, edit_task.php and toggle_task.php.
// They all need db.php to be loaded first because they use $conn.
// Every query also checks user_id, so a user can only change their own tasks.

/**
 * Deletes one task.
 *
 * @param int $taskId id of the task to delete
 * @param int $userId id of the logged in user (the owner of the task)
 */
function deleteTask(int $taskId, int $userId): void {
    global $conn;

    $stmt = $conn->prepare(
        "DELETE FROM tasks WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('ii', $taskId, $userId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Updates all the editable fields of a task.
 * is_done is not passed in, it is worked out from the status.
 *
 * @param string $dueDate  date as YYYY-MM-DD
 * @param string $priority low, medium or high
 * @param string $status   not_started, in_progress or completed
 */
function editTask(int $taskId, int $userId, string $title, string $description, string $dueDate, string $priority, string $status): void {
    global $conn;

    // A task is done when its status is completed, so the two always match
    $is_done = ($status === 'completed') ? 1 : 0;

    $stmt = $conn->prepare(
        "UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ?, status = ?, is_done = ? WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('sssssiii', $title, $description, $dueDate, $priority, $status, $is_done, $taskId, $userId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Gets one task as an array (column name => value).
 * Returns null if the task does not exist or belongs to someone else.
 */
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

    // fetch_assoc() gives null/false when there is no row
    return $task ?: null;
}
