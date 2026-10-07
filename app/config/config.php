<?php

/*
 * Standaardinstellingen. Per computer/server kun je ze aanpassen in config.local.php
 * (dat bestand staat in .gitignore, zodat wachtwoorden niet op GitHub komen).
 */
$config = [
    'app_name' => 'SpotOn',
    'venue'    => 'Harbor Stage',
    'debug'    => false,
    'timezone' => 'Europe/Amsterdam',

    // Maximaal aantal tickets dat een bezoeker in één keer mag reserveren.
    'max_tickets_per_reservation' => 10,

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'spoton',
        'user' => 'root',
        'pass' => '',
    ],
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $config = array_replace_recursive($config, require $localConfig);
}

return $config;
