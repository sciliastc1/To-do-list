<?php
declare(strict_types=1);

// Logs the user out and goes back to the login page.

// The session has to be started before it can be destroyed
session_start();

// Clear session data and the session cookie
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: login.php');
exit;
