<?php
    declare(strict_types=1);

    // Settings live in config.php (git-ignored) so credentials never reach the repo.
    $config = require __DIR__ . '/config.php';

    // Throw exceptions on mysqli errors (default in PHP 8.1+, explicit here)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['name']);
        mysqli_set_charset($conn, 'utf8mb4');
    } catch (mysqli_sql_exception $e) {
        // Log the real reason; never show host/user details to visitors
        error_log('DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        die('Sorry, something went wrong.');
    }

    // Don't leak credentials (or clash with variables like $user) in the including page
    unset($config);
