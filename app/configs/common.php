<?php

return [
    'sqlite_db_file' => __DIR__ . '/../../data/sii_test_task.sqlite3',

    'view_renderer' => [
        'views_dir' => __DIR__ . '/../web/views',
        'views_ext' => 'phtml',
    ],

    'mailer' => [
        'from' => 'SSI Test Task <ssi.test.task@ukr.net>',
        'smtp_host' => 'smtp.ukr.net',
        'smtp_username' => 'ssi.test.task@ukr.net',
        'smtp_password' => 'sd12wefWE',
        'smtp_secure' => 'ssl',
    ],

    'routes_cache_file' => __DIR__ . '/../../data/routes.cache',
    'routes' => [
        [['GET', 'POST'], '/', \app\web\controllers\UserController::class, 'registerAction'],
    ],
];