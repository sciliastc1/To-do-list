<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/task_worker.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$task_id = (int) ($_POST['task_id'] ?? 0);

$status = isset($_POST['is_done']) ? 'completed' : 'not_started';

// Fetch current task to keep existing fields intact
$task = getTask($task_id, $user_id);
if ($task !== null) {
    editTask($task_id, $user_id, $task['title'], $task['description'], $task['due_date'], $task['priority'], $status);
}

$conn->close();

header('Location: index.php');
exit;

