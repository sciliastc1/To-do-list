<?php
declare(strict_types=1);

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/twig.php';

$user_id = (int) $_SESSION['user_id'];

$status_labels = [
    'not_started' => 'Not Started',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
];

// Whitelist the filter — anything unknown shows all tasks
$filter = array_key_exists($_GET['status'] ?? '', $status_labels) ? $_GET['status'] : 'all';

// Count tasks per status for the filter tabs
$counts = array_fill_keys(array_keys($status_labels), 0);
$stmt = $conn->prepare("SELECT status, COUNT(*) AS total FROM tasks WHERE user_id = ? GROUP BY status");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $counts[$row['status']] = (int) $row['total'];
}
$stmt->close();
$counts = ['all' => array_sum($counts)] + $counts;

// Fetch tasks: unfinished first, then by due date, high priority first on the same day
$sql = "SELECT * FROM tasks WHERE user_id = ?"
     . ($filter !== 'all' ? " AND status = ?" : "")
     . " ORDER BY is_done ASC, due_date ASC, FIELD(priority, 'high', 'medium', 'low')";
$stmt = $conn->prepare($sql);
if ($filter !== 'all') {
    $stmt->bind_param('is', $user_id, $filter);
} else {
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Hand the data to the template
render('tasks.html.twig', [
    'tasks'         => $tasks,
    'status_labels' => $status_labels,
    'counts'        => $counts,
    'filter'        => $filter,
    'today'         => date('Y-m-d'),
    'show_form'     => ($_GET['action'] ?? '') === 'add',
    'error'         => $_GET['error'] ?? '',
]);
