<?php
require_once __DIR__ . '/twig.php'; 

render('login.html.twig', [
    'error' => $_GET['error'] ?? null
]);
?>