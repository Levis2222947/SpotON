<?php

// Mijn instellingen.
$config = [
    'debug'    => false, // true = echte foutmeldingen laten zien (alleen bij testen)
    'timezone' => 'Europe/Amsterdam',
    'max_tickets_per_reservation' => 10,

    'db' => [
        'host' => '127.0.0.1',
        'name' => 'spoton',
        'user' => 'root',
        'pass' => '',
    ],
];

// Op een server zet ik de echte databasegegevens in config.local.php (staat niet op GitHub).
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

return $config;
