<?php

$composer = __DIR__ . '/../vendor/autoload.php';

if (is_file($composer)) {
    require $composer;
} else {
    require __DIR__ . '/../lib/PHPMailer/Exception.php';
    require __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
    require __DIR__ . '/../lib/PHPMailer/SMTP.php';
}
