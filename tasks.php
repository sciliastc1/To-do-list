<?php
declare(strict_types=1);

// Tasks page: the full task list with priority, status and the filter tabs.

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/twig.php';

$user_id = (int) $_SESSION['user_id'];

// Status as saved in the database => text shown on the page
$status_labels = [
    'not_started' => 'Not Started',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
];

// Fetch tasks: unfinished first, then by due date, high priority first on the same day
$stmt = $conn->prepare(
    "SELECT * FROM tasks WHERE user_id = ?
     ORDER BY is_done ASC, due_date ASC, FIELD(priority, 'high', 'medium', 'low')"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Hand the data to the template. Filtering and counting happen in tasks.js
render('tasks.html.twig', [
    'tasks'         => $tasks,
    'status_labels' => $status_labels,
    'today'         => date('Y-m-d'),
    'error'         => $_GET['error'] ?? '',
]);
