<?php
return [
    'host' => env('MAIL_HOST', ''),
    'port' => env('MAIL_PORT', 465),        // SMTP端口，通常为465或587
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'secure' => env('MAIL_SECURE', 'ssl'),      // 加密方式，如'ssl'或'tls'
    'from_email' => env('MAIL_FROM_EMAIL', ''),
    'from_name' => env('MAIL_FROM_NAME', ''),
];
