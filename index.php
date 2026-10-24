<?php
    declare(strict_types=1);

    session_start();

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    // Redirect to login if not logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Database connection ($conn)
    require_once __DIR__ . '/db.php';

    $user_id = (int) $_SESSION['user_id'];
    $name    = htmlspecialchars($_SESSION['name'] ?? 'there');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Flow</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">Task Flow</div>
    <div class="navbar_links">
        <a href="index.php">Home</a>
        <a href="account.php">Account</a>
    </div>
</nav>

<div class="content">
    <h1>Good Morning, <?php echo htmlspecialchars($name); ?></h1>
    <p>Manage your tasks efficiently and effectively.</p>
</div>
<div class="dashboard-cards">
    <div class="dashboard-card">
        <h2>Task List</h2>
        <p>View and manage your tasks.</p>
        <a href="tasks.php" class="button">Go to Tasks</a>
    </div>
    <div class="dashboard-card">
        <h2>Calendar</h2>
        <p>Check your schedule and deadlines.</p>
        <a href="calendar.php" class="button">Go to Calendar</a>
    </div>
    <div class="dashboard-card">
        <h2>Reports</h2>
        <p>Generate reports on your task progress.</p>
        <a href="reports.php" class="button">Go to Reports</a>
    </div>
</div>
<div class="todo-section">
    <h2>To-Do List</h2>
    <?php
    // Fetch tasks from the database
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo '<ul class="todo-list">';
        while ($task = $result->fetch_assoc()) {
            $checked = $task['is_done'] ? 'checked' : '';
            $done_class = $task['is_done'] ? 'todo-item--done' : '';
            echo '<li class="todo-item ' . $done_class . '">';
            echo '<form action="toggle_task.php" method="POST" style="display:inline;">';
            echo '<input type="hidden" name="task_id" value="' . (int)$task['id'] . '">';
            echo '<input type="checkbox" class="todo-item_checkbox" name="is_done" value="1" ' . $checked . ' onchange="this.form.submit()">';
            echo '</form>';
            echo '<span class="todo-item_title">' . htmlspecialchars($task['title']) . '</span>';
            echo '<span class="todo-item_due-date">' . htmlspecialchars($task['due_date']) . '</span>';
            echo '<a href="edit_task.php?id=' . $task['id'] . '" class="btn-edit">Edit</a>';
            echo '<a href="delete_task.php?id=' . $task['id'] . '" class="btn-delete">Delete</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No tasks found. Add a new task to get started!</p>';
    }
    ?>
    <button class="btn-add-task" onclick="window.location.href='index.php?action=add'">
        Add New Task
    </button>  

    <?php
        // Include the create_task.php form
        if(isset($_GET['action']) && $_GET['action'] === 'add') {
            include 'create_task.php';
            
        }
    ?>
    <?php
    // Close the database connection
    $stmt->close();
    $conn->close();
    ?>
</div>
</body>

</html>
