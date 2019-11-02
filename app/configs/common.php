<?php

return [
    'base_url' => 'http://192.168.56.2:8000',

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

    'captcha' => [
        'session_name' => 'captcha',
        'dev_phrase' => '555',
        'length' => 5,
        'width' => 131,
        'height' => 38
    ],

    'routes_cache_file' => __DIR__ . '/../../data/routes.cache',
    'routes' => [
        [['GET', 'POST'], '/', \app\web\controllers\UserController::class, 'registerAction'],
        [['GET', 'POST'], '/login', \app\web\controllers\UserController::class, 'loginAction'],
        ['GET', '/captcha', \app\web\controllers\UserController::class, 'captchaAction'],
    ],
];