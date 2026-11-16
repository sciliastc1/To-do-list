<?php
declare(strict_types=1);

// Draws the captcha picture for the create account form (<img src="captcha.php">).
// The text in the picture is saved in the session, so register_process.php
// can check what the user typed. The answer never goes into the HTML.

session_start();

// No 0/O, 1/I/L so the letters can't be confused with each other
$characters = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
$code = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $characters[random_int(0, strlen($characters) - 1)];
}
$_SESSION['captcha'] = $code;

$width  = 160;
$height = 50;
$image  = imagecreatetruecolor($width, $height);

// Light background
$background = imagecolorallocate($image, 240, 242, 248);
imagefilledrectangle($image, 0, 0, $width, $height, $background);

// Random lines and dots so a computer can't read the text easily
for ($i = 0; $i < 6; $i++) {
    $color = imagecolorallocate($image, random_int(120, 200), random_int(120, 200), random_int(120, 200));
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $color);
}
for ($i = 0; $i < 150; $i++) {
    $color = imagecolorallocate($image, random_int(100, 220), random_int(100, 220), random_int(100, 220));
    imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
}

// Draw each letter in a dark colour at a slightly random height
for ($i = 0; $i < strlen($code); $i++) {
    $color = imagecolorallocate($image, random_int(0, 80), random_int(0, 80), random_int(0, 120));
    $x = 20 + $i * 26 + random_int(-3, 3);
    $y = random_int(10, 22);
    imagestring($image, 5, $x, $y, $code[$i], $color);
}

// Don't let the browser keep an old picture
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');

imagepng($image);
imagedestroy($image);
