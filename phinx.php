<?php

use Dotenv\Dotenv;

require "app/helpers.php";

if(file_exists(".env")) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

return
[
    'paths' => [
        'migrations' => 'database/migrations',
        'seeds' => 'database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => env("APP_ENV"),
        'production' => [
            'adapter' => env("DB_DRIVER", "mysql"),
            'host' => env("DB_HOST", "127.0.0.1"),
            'name' => env("DB_DATABASE"),
            'user' => env("DB_USERNAME"),
            'pass' => env("DB_PASSWORD"),
            'port' => env("DB_PORT"),
            'charset' => env("DB_CHARSET"),
        ],
        'development' => [
            'adapter' => env("DB_DRIVER", "mysql"),
            'host' => env("DB_HOST", "127.0.0.1"),
            'name' => env("DB_DATABASE_DEV"),
            'user' => env("DB_USERNAME"),
            'pass' => env("DB_PASSWORD"),
            'port' => env("DB_PORT"),
            'charset' => env("DB_CHARSET"),
        ]
    ],
    'version_order' => 'creation'
];
