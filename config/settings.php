<?php

return [
    'cache' => true,
    'cache_key_prefix' => 'db.settings.',
    'encryption' => true,
    "drivers" => [
        "pdo" => [
            'driver' => 'pdo',
            "table" => "settings",
            "dns"=>'mysql:host=localhost;dbname=test;port=3306;charset=utf8',
            "username" => 'root',
            "password" => 'root',
        ]
    ]
];