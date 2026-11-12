<?php
// Shows the login form. The form is sent to login_process.php,
// which sends the user back here with ?error=... if the login failed.
require_once __DIR__ . '/twig.php'; 

render('login.html.twig', [
    'error' => $_GET['error'] ?? null
]);
?>