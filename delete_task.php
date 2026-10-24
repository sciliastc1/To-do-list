<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/task_worker.php';

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

deleteTask($task_id, $user_id);

$conn->close();

header('Location: index.php');
exit;

