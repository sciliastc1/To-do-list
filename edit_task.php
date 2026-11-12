<?php
declare(strict_types=1);

// Edit page for one task (edit_task.php?id=...).
// GET shows the form filled in with the task, POST saves the changes.

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/task_worker.php';
require_once __DIR__ . '/twig.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$task_id = (int) ($_GET['id'] ?? 0);

// No id in the URL, go back to the home page
if ($task_id === 0) {
    header('Location: index.php');
    exit;
}

// Load existing task to pre-fill the form
$task = getTask($task_id, $user_id);

// Task not found or doesn't belong to this user
if ($task === null) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date    = $_POST['due_date']          ?? '';

    // Only accept values from these lists, anything else gets the default
    $allowed_priorities = ['low', 'medium', 'high'];
    $priority = in_array($_POST['priority'] ?? '', $allowed_priorities, true)
        ? $_POST['priority'] : 'low';

    $allowed_statuses = ['not_started', 'in_progress', 'completed'];
    $status = in_array($_POST['status'] ?? '', $allowed_statuses, true)
        ? $_POST['status'] : 'not_started';

    // Validate date format (YYYY-MM-DD) so a bad value can't crash the UPDATE
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $due_date);
    $valid_date = $date && $date->format('Y-m-d') === $due_date;

    if ($title !== '' && $valid_date) {
        editTask($task_id, $user_id, $title, $description, $due_date, $priority, $status);
        $conn->close();
        header('Location: index.php');
        exit;
    }
    // If the title or date was wrong we end up here and the form is shown again
}

$conn->close();

// Hand the task to the template so it can pre-fill the form
render('edit_task.html.twig', ['task' => $task]);
