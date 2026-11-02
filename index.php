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
    // Twig setup
    require_once __DIR__ . '/twig.php';

    $user_id = (int) $_SESSION['user_id'];
    $name    = $_SESSION['name'] ?? 'there';

    // Fetch this user's tasks as an array the template can loop over
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    // Hand the data to the template
    render('index.html.twig', [
        'name'      => $name,
        'tasks'     => $tasks,
        'show_form' => ($_GET['action'] ?? '') === 'add',
        'error'     => $_GET['error'] ?? '',
    ]);
