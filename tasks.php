<?php
declare(strict_types=1);

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

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

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List - Task Flow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">Task Flow</div>
    <div class="navbar_links">
        <a href="index.php">Home</a>
        <a href="tasks.php">Tasks</a>
        <a href="account.php">Account</a>
    </div>
</nav>

<div class="content">
    <h1>Task List</h1>
    <p>All your tasks in one place.</p>
</div>

<div class="todo-section">
    <div class="task-filters">
        <a href="tasks.php" class="task-filter <?php echo $filter === 'all' ? 'task-filter--active' : ''; ?>">
            All <span class="task-filter_count"><?php echo $counts['all']; ?></span>
        </a>
        <?php foreach ($status_labels as $key => $label): ?>
            <a href="tasks.php?status=<?php echo $key; ?>"
               class="task-filter <?php echo $filter === $key ? 'task-filter--active' : ''; ?>">
                <?php echo $label; ?> <span class="task-filter_count"><?php echo $counts[$key]; ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (count($tasks) > 0): ?>
        <ul class="todo-list">
            <?php foreach ($tasks as $task):
                $is_done = (bool) $task['is_done'];
                $overdue = !$is_done && $task['due_date'] < $today;
            ?>
                <li class="todo-item <?php echo $is_done ? 'todo-item--done' : ''; ?>">
                    <form action="toggle_task.php" method="POST" style="display:inline;">
                        <input type="hidden" name="task_id" value="<?php echo (int) $task['id']; ?>">
                        <input type="checkbox" class="todo-item_checkbox" name="is_done" value="1"
                               <?php echo $is_done ? 'checked' : ''; ?> onchange="this.form.submit()">
                    </form>

                    <div class="todo-item_body">
                        <span class="todo-item_title"><?php echo htmlspecialchars($task['title']); ?></span>
                        <?php if (($task['description'] ?? '') !== ''): ?>
                            <span class="todo-item_description"><?php echo htmlspecialchars($task['description']); ?></span>
                        <?php endif; ?>
                    </div>

                    <span class="badge badge--<?php echo htmlspecialchars($task['priority']); ?>">
                        <?php echo ucfirst(htmlspecialchars($task['priority'])); ?>
                    </span>
                    <span class="badge badge--status">
                        <?php echo $status_labels[$task['status']] ?? htmlspecialchars($task['status']); ?>
                    </span>
                    <span class="todo-item_due-date <?php echo $overdue ? 'todo-item_due-date--overdue' : ''; ?>">
                        <?php echo $overdue ? 'Overdue · ' : ''; ?><?php echo htmlspecialchars($task['due_date']); ?>
                    </span>

                    <a href="edit_task.php?id=<?php echo (int) $task['id']; ?>" class="btn-edit">Edit</a>
                    <a href="delete_task.php?id=<?php echo (int) $task['id']; ?>" class="btn-delete"
                       onclick="return confirm('Delete this task?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($filter === 'all'): ?>
        <p>No tasks yet. Add a new task to get started!</p>
    <?php else: ?>
        <p>No tasks with status “<?php echo $status_labels[$filter]; ?>”.</p>
    <?php endif; ?>

    <button class="btn-add-task" onclick="window.location.href='tasks.php?action=add'">
        Add New Task
    </button>
     <?php
        // Include the create_task.php form
        if(isset($_GET['action']) && $_GET['action'] === 'add') {
            include 'create_task.php';

        }
    ?>
</div>

</body>
</html>
