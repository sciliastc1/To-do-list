<?php
declare(strict_types=1);

// Deletes a task. This file is called from tasks.js with fetch(),
// so it does not show a page, it just answers 'ok' when it worked.

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/task_worker.php';

// Only logged in users can delete tasks
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$task_id = (int) ($_POST['id'] ?? 0);

// No (valid) id was sent, so there is nothing to delete
if ($task_id === 0) {
    exit;
}

// deleteTask() also checks the user id, so you can't delete someone else's task
deleteTask($task_id, $user_id);

$conn->close();

// tasks.js waits for this before it removes the row from the page
echo 'ok';

