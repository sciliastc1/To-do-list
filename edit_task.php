<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/task_worker.php';
require_once __DIR__ . '/twig.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$task_id = (int) ($_GET['id'] ?? 0);

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
}

$conn->close();

// Hand the task to the template so it can pre-fill the form
render('edit_task.html.twig', ['task' => $task]);
