<?php
declare(strict_types=1);

// Marks a task as done or not done when the checkbox is clicked.
// Called from tasks.js with fetch(), answers 'ok' when it is saved.

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/task_worker.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$task_id = (int) ($_POST['task_id'] ?? 0);

// is_done is only sent when the checkbox is ticked
$status = isset($_POST['is_done']) ? 'completed' : 'not_started';

// Fetch current task to keep existing fields intact
$task = getTask($task_id, $user_id);
if ($task !== null) {
    editTask($task_id, $user_id, $task['title'], $task['description'] ?? '', $task['due_date'], $task['priority'], $status);
}

$conn->close();

// The page is updated by tasks.js, so no redirect
echo 'ok';

