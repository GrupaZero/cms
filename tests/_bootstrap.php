<?php
// Here you can initialize variables that will be available to your tests

\Codeception\Configuration::$defaultSuiteSettings['modules']['config']['Db'] = [
    'dsn'      => 'mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_DATABASE', 'gzero-tests'),
    'user'     => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'dump'     => 'vendor/gzero/testing/db/dump.sql',
    'populate' => true,
    'cleanup'  => true
];
