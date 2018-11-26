<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'rrd' => [
            'path' => __DIR__ . '/../storage' ,
        ],

        'db' => [
            'path' => __DIR__ . '/../storage/thermometre.db' ,
        ],

        'json' => [
            'path' => __DIR__ . '/../storage/thermometre.json' ,
        ],

        'auth_token' => isset($_ENV['THERMOMETRE_TOKEN']) ? $_ENV['THERMOMETRE_TOKEN'] : '',
    ],
];
