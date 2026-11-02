<?php
declare(strict_types=1);

// Sets up Twig and provides render() so every page can show a template in one line.

// Composers autoloader makes the Twig classes available
require_once __DIR__ . '/vendor/autoload.php';

// Renders templates/<template> and prints it.
function render(string $template, array $vars = []): void {
    // static keeps $twig between calls, so Twig is only set up once per request
    static $twig = null;

    if ($twig === null) {
        // The loader tells Twig which folder holds the template files
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        // The environment does the rendering (and escapes HTML automatically)
        $twig   = new \Twig\Environment($loader);
    }
    echo $twig->render($template, $vars);
}