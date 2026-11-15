<?php
return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'pass' => $_ENV['DB_PASS'] ?? '',
    'db'   => $_ENV['DB_NAME'] ?? 'burp_foodhub',
];