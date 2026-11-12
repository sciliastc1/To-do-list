<?php
declare(strict_types=1);

// Shows the create account form. The form is sent to register_process.php.

require_once __DIR__ . '/twig.php';

// The template reads these instead of touching $_GET itself
render('create_accout.html.twig', [
    'error'   => $_GET['error']   ?? '',
    'success' => isset($_GET['success']),
    'name'    => $_GET['name']    ?? '',
    'email'   => $_GET['email']   ?? '',
]);
