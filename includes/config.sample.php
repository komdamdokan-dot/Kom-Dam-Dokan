<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Kom Dam Dokan',
        'url' => 'http://localhost/komdamdokan',
        'env' => 'development',
        'debug' => true,
        'timezone' => 'Asia/Dhaka',
        'session_name' => 'kdd_session',
        'remember_cookie' => 'kdd_remember',
        'remember_days' => 30,
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'komdamdokan',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'komdamdokan@gmail.com',
        'password' => 'qsej bdmz kujw mnhv',
        'from_email' => 'komdamdokan@gmail.com',
        'from_name' => 'Kom Dam Dokan',
        'encryption' => 'tls',
    ],
];
