<?php

return [
    'default'     => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => (getenv('DB_HOST')) ?: '127.0.0.1',
            'database'  => (getenv('DB_NAME')) ?: 'gzero-tests',
            'username'  => (getenv('DB_USER')) ?: 'root',
            'password'  => (getenv('DB_PASS')) ?: '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]
    ],
    'migrations'  => 'migrations',
];
